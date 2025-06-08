<?php

namespace app\api\controller;

use app\common\controller\Frontend;

class Email extends Frontend
{
    protected array $noNeedLogin = ['*'];

    public function index(): void
    {
        $res = \app\admin\model\Email::with('message_not_read')->when($this->request->post('email'), function ($query) {
            $query->where('address', $this->request->post('email'));
        })->order('id', 'desc')->find();
        if (isset($res->message_not_read)) {
            $res->message_not_read->is_read = 1;
            $res->message_not_read->save();
//            $res->message->each(function ($item) {
//               if ($item->is_read == 0){
//                   $item->is_read = 1;
//                   $item->save();
//               }
//            });
//            $res->message()->save([
//                'is_read'=>1
//            ]);
        }
        if (!$res){
            $this->error('没有数据');
        }
        $this->success('', $res);
    }


    // 生成一个不重复的随机邮箱并保存
    public function generate()
    {
        for ($i = 0; $i < 10; $i++) {
            $email = $this->generateRandomEmail();

            if (!\app\admin\model\Email::where('address', $email)->find()) {
                $emailModel = new \app\admin\model\Email();
                $emailModel->address = $email;

                if ($emailModel->save()) {
                    $res = ['email' => $email];
                     $this->success('', $res);
                } else {
                     $this->error('保存失败');
                }
            }
        }

         $this->error('生成了10次都重复，请重试');
    }


    static protected function generateRandomEmail()
    {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $length = rand(8, 12); // 随机长度 8~12
        $username = '';

        for ($i = 0; $i < $length; $i++) {
            $username .= $chars[rand(0, strlen($chars) - 1)];
        }

        $domains = ['@ms9999.cc'];
        $domain = $domains[array_rand($domains)];

        return $username . $domain;
    }


    public function checkEmail()
    {
        $e = $this->request->post('email');
        $res = \app\admin\model\Email::where('address', $e)->find();
        if ($res) {
            $this->success();
        } else {
            \app\admin\model\Email::create([
                'address'=>$e
            ]);
            $this->success();
//            $this->error('没有数据');
        }
    }

}