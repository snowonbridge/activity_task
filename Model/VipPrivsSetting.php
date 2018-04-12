<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/4/3
 * Time: 17:35
 */

namespace Workerman\Model;


use Workerman\Lib\Model;

class VipPrivsSetting extends Model
{
    public $db = 'activity';
    public $table = 'vip_privs_setting';
    public function getAll()
    {
       $all = $this->getRows($this->table,"status=1",[],['id','name','num']);
        $tmp=[];
        foreach ($all as $item)
        {
            $tmp[$item['id']] = $item;
        }
       return $tmp;
    }
    public function getAllByids($ids)
    {
        if($ids && is_array($ids))
        {
            $ids = implode(',',$ids);
        }
        if(!$ids)
            return [];
        $all = $this->getRows($this->table,"id in ({$ids}) and gift_id>0 and status=1",[],['id','name','num','gift_id']);
        return $all;
    }



}