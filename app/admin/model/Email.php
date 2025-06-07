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

}