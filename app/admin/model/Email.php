<?php

namespace app\admin\model;

use think\Model;

/**
 * Email
 */
class Email extends Model
{
    // 表名
    protected $name = 'email';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

    public function message()
    {
        return $this->hasMany(Message::class, 'email_id', 'id')->order('id', 'desc');
    }


    public function messageNotRead()
    {
        return  $this->hasOne(Message::class, 'email_id', 'id')->order('id', 'desc');
    }
}