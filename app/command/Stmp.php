<?php
declare (strict_types=1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use Swoole\Server;
use function Co\run;

class Stmp extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('stmp')
            ->setDescription('the stmp command');
    }

    protected function listen()
    {
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

                        $from = normalizeEmail($mailData[$fd]['from']);
                        $to = normalizeEmail($mailData[$fd]['to'][0]);
                        $email = parseEmailToUtf8($mailData[$fd]['data']);
                        echo "From: " . normalizeEmail($mailData[$fd]['from']) . "\n";
                        echo "To: " . normalizeEmail($mailData[$fd]['to'][0]) . "\n";
                        echo "验证码： " . join(',', extractVerificationCodes($email['body']));
                        go(function () use ($email, $to) {
                            $this->smtp_send_mail($to, '971626354@qq.com', $email['subject'], $email['body']);
                        });
                        $mailData[$fd]['state'] = 'QUIT';
                    }
                    break;

                case 'QUIT':
                    echo "QUIT";
                    $response = "221 Bye\r\n";
                    $server->send($fd, $response);
                    $server->close($fd);
                    return;
            }

            if ($response) {
                $server->send($fd, $response);
            }
        });


        $server->on('close', function (Server $server, $fd) use (&$mailData) {
            echo "Client {$fd} closed.\n";
            unset($mailData[$fd]);
        });

        echo "Swoole Mail Server started on port 25\n";
        $server->start();
    }

    protected function execute(Input $input, Output $output)
    {
        $this->listen();
    }

// 发送命令函数，发送后读取服务器响应
    protected  function send_cmd($fp, $cmd)
    {
        echo "C: $cmd";
        fwrite($fp, $cmd);
        $resp = fgets($fp, 515);
        echo "S: $resp";
        return $resp;
    }


    /**
     * 使用PHP实现基于MX记录的SMTP发信示例（无认证，纯手写协议）
     * 注意：此代码适合实验、学习，不能保证邮件成功投递到所有服务商
     */
    public function smtp_send_mail($from, $to, $subject, $body)
    {
        // 解析收件人域名MX记录
        $domain = substr(strrchr($to, "@"), 1); // 提取收件人域名
        $from_domain = substr(strrchr($from, "@"), 1); // 提取收件人域名
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


        // HELO
        $localhost = $from_domain;  // 你服务器的主机名
        $resp = $this->send_cmd($fp, "HELO $localhost\r\n");
        if (strpos($resp, '250') !== 0) {
            echo "HELO失败\n";
            fclose($fp);
            return false;
        }

        // MAIL FROM
        $resp = $this->send_cmd($fp, "MAIL FROM:<$from>\r\n");
        if (strpos($resp, '250') !== 0) {
            echo "MAIL FROM失败\n";
            fclose($fp);
            return false;
        }

        // RCPT TO
        $resp = $this->send_cmd($fp, "RCPT TO:<$to>\r\n");
        if (strpos($resp, '250') !== 0 && strpos($resp, '251') !== 0) {
            echo "RCPT TO失败\n";
            fclose($fp);
            return false;
        }

        // DATA
        $resp = $this->send_cmd($fp, "DATA\r\n");
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
        $this->send_cmd($fp, "QUIT\r\n");
        fclose($fp);
        echo "邮件发送成功\n";
        return true;

    }
}
