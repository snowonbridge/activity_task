<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-8
 * Time: 下午7:08
 */

namespace Workerman\Model;


use Workerman\Lib\Model;
use Workerman\Lib\Okey;
use Workerman\Service\ATCode;

class UserConsumptionLog extends Model{
    public $db = 'activity';
    public $table = 'user_consumption_log';

    //领取状态@0禁止,1已领取,2未领取
    const RECEIVE_FORBID = 0;
    const RECEIVE_YES= 1;
    const RECEIVE_NO = 2;

    public function rule()
    {
        /**
         * `uid` int(11) NOT NULL DEFAULT '0',
        `activity_id` int(11) NOT NULL DEFAULT '0' COMMENT '活动id',
        `challege_list` varchar(1024) NOT NULL DEFAULT '' COMMENT '挑战项目列表',
        `gift_list` varchar(504) NOT NULL DEFAULT '' COMMENT '奖励列表',
        `is_receive` tinyint(4) NOT NULL DEFAULT '0' COMMENT '领取状态@0禁止,1可领取,2未领取',
        `add_time` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间@格式:年月日',
         * 好友前置条件@0无,1与两个好友一起玩,2自己开房,3不要自己开房
         */
        return [
            ['uid','required'],
            ['activity_id','required'],
            ['challege_list','required'],
            ['achieve_list','required'],
            ['gift_list','required'],
            ['is_receive','required'],
            ['frequency','required'],
            ['img_icon','required'],
            ['current_frequency','required'],
        ];
    }

    /**
     * @desc 增加每日任务记录
     * @param array $params
     * @return bool
     */
    public function addEveryTask($params=array())
    {
        if(!$this->validate($params,$this->rules()))
        {
            return false;
        }
        $params['add_time'] = isset($params['add_time'])?$params['add_time']:date('Ymd',time());
        $params['create_time'] = time();
        $params['update_time'] = time();
        $this->rmCache($params["uid"],$params['activity_id'],$params['add_time']);
        return  $this->insert($this->table,$params);

    }

    /**
     * @desc 获取每日任务的一行,注意重置时间是1天
     * @param $uid
     * @param $activity_id
     * @param string $add_time
     * @return mixed
     */
    public function getEveryDayOne($uid,$activity_id,$add_time='')
    {

        $add_time = !$add_time?date('Ymd',time()):$add_time;
        $r = $this->getOne($uid,$activity_id,$add_time);
        return $r;
    }

    /**
     * @desc 获取关卡每日重置任务的状态
     * @param $uid
     */
    public function getEveryDayAll($uid,$activity_ids=array(),$add_time='')
    {
        if(!$activity_ids)
            return false;
        $add_time = $add_time?$add_time:date('Ymd',time());
        foreach($activity_ids as $activity_id)
        {
            $result[] = $this->getOne($uid,$activity_id,$add_time);
        }

        return $result;
    }

    /**
     * @desc
     * @param $uid
     * @param $activity_id
     */
    public function validFrequency($uid,$activity_id,$add_time='')
    {
        $add_time = $add_time?$add_time:date('Ymd',time());
        $frequency = $this->getOne($uid,$activity_id,$add_time);
        if(!$frequency)
            return true;
        $base_frequency = $frequency['frequency'];
        $currentmax_frequency = $frequency['current_frequency'];
        if($currentmax_frequency >=$base_frequency)
            return false;
        return true;
    }

    /**
     * @desc 如果频率设置为1天多次,需要按时间倒序拍
     * @param $uid
     * @param $activity_id
     * @param string $add_time
     * @return int
     */
    public function getUserFrequency($uid,$activity_id,$add_time='')
    {
        $add_time = $add_time?$add_time:date('Ymd',time());
        $r = ATCode::getCache(Okey::UserConsumptionLog($uid,$activity_id,$add_time),function($uid,$activity_id,$add_time){
            $result = $this->getRow($this->table,'uid=:uid and activity_id=:activity_id and add_time=:add_time order by create_time desc',[
                ':uid'=>$uid,':activity_id'=>$activity_id,':add_time'=>$add_time]);
            return $result;
        },[$uid,$activity_id,$add_time]);
        return isset($r['current_frequency'])?intval($r['current_frequency']):0;
    }

    /**
     * @desc 更新活动  进度状态
     * @param $id
     * @param $data
     * @return bool
     */
    public function updateStatus($id,$data)
    {
        $r = $this->getRow($this->table,'id=:id',[':id'=>$id]);
        $this->rmCache($r['uid'],$r['activity_id'],$r['add_time']);
        return $this->update($this->table,$data,'id=:id',[':id'=>$id]);
    }

    /**
     * @desc 领取奖励
     * @param $uid
     * @param $activity_id
     * @param string $add_time
     * @return bool
     */
    public function receiveEveryDayGift($uid,$activity_id,$add_time='')
    {
        $add_time = !$add_time?date('Ymd',time()):$add_time;
        $this->rmCache($uid,$activity_id,$add_time);
        $r = $this->update($this->table,['is_receive'=>self::RECEIVE_YES],'uid=:uid and activity_id=:activity_id and add_time=:add_time',[
            ':uid'=>$uid,':activity_id'=>$activity_id,':add_time'=>$add_time]);
        return $r;
    }
    /**
     * @desc 是否存在禁止领取或未领取的任务
     * @param $uid
     * @param $activity_id
     * @return int
     */
    public function hasNoReceived($uid,$activity_id,$add_time='')
    {
        $add_time = $add_time?$add_time:date('Ymd',time());
        $r = $this->getOne($uid,$activity_id,$add_time);
        if(in_array($r['is_receive'],[self::RECEIVE_FORBID,self::RECEIVE_NO]))
        {
            return true;
        }
        return false;
    }

    public function getOne($uid,$activity_id,$add_time='')
    {
        $add_time = $add_time?$add_time:date('Ymd',time());
        return ATCode::getCache(Okey::UserConsumptionLog($uid,$activity_id,$add_time),function($uid,$activity_id,$add_time){
            $result = $this->getRow($this->table,'uid=:uid and activity_id=:activity_id and add_time=:add_time order by create_time desc',[
                ':uid'=>$uid,':activity_id'=>$activity_id,':add_time'=>$add_time]);
            return $result;
        },[$uid,$activity_id,$add_time]);
    }

    public function rmCache($uid,$activity_id,$add_time)
    {
        return ATCode::rmCache(Okey::UserConsumptionLog($uid,$activity_id,$add_time));
    }


} 