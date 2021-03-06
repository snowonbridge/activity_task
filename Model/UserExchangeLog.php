<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-16
 * Time: 下午5:17
 */

namespace Workerman\Model;


use Workerman\Lib\Model;
use Workerman\Lib\Okey;
use Workerman\Lib\Redis;
use Workerman\Service\ATCode;

class UserExchangeLog extends Model{
    public $db = 'activity';
    public $table = 'user_exchange_log';


    /**
     * @desc 获取和设置兑换数量
     * @param $uid
     * @param string $month_time
     * @return int
     */
    public function getSetNum($param,$month_time='',$is_get=0)
    {
        $uid = $param['uid'];
        $from_gift_id = $param['from_gift_id'];
        $target_gift_id = $param['target_gift_id'];
        $activity_id =  $param['activity_id'];
        $month_time = $month_time?$month_time:date('Ym');

        $num = ATCode::getCache(Okey::UserExchangeNum($uid,$activity_id,$month_time),function($uid,$activity_id,$month_time){
            $r = $this->getRow($this->table,'uid=:uid and activity_id=:activity_id and add_month_time=:add_month_time',
                [':uid'=>$uid,':activity_id'=>$activity_id,':add_month_time'=>$month_time]);
            return $r?$r['num']:0;
        },[$uid,$activity_id,$month_time]);
        if($is_get)
        {
            return $num;
        }
        if($param['from_num'] == 0)
            return $num;
        ATCode::rmCache(Okey::UserExchangeNum($uid,$activity_id,$month_time),$param['from_num']);
        if(!$num)
        {
            $data['uid'] = $param['uid'];
            $data['activity_id'] = $param['activity_id'];
            $data['from_gift_id'] = $param['from_gift_id'];
            $data['target_gift_id'] = $param['target_gift_id'];
            $data['num'] = $param['from_num'];
            $data['add_month_time'] = $month_time;
            $data['add_day_time'] = date('Ymd');
            $data['create_time'] = time();
            if(!$this->insert($this->table,$data))
                return false;
            else
                return $data['num'];
        }else{

            $data['num'] = $num + $param['from_num'];

            $r = $this->update($this->table,$data,'uid=:uid and activity_id=:activity_id  and add_month_time=:add_month_time',
                [':uid'=>$uid,':activity_id'=>$activity_id,':add_month_time'=>$month_time]);
            return $r? $data['num']:$num;
        }

    }
} 