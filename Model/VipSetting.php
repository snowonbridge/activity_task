<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/4/3
 * Time: 17:34
 */

namespace Workerman\Model;


use Workerman\Lib\Model;

class VipSetting extends Model
{
    public $db = 'activity';
    public $table = 'vip_setting';
    public function getAll()
    {
        $all = $this->getRows($this->table," vip>0 order by charge asc",[],['id','name','vip','charge','privs']);
        foreach ($all as &$item)
        {
            $item['id'] = (int)$item['id'];
            $item['vip'] = (int)$item['vip'];
            $item['charge'] = (int)$item['charge'];
        }
        return $all;
    }
    public function getOneByVip($vip)
    {
        $r = $this->getRow($this->table,"vip=:vip",[":vip"=>$vip?:0]);
        return $r;
    }


}