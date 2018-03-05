<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-12
 * Time: 上午11:08
 */

namespace Workerman\Model;


use Workerman\Lib\Model;

class UserEverydayActivityLog extends Model{
    public $db = 'activity';
    public $table = 'user_everyday_activity_log';


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
            ['params_content','required'],

        ];
    }

    /**
     * @desc 增加每日任务记录
     * @param array $params
     * @return bool
     */
    public function add($params=array())
    {
        if(!$this->validate($params,$this->rules()))
        {
            return false;
        }

        $params['create_time'] = time();

        return  $this->insert($this->table,$params);
    }
    public function incrCounts($uid,$activity_id,$add_time='')
    {
        $add_time = $add_time?$add_time:date('Ymd',time());
        return $this->incr($this->table,'uid=:uid and activity_id=:activity_id and add_time=:add_time',[':uid'=>$uid,':activity_id'=>$activity_id,':add_time'=>$add_time],'counts',1);
    }
    public function incrAchieveCounts($uid,$activity_id,$add_time='')
    {
        $add_time = $add_time?$add_time:date('Ymd',time());
        return $this->incr($this->table,'uid=:uid and activity_id=:activity_id and add_time=:add_time',[':uid'=>$uid,':activity_id'=>$activity_id,':add_time'=>$add_time],'achieve_counts',1);
    }
    public function getOne($uid,$activity_id,$add_time='')
    {
        $add_time = $add_time?$add_time:date('Ymd',time());
        return $this->getRow($this->table,'uid=:uid and activity_id=:activity_id and add_time=:add_time',[':uid'=>$uid,':activity_id'=>$activity_id,':add_time'=>$add_time]);
    }
    public function incrAchieveCountsById($id)
    {
        return $this->incr($this->table,'id=:id ',[':id'=>$id],'achieve_counts',1);
    }

} 