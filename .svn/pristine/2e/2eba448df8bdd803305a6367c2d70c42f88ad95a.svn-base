<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/4/3
 * Time: 17:41
 */

namespace Workerman\Service;


use Workerman\Lib\Code;
use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Model\GiftContentSetting;
use Workerman\Model\User;
use Workerman\Model\UserGame;
use Workerman\Model\UserTool;
use Workerman\Model\VipPrivsSetting;
use Workerman\Model\VipRecvGiftLog;
use Workerman\Model\VipSetting;

class VipService extends Model
{
    /**
     * @desc 获取VIP介绍列表
     * @param $uid
     * @return bool
     */
    public function getList($uid)
    {
        $user_info = UserGame::instance()->getOne($uid);
        $user_vip = $user_info['vip'];
        //用户当前的积分和VIP等级
        $result['charge'] = (float)$user_info['charge']?:0;
        $result['vip']    = (int)$user_vip?:0;
        $privs_list = VipPrivsSetting::instance()->getAll();

        $vip_list = VipSetting::instance()->getAll();
        $max_vip = max(array_column($vip_list,'vip'));
        foreach ($vip_list as &$item)
        {
            if($item['vip'] == $user_vip+1 && $max_vip>=$user_vip+1)
            {
                $result['next_vip'] = $item['vip'];
                $result['next_charge'] = $item['charge'];
                $result['balance'] = $result['next_charge'] - $result['charge'];
            }elseif($max_vip<=$user_vip){
                //下级VIP等级
                $result['next_vip'] = $max_vip;
                //下级VIP积分下限
                $result['next_charge'] = $item['charge'];
                //差额
                $result['balance'] = 0;
            }
            $privs = explode(',',$item['privs']);
            $item['privs_text']='';
            foreach ($privs as $priv_id)
            {
                isset($privs_list[$priv_id]) && ($item['privs_text'] .="{$privs_list[$priv_id]['name']}&");
            }
            $item['privs_text'] = rtrim($item['privs_text'],"&");
        }
        foreach ($vip_list as &$item)
        {
            unset($item['id'],$item['privs']);
        }
        $result['list'] = $vip_list;
        return  $result;
    }

    /**
     * @获取用户当前VIP等级可补足的奖励配置
     * @param $uid
     */
    public function getGift($uid)
    {
        $user_info = UserGame::instance()->getOne($uid);
        $user_vip = $user_info['vip'];
        $setting = VipSetting::instance()->getOneByVip($user_vip);
        $privs_ids = explode(',',$setting['privs']);
        $result=[];
        $now = time();
        $today = date("Ymd",$now-3600);
        $recv_log = VipRecvGiftLog::instance()->getOne($uid,$user_vip>=1?$user_vip-1:0,$today);
        if($recv_log)
        {//当天如果升级，判断之前vip等级是否有领取过，没有领取过则可以领取，领取过了则不能领取
            return [];
        }
        if($privs_ids)
        {
            $privs_setting = VipPrivsSetting::instance()->getAllByids($setting['privs']);
            //已补足的列表
            $recv_log = VipRecvGiftLog::instance()->getOne($uid,$user_vip,$today);
            $recv_priv_ids = array_column($recv_log,'priv_id');

            foreach ($privs_setting as $item)
            {
                if(!in_array($item['id'],$recv_priv_ids))
                {
                    $result[] = $item;
                }
            }
        }
        foreach ($result as &$item)
        {
            $item['num'] = (int)$item['num'];
            $item['gift_id'] =  (int)$item['gift_id'];
            $item['id'] =  (int)$item['id'];
        }
        return $result;
    }

    /**
     * 领取VIP每日登陆奖励
     * 每日凌晨1：00后玩家首次上线,算做当天
     * @param $uid
     * @param $day
     */
    public function recv($uid)
    {
        $setting = $this->getGift($uid);
        if(!$setting)
        {
            Logger::write("当天已补足,明天在补",__METHOD__,"INFO");
            return [];
        }
        $user_info = UserGame::instance()->getOne($uid);
        $now = time();
        $today = date("Ymd",$now-3600);
        $result=[];
        foreach($setting as $item)
        {
            if($item['id']<= 0)
                continue;

            switch ($item['gift_id'])
            {
                case GiftContentSetting::GIFT_TYPE_MONEY://金币补足
                    $user_num = $user_info['chip'];
                    break;
                /**
                 * 道具数量获取
                 */
                case GiftContentSetting::GIFT_TYPE_REMENBCARD_II:
                case GiftContentSetting::GIFT_TYPE_REMENBCARD_III:
                case GiftContentSetting::GIFT_TYPE_REMENBCARD ://计牌器补足
                case GiftContentSetting::GIFT_TYPE_JBCARD:
                    $user_num =UserTool::instance()->getToolNum($uid,$item['gift_id']);
                    break;
                case GiftContentSetting::GIFT_TYPE_DIAMAND://钻石补足
                    $user_num = $user_info['diamond'];
                    break;
                default://
                    Logger::write("没有 {$item['gift_id']} 的补足配置",__METHOD__,'INFO');
            }
            if($user_num < $item['num'])
            {
                $data=[];
                $data['uid'] = $uid;
                $data['priv_id'] = $item['id'];
                $data['vip'] = $user_info['vip'];
                $data['gift_id']=$item['gift_id'];
                $data['gift_num']=$item['num']-$user_num;
                $data['receive_status']=0;
                $data['day_time'] = $today;
                $last_id = VipRecvGiftLog::instance()->add($data);
                if(!$last_id)
                {
                    Logger::write("添加预领取日志失败",__METHOD__,'ERROR');
                    return ['code'=>Code::SUCCESS,'msg'=>"添加预领取{$item['gift_id']}日志失败",'data'=>$result];

                }
                $ret = GiftService::instance()->insertGift($uid,$item['gift_id'],$item['num']-$user_num,'VIP每日首次登录领取',ATCode::CLMODE_VIP_LOGIN);
                if($ret['code'] != Code::SUCCESS)
                {
                    Logger::write("发送补足 {$item['gift_id']} 的时候出现异常：{$ret['code']},{$ret['msg']}",__METHOD__,'ERROR');
                    return ['code'=>$ret['code'],'msg'=>"发送补足 {$item['gift_id']} 的时候出现异常：{$ret['code']},{$ret['msg']}",'data'=>$result];
                }
                $s = VipRecvGiftLog::instance()->updateStatus($last_id);
                if(false === $s)
                {
                    Logger::write("更新领取状态失败 last_id:$last_id",__METHOD__,'ERROR');
                }
                $result[] = ['id'=>$item['gift_id'],'num'=>$item['num']-$user_num];
            }else{
                Logger::write("用户 {$item['gift_id']} 不需要补足",__METHOD__,'INFO');
            }

        }
        return ['code'=>Code::SUCCESS,'msg'=>"领取成功",'data'=>$result];

    }

}