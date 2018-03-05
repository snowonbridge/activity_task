<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-7
 * Time: 下午4:18
 */

namespace Workerman\Model;


use Workerman\Lib\Model;
use Workerman\Lib\Okey;
use Workerman\Service\ATCode;

class SocialGift extends Model{
    public $db = 'activity';
    public $table = 'social_gift';

    const STATUS_ON=1;
    const STATUS_OFF=2;

    public function rule()
    {
        return [
            ['activity_id','required'],
            ['action_list','required'],
            ['frequency','required'],
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
        $this->rmCache($params['activity_id']);
        return  $this->insert($this->table,$params);

    }


    public function getOne($activity_id)
    {
        return ATCode::getCache(Okey::SocialGift($activity_id),function($activity_id){
            $result =  $this->getRow($this->table,'activity_id=:activity_id and status=:status',[':activity_id'=>$activity_id,':status'=>self::STATUS_ON]);
            return $result;
        },[$activity_id]);
    }
    public function hasActivity($activity_id)
    {
        $r = $this->getOne($activity_id);
        return  empty($r)?0:1;

    }

    public function rmCache($activity_id)
    {
        return ATCode::rmCache(Okey::SocialGift($activity_id));
    }
} 