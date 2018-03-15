<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-15
 * Time: 上午9:44
 */

namespace Workerman\Service;


use Workerman\Filter\MonthTaskOutFilter;
use Workerman\Lib\Code;
use Workerman\Lib\Model;
use Workerman\Model\ActivityCategory;
use Workerman\Model\ActivityChannel;
use Workerman\Model\ActivityControl;
use Workerman\Model\ActivityTabSetting;
use Workerman\Model\ConsumptionGift;
use Workerman\Model\CrossChallegeGift;
use Workerman\Model\FaControlAppList;
use Workerman\Model\FaControlAreaSetting;
use Workerman\Model\FaControlStoreSetting;
use Workerman\Model\User;
use Workerman\Model\UserConsumptionLog;
use Workerman\Lib\Logger;
use Workerman\Model\UserCrossChallegeLog;
use Workerman\Model\UserGame;
use Workerman\Service\ConsumptionService;
use Workerman\Service\CrossChallegeService;
use Workerman\Service\EveryDayTaskService;
use Workerman\Service\FunctionService;

use Workerman\Service\SocialService;
class MonthTaskService extends Model{


    private function getConsumptionList($uid,$activity)
    {
        $activity_id = $activity['activity_id'];
        $tab_id = $activity['tab_id'];
        $disable = $activity['disable'];//该活动是否已被禁用，如果是的话，则基于该活动也不显示
        if($disable)
        {
            return ['disable_activity_id'=>$activity['base_activity_id'],'is_show'=>0];
        }

        $list2 = ConsumptionService::instance()->showJoinStatus($uid,$activity_id,date('Ym'));
        if($list2)
        {
            $t = array_shift($list2);
            //忽略角色
            $t['gift'] = ConsumptionService::instance()->showActivityGifts($activity_id);
            unset($t['challege_config_id']);

        }else{
            Logger::write("未获取到用户uid: $uid,tab_id:$tab_id,对活动的参与进度 activity_id:$activity_id",__METHOD__,'ERROR');
            return ['is_show'=>0];
        }
        $t['disable_activity_id'] = 0;
        //
        if($t['is_receive'] != UserConsumptionLog::RECEIVE_YES)
        {//当前任务未启用或未领取，显示这个
            $t['disable_activity_id'] = $activity['base_activity_id'];
            $is_show = 1;
        }elseif($t['is_receive'] == UserConsumptionLog::RECEIVE_YES && $activity['base_activity_id'] == 0)
        {
            $is_show = 1;
        }elseif($t['is_receive'] == UserConsumptionLog::RECEIVE_YES && $activity['base_activity_id'] > 0)
        {
            $is_show = 0;
        }
        if(isset($is_show) && $is_show)
        {
            $t['tab_id'] = $tab_id;
            $t['is_show'] = 1;
            $t['redirect_id'] = intval($t['redirect_id']);
            $t['activity_id'] = intval($activity_id);
            $t['total_num'] = isset( $t['total_num'])? intval($t['total_num']):0;
            $t['achieve_num'] = isset( $t['achieve_num'])?(int)$t['achieve_num']:0;
            $t['is_receive'] = isset( $t['is_receive'])? intval($t['is_receive']):0;
            $t['gift'] = isset( $t['gift'])? $t['gift']:[];
            return $t;
        }else{
            return ['is_show'=>0];
        }
    }
    private function getCrossList($uid,$activity)
    {
        $activity_id = $activity['activity_id'];
        $tab_id = $activity['tab_id'];
        $disable = $activity['disable'];//该活动是否已被禁用，如果是的话，则基于该活动也不显示
        if($disable)
        {
            return ['disable_activity_id'=>$activity['base_activity_id'],'is_show'=>0];
        }

        $list2 = CrossChallegeService::instance()->showJoinStatus($uid,$activity_id,date('Ym'));
        if($list2)
        {
            $t = array_shift($list2);
            //忽略角色
            $t['gift'] = CrossChallegeService::instance()->showActivityGifts($activity_id);
            unset($t['challege_config_id']);

        }else{
            Logger::write("未获取到用户uid: $uid,tab_id:$tab_id,对活动的参与进度 activity_id:$activity_id",__METHOD__,'ERROR');
            return ['is_show'=>0];
        }
        $t['disable_activity_id'] = 0;
        //
        if($t['is_receive'] != UserCrossChallegeLog::RECEIVE_YES)
        {//当前任务未启用或未领取，显示这个
            $t['disable_activity_id'] = $activity['base_activity_id'];
            $is_show = 1;
        }elseif($t['is_receive'] == UserCrossChallegeLog::RECEIVE_YES && $activity['base_activity_id'] == 0)
        {
            $is_show = 1;
        }elseif($t['is_receive'] == UserCrossChallegeLog::RECEIVE_YES && $activity['base_activity_id'] > 0)
        {
            $is_show = 0;
        }
        if(isset($is_show) && $is_show)
        {
            $t['tab_id'] = $tab_id;
            $t['is_show'] = 1;
            $t['redirect_id'] = intval($t['redirect_id']);
            $t['activity_id'] = intval($activity_id);
            $t['total_num'] = isset( $t['total_num'])? intval($t['total_num']):0;
            $t['achieve_num'] = isset( $t['achieve_num'])? $t['achieve_num']:0;
            $t['is_receive'] = isset( $t['is_receive'])? intval($t['is_receive']):0;
            $t['gift'] = isset( $t['gift'])? $t['gift']:[];
            return $t;
        }else{
            return ['is_show'=>0];
        }

    }
    public function getList($uid,$sid,$version,$unid)
    {
        $tabList = ActivityTabSetting::instance()->getAll();
        $monthActivityList = ActivityCategory::instance()->getMonthActivityList();
        $now = time();

        $itemList=[];
        $disabled=[];
        $cross_activity_ids = array_map(function($v){
            if($v['activity_type'] == EveryDayTaskService::CROSS_CHALLEGE_TYPE)
                return $v['activity_id'];
            else
                return false;
        },$monthActivityList);
        //批量获取activity_id
        $cross_activity_ids = array_keys(array_filter($cross_activity_ids));
        $consumption_activity_ids = array_map(function($v){
            if($v['activity_type'] == EveryDayTaskService::CONSUMPTION_TYPE)
                return $v['activity_id'];
            else
                return false;
        },$monthActivityList);
        $consumption_activity_ids = array_keys(array_filter($consumption_activity_ids));
        $cross_base_ids = CrossChallegeGift::instance()->getBaseActivityIds($cross_activity_ids);
        $consumption_base_ids = ConsumptionGift::instance()->getBaseActivityIds($consumption_activity_ids);
        foreach($monthActivityList as $activity_id=>$activity)
        {
            if( (!($activity['start_time'] <= $now) &&($now <= $activity['end_time'] )) )
            {
                continue;
            }
            !isset($itemList[$activity['tab_id']]['title']) &&$itemList[$activity['tab_id']]['title'] = $tabList[$activity['tab_id']]['title'];
            !isset($itemList[$activity['tab_id']]['start_time']) && $itemList[$activity['tab_id']]['start_time'] = (int)$activity['start_time'];
            !isset($itemList[$activity['tab_id']]['end_time']) && $itemList[$activity['tab_id']]['end_time'] = (int)$activity['end_time'];
            !isset($itemList[$activity['tab_id']]['tab_id']) && $itemList[$activity['tab_id']]['tab_id'] = (int)$activity['tab_id'];

            $activity['disable'] = isset($disabled[$activity_id]) && $disabled[$activity_id]?1:0;

            if($activity['activity_type'] == EveryDayTaskService::CONSUMPTION_TYPE)
            {
                $activity['base_activity_id'] = isset($consumption_base_ids[$activity_id])?$consumption_base_ids[$activity_id]:0;
                $ret = $this->getConsumptionList($uid,$activity);
            }elseif($activity['activity_type'] == EveryDayTaskService::CROSS_CHALLEGE_TYPE){
                $activity['base_activity_id'] = isset($cross_base_ids[$activity_id])?$cross_base_ids[$activity_id]:0;
                $ret = $this->getCrossList($uid,$activity);

            }else{
                continue;
            }
            if(isset($ret['disable_activity_id']) && $ret['disable_activity_id'])
            {//如果存在下一个活动，且未开启，设置标志位1,
                $disabled[$ret['disable_activity_id']] = 1;
            }
            if(isset($ret['is_show']) && $ret['is_show'])
            {
                unset($ret['start_time'],$ret['end_time'],$ret['is_show'],$ret['disable_activity_id'],$ret['tab_id'],$ret['base_activity_id']);
                $itemList[$activity['tab_id']]['items'][] = $ret;
            }



        }
        ksort($itemList);
        //最后，对过滤规则进行验证，暂时只对tab=3进行过滤
        $itemList[ATCode::TAB_COMSUMPTION_REWARD]['items'] = $this->checkRules($uid,$sid,$version,$unid,$itemList[ATCode::TAB_COMSUMPTION_REWARD]['items']);
        $itemList[ATCode::TAB_PLAY_AND_MAKE_MONEY]['items'] = $this->checkRules($uid,$sid,$version,$unid,$itemList[ATCode::TAB_PLAY_AND_MAKE_MONEY]['items']);
        return isset($itemList)?array_values($itemList):[];
    }
    public function receiveGift($uid,$activity_id)
    {


        $type = EveryDayTaskService::instance()->getActivityType($activity_id);
        switch($type)
        {
            case EveryDayTaskService::CONSUMPTION_TYPE:
                $ret = ConsumptionService::instance()->receiveGift($uid,$activity_id,date('Ym'));
                if($ret['code'] == Code::SUCCESS)
                {
                    $next_activity_id = ConsumptionGift::instance()->getNextActivityId($activity_id);
                    if(!$next_activity_id)
                    {
                        return array('code'=>Code::SUCCESS,'msg'=>'领取成功','data'=>[]);
                    }
                    $list2 = ConsumptionService::instance()->showJoinStatus($uid,$next_activity_id,date("Ym"));
                    if($list2)
                    {
                        $t = array_shift($list2);
                        //忽略角色
                        $t['gift'] = ConsumptionService::instance()->showActivityGifts($next_activity_id);
                        unset($t['challege_config_id'],$t['achieve_status']);
                        $t['activity_id'] = intval($next_activity_id);
                        $t['total_num'] = isset( $t['total_num'])? $t['total_num']:0;
                        $t['achieve_num'] = isset( $t['achieve_num'])? $t['achieve_num']:0;
                        $t['is_receive'] = isset( $t['is_receive'])? intval($t['is_receive']):0;
                        $t['gift'] = isset( $t['gift'])? $t['gift']:[];
                        return array('code'=>Code::SUCCESS,'msg'=>'领取成功','data'=>$t);
                    }
                }
                return array('code'=>$ret['code'],'msg'=>$ret['msg'],'data'=>$ret['data']);
                break;
            case EveryDayTaskService::CROSS_CHALLEGE_TYPE:

                $ret =  CrossChallegeService::instance()->receiveGift($uid,$activity_id,date('Ym'));
                if($ret['code'] == Code::SUCCESS)
                {
                    $next_activity_id = CrossChallegeGift::instance()->getNextActivityId($activity_id);
                    if(!$next_activity_id)
                    {
                        return array('code'=>Code::SUCCESS,'msg'=>'领取成功','data'=>[]);
                    }
                    $list2 = CrossChallegeService::instance()->showJoinStatus($uid,$next_activity_id,date("Ym"));
                    if($list2)
                    {
                        $t = array_shift($list2);
                        //忽略角色
                        $t['gift'] = CrossChallegeService::instance()->showActivityGifts($next_activity_id);
                        unset($t['challege_config_id'],$t['achieve_status']);
                        $t['activity_id'] = intval($next_activity_id);
                        $t['total_num'] = isset( $t['total_num'])? $t['total_num']:0;
                        $t['achieve_num'] = isset( $t['achieve_num'])? $t['achieve_num']:0;
                        $t['is_receive'] = isset( $t['is_receive'])? intval($t['is_receive']):0;
                        $t['gift'] = isset( $t['gift'])? $t['gift']:[];
                        return array('code'=>Code::SUCCESS,'msg'=>'领取成功','data'=>$t);
                    }
                }
                return array('code'=>$ret['code'],'msg'=>$ret['msg'],'data'=>$ret['data']);
                break;
//            case EveryDayTaskService::FUNCTION_TYPE:
//
//                $ret =  FunctionService::instance()->receiveGift($uid,$activity_id,date('Ym'));
//                return $ret;
//                break;
//            case EveryDayTaskService::SOCIAL_TYPE:
//
//                $ret =  SocialService::instance()->receiveGift($uid,$activity_id,date('Ym'));
//                return $ret;
//                break;
            default:
                Logger::write('活动类型不支持领取奖励','receiveGift default',"ERROR");
                return array('code'=>Code::NOT_ALLOWED_OPERATION,'msg'=>'活动类型不支持领取奖励','data'=>[]);

        }

    }

    /**
     * @desc 获取活动列表可领取奖励状态
     * @param $uid
     * @return bool true represent 有可领取的活动奖励,false没有
     */
    public function getGiftStatus($uid,$sid,$version,$unid)
    {
        $result = $this->getList($uid,$sid,$version,$unid);
        if(!isset($result) || empty($result))
            return false;
        $counts = 0;
        foreach($result as $k=>$giftList)
        {
            foreach($giftList['items'] as $k1=>$gift)
            {
                if($gift['is_receive'] == 2)
                {
                    $counts ++;
                }
            }
        }
        return $counts;
    }

    /**
     * @desc 检查月活动是否在显示规则内
     * @param $uid
     * @param $activityList
     */
    public function checkRules($uid,$sid,$version,$unid,$activityItemList)
    {
        $result = MonthTaskOutFilter::instance()->exec($uid,$sid,$version,$unid,$activityItemList);
        return $result;
    }

} 