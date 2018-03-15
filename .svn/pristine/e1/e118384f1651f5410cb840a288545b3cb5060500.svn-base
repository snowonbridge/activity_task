<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-7
 * Time: 下午3:50
 */

namespace Workerman\Service;


use Workerman\Lib\Code;
use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Model\ActivityCategory;

class EveryDayTaskService extends Model{

    const CROSS_CHALLEGE_TYPE = 1;
    const CONSUMPTION_TYPE = 2;
    const FUNCTION_TYPE = 3;
    const SOCIAL_TYPE = 6;
    /**
     * @获取每日任务列表
     * @param $uid
     * @param int $user_role
     * @return array
     */
    public function getList($uid)
    {
        if($uid <=0)
            return [];
        $activityList = ActivityCategory::instance()->getEveryDayActivityList();
        if(!$activityList)
        {
            return array();
        }
//        $activityListIds = array_column($activityList,'activity_id');
        $result = [];
//        $activityListIds = array_map('intval',$activityListIds);
//        $activityListIds=[306];
        foreach($activityList as $item)
        {
            $activity_id = $item['activity_id'];
            $activity_type = $item['activity_type'];
            if($activity_type == self::CROSS_CHALLEGE_TYPE && CrossChallegeService::instance()->hasActivity($activity_id))
            {

                $list1 = CrossChallegeService::instance()->showJoinStatus($uid,$activity_id,date('Ymd'));

                if($list1)
                {
                    $t = array_shift($list1);
                    unset($t['challege_config_id']);
                    $t['gift'] = CrossChallegeService::instance()->showActivityGifts($activity_id);



                }
                $t['activity_id'] = $activity_id;
            }elseif($activity_type == self::CONSUMPTION_TYPE && ConsumptionService::instance()->hasActivity($activity_id))
            {
                $list2 = ConsumptionService::instance()->showJoinStatus($uid,$activity_id,date('Ymd'));
                if($list2)
                {
                    $t = array_shift($list2);
                    $t['gift'] = ConsumptionService::instance()->showActivityGifts($activity_id);
                    unset($t['challege_config_id']);


                }
                $t['activity_id'] = $activity_id;
            }elseif($activity_type == self::FUNCTION_TYPE && FunctionService::instance()->hasActivity($activity_id))
            {
                $list3 = FunctionService::instance()->showJoinStatus($uid,$activity_id);
                if($list3)
                {
                   $t = array_shift($list3);
                   $t['gift'] = FunctionService::instance()->showActivityGifts($activity_id);
                    unset($t['challege_config_id']);

                }
                $t['activity_id'] = $activity_id;
            }elseif($activity_type == self::SOCIAL_TYPE && SocialService::instance()->hasActivity($activity_id))
            {
                $list4 = SocialService::instance()->showJoinStatus($uid,$activity_id);
                if($list4)
                {
                    $t = array_shift($list4);
                    $t['gift'] = SocialService::instance()->showActivityGifts($activity_id);

                    unset($t['challege_config_id']);

                }
                $t['activity_id'] = $activity_id;
            }else{
                continue;
            }
            $t['total_num'] = isset( $t['total_num'])? (int)$t['total_num']:0;
            $t['achieve_num'] = isset( $t['achieve_num'])? (int)$t['achieve_num']:0;
            $t['achieve_status'] = isset( $t['achieve_status'])? $t['achieve_status']:0;
            $t['is_receive'] = isset( $t['is_receive'])? intval($t['is_receive']):0;
            $t['gift'] = isset( $t['gift'])? $t['gift']:[];

            unset($t['achieve_status'],$t['start_time'],$t['end_time'],$t['base_activity_id']);
            $result[] = $t;
        }
        return $result;
    }

    public function receiveGift($uid,$activity_id)
    {
        $type = $this->getActivityType($activity_id);
        switch($type)
        {
            case self::CONSUMPTION_TYPE:
                return ConsumptionService::instance()->receiveGift($uid,$activity_id);
                break;
            case self::CROSS_CHALLEGE_TYPE:

                $ret =  CrossChallegeService::instance()->receiveGift($uid,$activity_id);
                return $ret;
                break;
            case self::FUNCTION_TYPE:

                $ret =  FunctionService::instance()->receiveGift($uid,$activity_id);
                return $ret;
                break;
            case self::SOCIAL_TYPE:

                $ret =  SocialService::instance()->receiveGift($uid,$activity_id);
                return $ret;
                break;
            default:
                Logger::write('活动类型不支持领取奖励','receiveGift default',"ERROR");

        }
        return array('code'=>Code::NOT_ALLOWED_OPERATION,'msg'=>'活动类型不支持领取奖励','data'=>[]);

    }
    public   function getActivityType($activity_id)
    {
        $typeConfig = require(CFG_PATH . 'activity_type.php');

        foreach($typeConfig as $k=>$items)
        {
            foreach($items as $item)
                $typeArr[$item] = $k;
        }
        return isset($typeArr[$activity_id])?$typeArr[$activity_id]:0;
    }

    /**
     * @desc 获取活动列表可领取奖励状态
     * @param $uid
     * @return bool true represent 有可领取的活动奖励,false没有
     */
    public function getGiftStatus($uid)
    {
        $result = $this->getList($uid);

        if(!isset($result) || empty($result))
            return false;
        $counts = 0;
        foreach($result as $k=>$gift)
        {
            if($gift['is_receive'] == 2)
            {
                $counts++;
            }
        }
        return $counts;
    }



} 