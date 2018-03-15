<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/3/12
 * Time: 18:09
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;

class FriendLog extends Model{

    public $db = 'slog';
    public $table = 'friend_log';

    /**
     * 操作类型:1@结为好友,2@解除好友
     */
    const OPERATE_TYPE_FRIEND_MAKE = 1;
    const OPERATE_TYPE_FRIEND_UNBIND = 2;

    /**
     * 主被类型:1@主动,2@被动
     */
    const FTYPE_POSITIVE = 1;
    const FTYPE_PASSIVE = 2;

    /**
     * fuid` int(11) NOT NULL DEFAULT '0' COMMENT '主动邀请好友的用户uid',
    `fmid` int(11) NOT NULL DEFAULT '0' COMMENT '玩家MID',
    `fchannel_id` int(11) NOT NULL DEFAULT '0' COMMENT '渠道号',
    `tuid` int(11) NOT NULL DEFAULT '0' COMMENT '被动邀请好友的用户uid',
    `tmid` int(11) NOT NULL DEFAULT '0' COMMENT '好友MID',
    `tchannel_id` int(11) NOT NULL DEFAULT '0' COMMENT '好友渠道号',
    `operate_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '操作类型:1@结为好友,2@解除好友',
    `ftype` tinyint(4) NOT NULL DEFAULT '0' COMMENT '主被类型:1@主动,2@被动',
    `gift_1_id` varchar(100) DEFAULT '' COMMENT '礼物1ID',
    `gift_1_num` int(11) DEFAULT '0' COMMENT '礼物数量',
    `gift_2_id` varchar(100) DEFAULT '' COMMENT '礼物2ID',
    `gift_2_num` int(11) DEFAULT '0' COMMENT '礼物2数量',
    `gift_3_id` varchar(100) DEFAULT '' COMMENT '礼物3ID',
    `gift_3_num` int(11) DEFAULT '0' COMMENT '礼物3数量',
    `fuid_vip` int(11) NOT NULL DEFAULT '0' COMMENT '当时本人VIP级别',
    `tnd` int(11) NOT NULL DEFAULT '0' COMMENT '当日第几次',
    `operate_time` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
     */
    public function rules()
    {
        return [
            ['fuid','required'],
            ['fmid','required'],
            ['funame','required'],
            ['fchannal_id','required'],
            ['tuid','required'],
            ['tmid','required'],
            ['tuname','required'],
            ['tchannal_id','required'],
            ['operate_type','required'],
            ['ftype','required'],
            ['operate_type','required'],
            ['gift_1_id','required'],
            ['gift_1_num','required'],
            ['gift_2_id','required'],
            ['gift_2_num','required'],
            ['gift_3_id','required'],
            ['gift_3_num','required'],
            ['after_num','required'],
            ['give_uid','required'],
            ['give_mid','required'],
            ['fuid_vip','required'],
            ['tnd','required'],
            ['operate_time','required'],

        ];
    }
    public function insert($data)
    {
        if(!$this->validate($data,$this->rules()))
        {
            Logger::write('数据插入rules验证不通过',__METHOD__,"ERROR");
            return false;
        }
        $data['create_time'] = time();

        return $this->insertRaw($this->table,$data);
    }

}