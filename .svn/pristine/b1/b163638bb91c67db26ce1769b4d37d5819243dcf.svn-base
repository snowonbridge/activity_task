<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-11
 * Time: 下午12:22
 */

namespace Workerman\Model;


use Workerman\Lib\Model;

class OnlineGift extends Model{
    public $db = 'activity';
    public $table = 'online_gift';


    public function rule()
    {
        return [
            ['activity_id','required'],

            ['challege_list','required'],
            ['frequency','required'],
            ['status','required'],
            ['gift_list','required'],
            ['img_icon','required'],
            ['desc','required'],
        ];
    }
    public function add($params=array())
    {
        if(!$this->validate($params,$this->rules()))
        {
            return false;
        }
        $params['create_time'] = time();
        return  $this->insert($this->table,$params);

    }

    public function getOne($activity_id)
    {
        $r = $this->getRow($this->table,'activity_id=:activity_id',[':activity_id'=>$activity_id]);
        return $r;
    }
    public function hasActivity($activity_id)
    {
        $r = $this->count($this->table,'activity_id=:activity_id',[':activity_id'=>$activity_id]);
        return $r;
    }
} 