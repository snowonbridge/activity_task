<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/3/9
 * Time: 9:42
 */

namespace Workerman\Service;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Model\FriendApply;
use Workerman\Model\FriendInviteGameLog;
use Workerman\Model\FriendLog;
use Workerman\Model\FriendOperateNumLog;
use Workerman\Model\GiftContentSetting;
use Workerman\Model\GiftLog;
use Workerman\Model\ToolOperateLog;
use Workerman\Model\User;
use Workerman\Model\UserGift;
use Workerman\Model\UserTool;
use Workerman\Model\ToolUseLocationLog;
use Workerman\Model\UserUsedTool;

class DataLogService extends Model{


    /**
     * @desc 更新poker_friend_apply表is_invite_game 状态
     * game_id
     * game_type_id
     * game_room_id
     * $uid
     * $friend_uid
     * invite_time
     */
    public function updateToolUsingStatus($params)
    {
        $uid = $params['uid'];
        $friend_uid = $params['friend_uid'];
        $invite_time = $params['invite_time'];
        $apply_params = array();
        $apply_params['uid'] = $uid;
        $apply_params['friend_uid'] = $friend_uid;
        $apply_params['invite_time'] = $invite_time;
        FriendInviteGameLog::instance()->insert($apply_params);
        $applyInfo = FriendApply::instance()->getOne($uid,$friend_uid);
        if($applyInfo && !$applyInfo['is_invite_game'])
        {
            $s =  FriendApply::instance()->updateInviteStatus($applyInfo['id']);
            if(!$s)
            {
                Logger::write('更新is_invite_game状态失败 friend_apply ',__METHOD__,"ERROR");
            }
        }
    }

    /**
     * -------------道具同步入库 start-----------------
     */

    /**
     * 消费过期操作记录
     *
     * 必须先ToolUseLocationLog 入库
     *uid
     * ttid
     *
     *
     */
    public function toolExpireLogLoop($params)
    {
        $uid = $params['uid'];
        $ttid = $params['ttid'];
        $toolInfo = UserTool::instance()->getToolById($ttid);
        $tool_id = $toolInfo['tlid'];
        $data['tool_id'] = $tool_id;
        $userInfo = User::instance()->getName($uid);
        $data['uname'] = $userInfo['uname'];
        $data['channal_id'] = $userInfo['unid'];
        $data['mid'] = $userInfo['mid'];
        $data['tool_name'] = GiftContentSetting::instance()->getName($tool_id);
        $data['operate_type'] = ToolOperateLog::OPERATE_TYPE_EXPIRE;
        //这个ref要明确下业务定义
        $data['get_type'] =  $toolInfo['ref'];
        //定义这个source字段为json格式 商城购买时的格式为{desc:'',goods_id:0}，
        $data['get_type_desc']= (false !== ($source = @json_decode($toolInfo['source'])))?$source['desc']:$toolInfo['source'];
        $data['goods_id'] = $source['goods_id'];
        $locationLog = ToolUseLocationLog::instance()->getOne($ttid);
        if($locationLog)
        {//存在道具使用记录
            $data['use_location'] = ATCode::getLocationText($locationLog['game_id'],$locationLog['game_type_id'],$locationLog['game_room_id']);
            //道具操作时间
            $data['operate_time'] = $locationLog['operate_time'];
            //道具开始生效时间，暂时用operation_time字段
            $data['begin_time']   =   $locationLog['operate_time'];
            $data['expire_time'] = GiftContentSetting::instance()->getToolExpiration($tool_id,$data['begin_time']) ;

        }else{//不存在
            $data['use_location'] = '';
            $data['operate_time'] = time();
            $data['begin_time'] = 0;
            $data['expire_time'] = 0;
        }
        $data['before_num'] = 0;
        $data['after_num'] = UserTool::instance()->getToolNum($uid,$tool_id);
        $data['valid_duration'] = GiftContentSetting::instance()->getToolDuration($tool_id);
        ToolOperateLog::instance()->insert($data);
    }


    /**
     * 购买获取道具操作记日志
     * uid
     * ttid
     * before_num
     * after_num
     * operate_time
     */
    public function toolPurchaseLogLoop($params)
    {
        $data=array();
        $uid = $params['uid'];
        $ttid = $params['ttid'];
        $toolInfo = UserTool::instance()->getToolById($ttid);
        $tool_id = $toolInfo['tlid'];
        $data['tool_id'] = $tool_id;
        $userInfo = User::instance()->getName($uid);
        $data['uname'] = $userInfo['uname'];
        $data['channal_id'] = $userInfo['unid'];
        $data['mid'] = $userInfo['mid'];
        $data['tool_name'] = GiftContentSetting::instance()->getName($tool_id);
        $data['operate_type'] = ToolOperateLog::OPERATE_TYPE_GET;
        //这个ref要明确下业务定义
        $data['get_type'] =  $toolInfo['ref'];
        //定义这个source字段为json格式 商城购买时的格式为{desc:'',goods_id:0}，
        $data['get_type_desc']= (false !== ($source = @json_decode($toolInfo['source'])))?$source['desc']:$toolInfo['source'];
        $data['goods_id'] = isset($source['goods_id'])?$source['goods_id']:0;
        $data['use_location'] = '';
        $data['operate_time'] = $params['operate_time'];
        $data['begin_time'] = 0;
        $data['expire_time'] = 0;
        $data['before_num'] = $params['before_num'];
        $data['after_num'] =  $params['after_num'];
        $data['valid_duration'] = GiftContentSetting::instance()->getToolDuration($tool_id);
        ToolOperateLog::instance()->insert($data);
    }

    /**
     * 道具使用日志的消费
     * uid
     * ttid
     */
    public function toolUseLogLoop($params)
    {
        $data=array();
        $uid = $params['uid'];
        $ttid = $params['ttid'];
        $toolInfo = UserTool::instance()->getToolById($ttid);
        $tool_id = $toolInfo['tlid'];
        $data['tool_id'] = $tool_id;
        $userInfo = User::instance()->getName($uid);
        $data['uname'] = $userInfo['uname'];
        $data['channal_id'] = $userInfo['unid'];
        $data['mid'] = $userInfo['mid'];
        $data['tool_name'] = GiftContentSetting::instance()->getName($tool_id);
        $data['operate_type'] = ToolOperateLog::OPERATE_TYPE_USE;
        //这个ref要明确下业务定义
        $data['get_type'] =  $toolInfo['ref'];
        //定义这个source字段为json格式 商城购买时的格式为{desc:'',goods_id:0}，
        $data['get_type_desc']= (false !== ($source = @json_decode($toolInfo['source'])))?$source['desc']:$toolInfo['source'];
        $data['goods_id'] = $source['goods_id'];
        $locationLog = ToolUseLocationLog::instance()->getOne($ttid);
        if($locationLog)
        {//存在道具使用记录
            $data['use_location'] = ATCode::getLocationText($locationLog['game_id'],$locationLog['game_type_id'],$locationLog['game_room_id']);
            //道具操作时间
            $data['operate_time'] = $locationLog['operate_time'];
            //道具开始生效时间，暂时用operation_time字段
            $data['begin_time']   =   $locationLog['operate_time'];
            $data['expire_time'] = GiftContentSetting::instance()->getToolExpiration($tool_id,$data['begin_time']) ;
            $data['before_num'] = $locationLog['before_num'];
            $data['after_num'] =  $locationLog['after_num'];
        }else{//不存在
            $data['use_location'] = '';
            $data['operate_time'] = 0;
            $data['begin_time'] = 0;
            $data['expire_time'] = 0;
            $data['before_num'] = 0;
            $data['after_num'] =  0;
        }

        $data['valid_duration'] = GiftContentSetting::instance()->getToolDuration($tool_id);
        ToolOperateLog::instance()->insert($data);
    }

    /**
     * -------------道具同步入库 end-----------------
     */
    /**
     * -------------礼物同步入库 start-----------------
     */

    /**
     * 礼物变卖操作记录,这个操作需要先poker_user_gift入库,必须存在于用户记录中
     *
     * uid
     * gift_auto_id
     * operate_type
     * before_num
     * after_num
     * reason   变卖原因
     * operate_time
     */
    public function giftSellLogLoop($params)
    {
        $data = array();
        $data['uid'] = $params['uid'];
        $userInfo = User::instance()->getName($data['uid']);
        $data['uname'] = $userInfo['uname'];
        $data['channal_id'] = $userInfo['unid'];
        $data['mid'] = $userInfo['mid'];
        $giftInfo = UserGift::instance()->getOneById($params['gift_auto_id']);
        $data['gift_id'] = $giftInfo['gid'];
        $data['gift_name'] = ATCode::getGiftName($data['gift_id']);
        $data['operate_type'] = GiftLog::OPERATE_TYPE_SELL;
        $data['m_type'] = $giftInfo['m_type'];
        $data['before_num'] = $params['before_num'];
        $data['after_num'] = $params['after_num'];
        $data['give_uid'] = 0; //变卖时，没有用户

        $data['give_mid'] = 0;
        $data['desc'] = $giftInfo['reason'];
        $data['operate_time'] = $params['operate_time'];
        GiftLog::instance()->insert($data);
    }

    /**
     * 礼物赠送操作记录
     * uid
     * gift_id
     * operate_type
     * m_type
     * before_num
     * after_num
     * give_uid 被赠送人
     * reason
     * operate_time
     */
    public function giftPresentLogLoop($params)
    {
        $data = array();
        $data['uid'] = $params['uid'];
        $userInfo = User::instance()->getName($data['uid']);
        $data['uname'] = $userInfo['uname'];
        $data['channal_id'] = $userInfo['unid'];
        $data['mid'] = $userInfo['mid'];
        $data['gift_id'] = $params['gift_id'];
        $data['gift_name'] = ATCode::getGiftName($data['gift_id']);
        $data['operate_type'] = GiftLog::OPERATE_TYPE_PRESENT;
        $data['m_type'] = $params['m_type'];
        $data['before_num'] = $params['before_num'];
        $data['after_num'] = $params['after_num'];
        $data['give_uid'] = $params['give_uid'];
        $userInfo = User::instance()->getName($data['give_uid']);
        $data['give_mid'] = $userInfo['mid'];
        $data['desc'] = $params['reason'];
        $data['operate_time'] = $params['operate_time'];
        GiftLog::instance()->insert($data);
    }

    /**
     * 礼物接收操作记录，这个操作需要先poker_user_gift入库
     * uid
     * gift_auto_id (poker_user_gift自增id)
     * operate_type
     *before_num
     * after_num
     * give_uid
     */
    public function giftRecvLogLoop($params)
    {
        $data = array();
        $data['uid'] = $params['uid'];
        $userInfo = User::instance()->getName($data['uid']);
        $data['uname'] = $userInfo['uname'];
        $data['channal_id'] = $userInfo['unid'];
        $data['mid'] = $userInfo['mid'];
        $id = $params['gift_auto_id'];
        $giftInfo = UserGift::instance()->getOneById($id);
        $data['gift_id'] = $giftInfo['gid'];
        $data['gift_name'] = ATCode::getGiftName($data['gift_id']);
        $data['operate_type'] = GiftLog::OPERATE_TYPE_RECV;
        $data['m_type'] = $giftInfo['m_type'];
        $data['before_num'] = $params['before_num'];
        $data['after_num'] = $params['after_num'];
        $data['give_uid'] = $params['give_uid'];
        $userInfo = User::instance()->getName($data['give_uid']);
        $data['give_mid'] = $userInfo['mid'];
        $data['desc'] = $giftInfo['source'];
        $data['operate_time'] = $giftInfo['gettime'];
        GiftLog::instance()->insert($data);
    }

    /**
     * -------------礼物同步入库 end-----------------
     */
    /**
     * -------------好友操作同步入库 start-----------------
     */
    /**
     * 好友操作记录
     *
     * apply_id  poker_friend_apply的自增ID
     * f_operate_id  friend_operatenum_log的自增ID
     * t_operate_id  friend_operatenum_log的自增ID
     */
    public function friendLogLoop($params)
    {
        $data=array();
        $apply_id = $params['apply_id'];
        $f_operate_id = $params['f_operate_id'];
        $t_operate_id = $params['t_operate_id'];
        $applyInfo= FriendApply::instance()->getOneById($apply_id);
        if($applyInfo)
        {
            $data['fuid'] = $applyInfo['fuid'];
            $userInfo = User::instance()->getName($data['fuid']);
            $data['funame'] = $userInfo['uname'];
            $data['fchannal_id'] = $userInfo['unid'];
            $data['fmid'] = $userInfo['mid'];
            $data['tuid'] = $applyInfo['tuid'];
            $userInfo = User::instance()->getName($data['tuid']);
            $data['tuname'] = $userInfo['uname'];
            $data['tchannal_id'] = $userInfo['unid'];
            $data['tmid'] = $userInfo['mid'];

        }

        $operateLog = FriendOperateNumLog::instance()->getOneById($f_operate_id);
        if($operateLog)
        {
            if($operateLog['uid'] == $data['fuid'])
            {
                $data['operate_type'] = $operateLog['operate_type'];
                $data['ftype'] = $operateLog['ftype'];
                if( ($operateLog['operate_type'] == FriendLog::OPERATE_TYPE_FRIEND_MAKE) && false !== ($giftList=@json_decode($operateLog['gift_content'])))
                {
                    $data["gift_1_id"] = $giftList['id'];
                    $data["gift_1_num"] = $giftList['num'];
                }
                $data['fuid_vip'] = $operateLog['vip'];
                $data['tnd'] = $operateLog['num'];
                $data['operate_time'] = $applyInfo['operate_time'];
            }else{
                Logger::write("在表friend_operatenum_log中f_operate_id（id）:$f_operate_id 中用户信息不匹配 {$operateLog['uid']} == {$data['fuid']}",__METHOD__,"ERROR");
            }
        }else{
            Logger::write("f_operate_id（id）:$f_operate_id 在表friend_operatenum_log不存在",__METHOD__,'ERROR');
        }
        //同意人日志信息入库
        FriendLog::instance()->insert($data);


        $operateLog = FriendOperateNumLog::instance()->getOneById($t_operate_id);
        if($operateLog)
        {
            if($operateLog['uid'] == $data['tuid'])
            {
                $data['operate_type'] = $operateLog['operate_type'];
                $data['ftype'] = $operateLog['ftype'];
                if( ($operateLog['operate_type'] == FriendLog::OPERATE_TYPE_FRIEND_MAKE) && false !== ($giftList=@json_decode($operateLog['gift_content'])))
                {
                    $data["gift_1_id"] = $giftList['id'];
                    $data["gift_1_num"] = $giftList['num'];
                }
                $data['fuid_vip'] = $operateLog['vip'];
                $data['tnd'] = $operateLog['num'];
                $data['operate_time'] = $applyInfo['operate_time'];
            }else{
                Logger::write("在表friend_operatenum_log中t_operate_id（id）:$t_operate_id 中用户信息不匹配 {$operateLog['uid']} == {$data['tuid']}",__METHOD__,"ERROR");
            }
        }else{
            Logger::write("t_operate_id（id）:$t_operate_id 在表friend_operatenum_log不存在",__METHOD__,"ERROR");
        }
        //被同意者日志信息入库
        FriendLog::instance()->insert($data);

    }

    /**
     * -------------好友操作同步入库 end-----------------
     */
}