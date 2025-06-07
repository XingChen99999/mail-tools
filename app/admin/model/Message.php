<?php

namespace app\admin\model;

use think\Model;

/**
 * Message
 */
class Message extends Model
{
    // 表名
    protected $name = 'message';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;


    public function getContentAttr($value): string
    {
        return !$value ? '' : htmlspecialchars_decode($value);
    }

    public function email(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\Email::class, 'email_id', 'id');
    }
}