<?php
/**
 * Created by 育人爬虫.
 * User: Vnser
 * Date: 2020/12/27 0027
 * Time: 15:25
 */
namespace vring\db;
class SQL
{
    static public function insertInto($tableName, $data)
    {
        $sql = "insert into `{$tableName}`(%s) values(%s)";
        $field = '`' . join('`,`', array_keys($data)) . '`';
        $valArr = [];
        foreach ($data as $item){
            if (is_array($item)){
                switch ($item[0]){
                    case 'raw':
                        $valArr[] = " ({$item[1]}) ";
                        break;
                }
            }else{
                $valArr[] = "'{$item}'";
            }

        }
        $val =  join(",", $valArr);
        $sql = sprintf($sql, $field, $val);
        return $sql;
    }
}