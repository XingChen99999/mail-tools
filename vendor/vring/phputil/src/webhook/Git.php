<?php

namespace vring\webhook;
class Git
{
    const SERVER_NAME = 'server';
    const SERVER_EMAIL = 'server@example.com';

    //webhook脚本
    static public function pushEventPull($username, $password, $repository, $pullbranch,$home)
    {
        /*  $username = \think\Env::get('webhook.username');
          $password = \think\Env::get('webhook.password');
          $repository = \think\Env::get('webhook.repository');
          $pullbranch = \think\Env::get('webhook.pullbranch');*/
        $a = parse_url($repository);
        $repository = "{$a['scheme']}://{$username}:{$password}@{$a['host']}" . (isset($a['port']) ? ":{$a['port']}" : '') . $a['path'];
        $env = array_merge($_ENV, [
            'GIT_TERMINAL_PROMPT' => 0,
            'HOME' => $home
        ]);
        print_r(self::runCommand('git config --global user.email "' . self::SERVER_EMAIL . '" && git config --global user.name "' . self::SERVER_NAME . '"', [], null, $env));
        print_r(self::runCommand('git pull ' . $repository . " {$pullbranch}", [], null, $env));
    }

    static private function runCommand(string $command, array $descriptors = [], string $cwd = null, array $env = null): array
    {
        $defaultDescriptors = [
            0 => ['pipe', 'r'], // 标准输入
            1 => ['pipe', 'w'], // 标准输出
            2 => ['pipe', 'w'], // 标准错误
        ];
        $descriptors = array_replace($defaultDescriptors, $descriptors);
        $process = proc_open($command, $descriptors, $pipes, $cwd, $env);
        if (!is_resource($process)) {
            throw new \RuntimeException("Failed to open process");
        }
        fclose($pipes[0]); // Git 不会使用 stdin 获取凭据，直接关闭
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $status = proc_close($process);
        return [
            'stdout' => trim($stdout),
            'stderr' => trim($stderr),
            'exit_code' => $status
        ];
    }

}