<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use Swoole\Server;
class Stmp extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('stmp')
            ->setDescription('the stmp command');
    }

    protected function execute(Input $input, Output $output)
    {





        /**
         * 去除两端空白和尖括号，并转为小写
         *
         * @param string $email 原始字符串，如 "  <User@EXAMPLE.COM>  "
         * @return string 处理后的小写邮箱，如 "user@example.com"
         */
        function normalizeEmail(string $email): string
        {
            // trim 的第二个参数列出需要去除的字符：空格、制表符、换行符、NULL、\v 以及尖括号
            $stripped = trim($email, " \t\n\r\0\x0B<>");
            return strtolower($stripped);
        }

        /**
         * 从给定文本中提取 115 位的长码（只含大小写字母和数字）
         *
         * @param string $text 待匹配的文本
         * @return array 匹配到的所有 115 位长码，若无返回空数组
         */
        function extract115CharCodes(string $text): array
        {
            // \b 确保前后是“单词边界”，避免匹配到更长字符串的中间
            // [A-Za-z0-9]{115} 只匹配长度恰好 115 个字符，范围是 A–Z、a–z、0–9
            preg_match_all('/\b[A-Za-z0-9]{115}\b/', $text, $matches);
            return $matches[0];
        }


        function extractVerificationCodes(string $text): array
        {
            $res = extract115CharCodes($text);
            if ($res) {
                return $res;
            }
            // \b 确保前后是单词边界，避免匹配到更长字符串中的子串
            // [A-Z0-9]{4,6} 只匹配大写字母或数字，长度 4–6
            preg_match_all('/\b[A-Z0-9]{4,6}\b/', strip_tags($text), $matches);
            return $matches[0];
        }

        /**
         * 解析原始邮件，提取 Subject 和正文内容，并统一转为 UTF-8
         *
         * @param string $rawEmail 完整的原始邮件文本
         * @return array ['subject' => string, 'body' => string]
         */
        function parseEmailToUtf8(string $rawEmail): array
        {
            // 1. 拆分头部/主体
            list($rawHeaders, $rawBody) = preg_split("/\r?\n\r?\n/", $rawEmail, 2);
            // 2. 解析头部，合并折行
            $lines = preg_split("/\r?\n/", $rawHeaders);
            $headers = $current = null;
            foreach ($lines as $line) {
                if (preg_match('/^[ \t]/', $line) && $current) {
                    $headers[$current] .= ' ' . trim($line);
                } elseif (strpos($line, ':') !== false) {
                    list($k, $v) = explode(':', $line, 2);
                    $current = strtolower(trim($k));
                    $headers[$current] = trim($v);
                }
            }

            // 3. 解析并转码 Subject
            $subject = '';
            if (!empty($headers['subject'])) {
                // mb_decode_mimeheader 会自动把 RFC2047 编码片段转回原编码
                $decoded = mb_decode_mimeheader($headers['subject']);
                // 再转为 UTF-8
                $subject = mb_convert_encoding($decoded, 'UTF-8', mb_detect_encoding($decoded, mb_detect_order(), true));
            }

            // 辅助：从 Content-Type 里提取 charset
            $getCharset = function (string $header) {
                if (preg_match('/charset="?([^;\s"]+)/i', $header, $m)) {
                    return strtolower($m[1]);
                }
                return null;
            };
            $defaultCharset = $getCharset($headers['content-type'] ?? '') ?: 'ASCII';

            // 4. 处理 multipart 或单体
            $body = '';
            if (!empty($headers['content-type']) && preg_match('/boundary="([^"]+)"/i', $headers['content-type'], $m)) {
                $boundary = preg_quote($m[1], '/');
                $parts = preg_split("/--{$boundary}(?:--)?\r?\n/", $rawBody);
                // 优先 text/plain
                foreach ($parts as $part) {
                    if (!preg_match('/^Content-Type:/mi', $part)) continue;
                    if (stripos($part, 'text/plain') !== false) {
                        list($ph, $pb) = preg_split("/\r?\n\r?\n/", $part, 2);
                        // 原 charset
                        $cs = $getCharset($ph) ?: $defaultCharset;
                        // 编码方式
                        $enc = preg_match('/Content-Transfer-Encoding:\s*(\S+)/i', $ph, $e)
                            ? strtolower($e[1]) : '7bit';
                        // 解码
                        switch ($enc) {
                            case 'base64':
                                $decoded = base64_decode($pb);
                                break;
                            case 'quoted-printable':
                                $decoded = quoted_printable_decode($pb);
                                break;
                            default:
                                $decoded = $pb;
                        }
                        // 转 UTF-8
                        $body = trim(mb_convert_encoding($decoded, 'UTF-8', $cs));
                        break;
                    }
                }
                // 再尝试 text/html
                if ($body === '') {
                    foreach ($parts as $part) {
                        if (stripos($part, 'text/html') === false) continue;
                        list($ph, $pb) = preg_split("/\r?\n\r?\n/", $part, 2);
                        $cs = $getCharset($ph) ?: $defaultCharset;
                        $enc = preg_match('/Content-Transfer-Encoding:\s*(\S+)/i', $ph, $e)
                            ? strtolower($e[1]) : '7bit';
                        $decoded = $enc === 'base64'
                            ? base64_decode($pb)
                            : ($enc === 'quoted-printable'
                                ? quoted_printable_decode($pb)
                                : $pb);
                        $body = trim(strip_tags(mb_convert_encoding($decoded, 'UTF-8', $cs)));
                        break;
                    }
                }
            } else {
                // 单体邮件
                $enc = strtolower($headers['content-transfer-encoding'] ?? '7bit');
                switch ($enc) {
                    case 'base64':
                        $decoded = base64_decode($rawBody);
                        break;
                    case 'quoted-printable':
                        $decoded = quoted_printable_decode($rawBody);
                        break;
                    default:
                        $decoded = $rawBody;
                }
                $body = trim(mb_convert_encoding($decoded, 'UTF-8', $defaultCharset));
            }

            return ['subject' => $subject, 'body' => $body];
        }


// 创建 Swoole TCP 服务器
        $server = new Server('0.0.0.0', 25, SWOOLE_BASE, SWOOLE_SOCK_TCP);
        $server->set([
            'ssl_cert_file' => __DIR__ . '/smtp.crt',
            'ssl_key_file' => __DIR__ . '/smtp.key',
            // 其他 SSL 选项
            'ssl_verify_peer' => false,
            'ssl_allow_self_signed' => true,
        ]);
// 存储每个连接的邮件数据
        $mailData = [];

// 设置服务器回调
        $server->on('connect', function (Server $server, $fd) use (&$mailData) {
            echo "Client {$fd} connected.\n";

            // 初始化邮件数据
            $mailData[$fd] = [
                'from' => '',
                'to' => [],
                'data' => '',
                'state' => 'INIT'
            ];

            // 发送欢迎消息
            $server->send($fd, "220 Welcome to Swoole Mail Server\r\n");
        });

        $server->on('receive', function (Server $server, $fd, $reactorId, $data) use (&$mailData) {
            // echo 123;
            // echo $data;

            $command = strtoupper(trim($data));
            $response = '';

            switch ($mailData[$fd]['state']) {
                case 'INIT':
                    if (strpos($command, 'HELO') === 0 || strpos($command, 'EHLO') === 0) {
                        $response = "250 Hello, I'm Swoole Mail Server\r\n";
                        $mailData[$fd]['state'] = 'READY';
                    } else {
                        $response = "503 Bad sequence of commands\r\n";
                    }
                    break;

                case 'READY':
                    if (strpos($command, 'MAIL FROM:') === 0) {
                        $mailData[$fd]['from'] = substr($command, 10);
                        $response = "250 OK\r\n";
                        $mailData[$fd]['state'] = 'FROM';
                    } else {
                        $response = "503 Expected MAIL FROM\r\n";
                    }
                    break;

                case 'FROM':
                    if (strpos($command, 'RCPT TO:') === 0) {
                        $mailData[$fd]['to'][] = substr($command, 8);
                        $response = "250 OK\r\n";
                    } elseif ($command === 'DATA') {
                        $response = "354 Start mail input; end with <CRLF>.<CRLF>\r\n";
                        $mailData[$fd]['state'] = 'DATA';
                    } else {
                        $response = "503 Expected RCPT TO or DATA\r\n";
                    }
                    break;

                case 'DATA':

                    $line = str_replace("\r\n..", "\r\n.", $data);
                    $mailData[$fd]['data'] .= $line;
                    if (strlen($data) - strrpos($data, '.') <= 5) {
                        // 保存邮件到文件
                        // $filename = 'mail_' . date('Ymd_His') . '_' . uniqid() . '.eml';
                        // file_put_contents($filename, $mailData[$fd]['data'].$data);

                        $response = "250 Message accepted for delivery\r\n";
                        $mailData[$fd]['state'] = 'READY';
//                print_r($mailData);
//               echo strtoupper( trim($mailData[$fd]['from'],'<>'));
//                echo "Mail saved to: $filename\n";
                        echo "From: " . normalizeEmail($mailData[$fd]['from']) . "\n";
                        echo "To: " . normalizeEmail($mailData[$fd]['to'][0]) . "\n";
                        echo "验证码： " . join(',', extractVerificationCodes(parseEmailToUtf8($mailData[$fd]['data'])['body']));
                    }
                    break;

                case 'QUIT':
                    $response = "221 Bye\r\n";
                    $server->close($fd);
                    break;
            }

            // 处理QUIT命令（任何状态都可以退出）
            if ($command === 'QUIT') {
                $response = "221 Bye\r\n";

            }
            if ($response) {
                $server->send($fd, $response);
                if ($command === 'QUIT') {
                    $server->close($fd);
                }

            }
        });

        $server->on('close', function (Server $server, $fd) use (&$mailData) {
            echo "Client {$fd} closed.\n";
            unset($mailData[$fd]);
        });

        echo "Swoole Mail Server started on port 25\n";
        $server->start();
    }


    public function send()
    {

        /**
         * 使用PHP实现基于MX记录的SMTP发信示例（无认证，纯手写协议）
         * 注意：此代码适合实验、学习，不能保证邮件成功投递到所有服务商
         */

        function smtp_send_mail($from, $to, $subject, $body)
        {
            // 解析收件人域名MX记录
            $domain = substr(strrchr($to, "@"), 1); // 提取收件人域名
            if (!$domain) {
                echo "收件人地址格式错误\n";
                return false;
            }

            $mx_hosts = [];
            if (!getmxrr($domain, $mx_hosts)) {
                echo "无法获取域名MX记录: $domain\n";
                return false;
            }

            // 选择优先级最高的MX服务器，简单起见用第一个
            $mx_host = $mx_hosts[0];
            echo "目标MX服务器: $mx_host\n";

            // 连接SMTP服务器（25端口）
            $fp = fsockopen($mx_host, 25, $errno, $errstr, 10);
            if (!$fp) {
                echo "无法连接 $mx_host:25 - $errstr ($errno)\n";
                return false;
            }

            // 读取服务器欢迎信息
            $response = fgets($fp, 515);
            echo "S: $response";

            // 发送命令函数，发送后读取服务器响应
            function send_cmd($fp, $cmd)
            {
                echo "C: $cmd";
                fwrite($fp, $cmd);
                $resp = fgets($fp, 515);
                echo "S: $resp";
                return $resp;
            }

            // HELO
            $localhost = 'vjike.cn';  // 你服务器的主机名
            $resp = send_cmd($fp, "HELO $localhost\r\n");
            if (strpos($resp, '250') !== 0) {
                echo "HELO失败\n";
                fclose($fp);
                return false;
            }

            // MAIL FROM
            $resp = send_cmd($fp, "MAIL FROM:<$from>\r\n");
            if (strpos($resp, '250') !== 0) {
                echo "MAIL FROM失败\n";
                fclose($fp);
                return false;
            }

            // RCPT TO
            $resp = send_cmd($fp, "RCPT TO:<$to>\r\n");
            if (strpos($resp, '250') !== 0 && strpos($resp, '251') !== 0) {
                echo "RCPT TO失败\n";
                fclose($fp);
                return false;
            }

            // DATA
            $resp = send_cmd($fp, "DATA\r\n");
            if (strpos($resp, '354') !== 0) {
                echo "DATA命令被拒绝\n";
                fclose($fp);
                return false;
            }

            // 邮件头和正文
            $message_id = '<' . time() . '.' . uniqid() . '@' . parse_url('http://' . $from, PHP_URL_HOST) . '>';
            $message = "Subject: $subject\r\n";
            $message .= "From: <$from>\r\n";
            $message .= "To: <$to>\r\n";
            $message .= "Date: " . date('r') . "\r\n";
            $message .= "Message-ID: $message_id\r\n";
            $message .= "\r\n";
            $message .= $body . "\r\n";
            $message .= ".\r\n";

            fwrite($fp, $message);
            $resp = fgets($fp, 515);
            echo "S: $resp";
            if (strpos($resp, '250') !== 0) {
                echo "邮件发送失败\n";
                fclose($fp);
                return false;
            }

            // QUIT
            send_cmd($fp, "QUIT\r\n");
            fclose($fp);
            echo "邮件发送成功\n";
            return true;
        }

// 测试调用
        $from = 'yyds12323sadasda@vjike.cn';    // 请替换成你自己的发件地址
        $to = 'xingchen010301@gmail.com';             // 收件地址
        $subject = '测试邮件';
        $body = '这是测试邮件的正文内容';

        smtp_send_mail($from, $to, $subject, $body);

    }
}
