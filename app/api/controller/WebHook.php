<?php

namespace app\api\controller;

use think\facade\Env;
use vring\webhook\Git;

class WebHook
{
    public function git()
    {

        $username = Env::get('webhook.username');
        $password = Env::get('webhook.password');
        $repository = Env::get('webhook.repository');
        $pullbranch = Env::get('webhook.pullbranch');
        Git::pushEventPull($username, $password, $repository, $pullbranch,root_path());
        exit;

    }
}