<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/3/27
 * Time: 10:50
 */

namespace Workerman\Service;


use Workerman\Lib\Code;
use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Model\CheckinSetting;
use Workerman\Model\User;
use Workerman\Model\UserCheckinDaysLog;

class CheckInService extends Model
{
    /**
     * @desc 获取签到奖励列表
     * @param $uid
     * @param $platform_id
     * @return array
     */
    public function getList($uid,$platform_id)
    {
        if(!$uid || !$platform_id)
        {
            Logger::write("参数非法",__METHOD__,"ERROR");
            return ['code'=>Code::CODEERRPARAM,'msg'=>"参数非法 uid:$uid ,platform_id:$platform_id",'data'=>[]];
        }
        $user = User::instance()->getOneByUid($uid);
        $channel_id = $user['unid'];
        $register_way_id = $user['usertype'];

        //已签到天数
//        $days = empty($log)?0:($log['days']+1)%7;
        $setting= CheckinSetting::instance()->getOne($channel_id,$register_way_id,$platform_id);
        if(!$setting)
        {
            return ['code'=>Code::DATAEXCEPTION,'msg'=>"配置缺失 uid:$uid ,channel_id：$channel_id,register_way_id:$register_way_id,platform_id:$platform_id",'data'=>[]];
        }
        $log = UserCheckinDaysLog::instance()->getOne($uid,$setting['rule_id']);
        $total_days = $setting['days'];
        //第几轮领取
        $days = empty($log)?0:$log['days'];
        $gift_list = json_decode($setting['gift_content'],true);
        foreach ($gift_list as &$item)
        {
            $item['is_received'] = 0;
        }
        //表示已经修改过配置
        if($log && $setting['update_time'] > $log['update_time'])
        {
            return ['code'=>Code::SUCCESS,'msg'=>"获取签到奖励列表",'data'=>['total_days'=>$total_days,'days'=>0,'gift_list'=>$gift_list]];
        }
        //获取领取状态
        $user_gift_status_list = UserCheckinDaysLog::instance()->getGiftStatus($uid,$setting['rule_id']);
        foreach ($gift_list as &$item)
        {
            if(isset($user_gift_status_list[$item['day_nd']]) && $user_gift_status_list[$item['day_nd']] == 1)
            {
                $item['is_received'] = 1;
            }
        }
        return ['code'=>Code::SUCCESS,'msg'=>"获取签到奖励列表",'data'=>['total_days'=>(int)$total_days,'days'=>(int)$days,'gift_list'=>$gift_list]];

    }

    /**
     * @desc 领取签到奖励
     * @param $uid
     */
    public function receiveGift($uid,$platform_id)
    {
        if(!$uid )
        {
            Logger::write("参数非法",__METHOD__,"ERROR");
            return ['code'=>Code::CODEERRPARAM,'msg'=>"参数非法 uid:$uid ",'data'=>[]];
        }
        $user = User::instance()->getOneByUid($uid);
        $channel_id = $user['unid'];
        $register_way_id = $user['usertype'];
        $has_checkin = $this->hasCheckin($uid,$channel_id,$register_way_id,$platform_id);
        if($has_checkin)
        {
            return ['code'=>Code::NOT_ALLOWED_OPERATION,'msg'=>"当天已领取签到奖励",'data'=>[]];
        }
        $setting= CheckinSetting::instance()->getOne($channel_id,$register_way_id,$platform_id);
        if(!$setting)
        {
            return ['code'=>Code::DATAEXCEPTION,'msg'=>"配置缺失 uid:$uid ,channel_id：$channel_id,register_way_id:$register_way_id,platform_id:$platform_id",'data'=>[]];
        }
        $day_nd = UserCheckinDaysLog::instance()->checkin($uid,$setting);
        if(!$day_nd)
        {
            return ['code'=>Code::OPERATEEXCEPTION,'msg'=>"逻辑异常",'data'=>[]];
        }
        $gift_list = json_decode($setting['gift_content'],true);
        $list = [];
        foreach ($gift_list as $k=>$item)
        {
            if($item['day_nd'] == $day_nd)
            {
                $list = $item['list'];
                break;
            }
        }
        foreach ($list as $item)
        {
            $gift_content_id = $item['id'];
            $num = $item['num'];
            GiftService::instance()->insertGift($uid,$gift_content_id,$num,'签到奖励'."第 {$day_nd} 次",ATCode::CLMODE_ACTIVITY_CHECKIN);
        }


        return ['code'=>Code::SUCCESS,'msg'=>"当天领取签到奖励成功",'data'=>$list];
    }
    /**
     * @desc 当天是否已签到
     * @param $uid
     * @param $rule_id
     */
    public function hasCheckin($uid,$channel_id,$register_way_id,$platform_id)
    {

        $setting= CheckinSetting::instance()->getOne($channel_id,$register_way_id,$platform_id);
        $last_one = UserCheckinDaysLog::instance()->getOne($uid,$setting['rule_id']);
        if(!$last_one || $setting['update_time']>$last_one['update_time'])
        {
            Logger::write("没有签到记录或修改过配置",__METHOD__,"WARN");
            return 0;
        }
        $last_cycle = $last_one['cycles'];
        $today = mktime(0,0,0,date("m"),date("d"),date("Y"));
        //是否有当天的更新记录
        if($last_one['update_time']>$today)
        {
            Logger::write("当天已签到奖励",__METHOD__,"WARN");
            return 1;
        }else{

            return 0;
        }

    }
}