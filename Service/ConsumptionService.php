<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-8
 * Time: 下午4:44
 */

namespace Workerman\Service;


use Workerman\Lib\Code;
use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Model\ConsumptionConfig;
use  Workerman\Model\ConsumptionGift;
use Workerman\Model\CrossChallegeGift;
use  Workerman\Model\GameActivityMap;
use  Workerman\Model\UserConsumptionLog;
use  Workerman\Model\GiftContentSetting;
use Workerman\Model\UserExchangeLog;
use Workerman\Model\UserPurchaseDiamandLog;
use Workerman\Model\UserUseJbcardLog;
use Workerman\Model\UserUseRemindcardLog;
use Workerman\Model\UserUseRoomcardLog;

/**
 * @desc 消费类活动
 * Class ConsumptionService
 * @package Simple\Services\ActivityTask
 */
class ConsumptionService extends Model{

    /**
     * @param array $params
     * @param int $params:uid
     * @param int $params:from_gift_id
     * @param int $params:action_id
     * @param int $params:target_gift_id
     * @param int $params:from_num
     * @param int $params:magic
     * @param int $params:login_check
     * @param int $params:time时间戳
     * @param int $params:
     * @return array
     */
    public function addLog($params=array())
    {

        $uid = $params['uid'];
        $activity_id = $params['activity_id'];

        $add_time = $params['add_time']= isset($params['time'])?$params['time']:time();


        if(ConsumptionGift::instance()->isMonthTask($activity_id))
        {//月活动
            $add_time = date('Ym',$add_time);
            //暂时是验证有效时间
            if(!ConsumptionGift::instance()->validConfig($activity_id,$params))
            {
                Logger::write('不满足该月活动条件',__METHOD__);
                return array('code'=>Code::NOT_ALLOWED_OPERATION,'msg'=>'不满足该月活动条件','data'=>[]);
            }
            //对月活动进行累计数量
            $from_num = $this->getFromNum($params);

            $params['from_num'] = $from_num;
        }else{//每日任务
            $add_time = date('Ymd',$add_time);
            //获取原货币或道具数量

        }


        $consumptions_gift = ConsumptionGift::instance()->getOne($activity_id);
        if(false !== strpos($consumptions_gift['actions_list'],','))
        {
            $actions_list = explode(',',trim($consumptions_gift['actions_list'],','));
        }else{
            $actions_list = [$consumptions_gift['actions_list']];
        }
        foreach($actions_list as $k=>$v)
        {
            if(false !== strpos($v,'&'))
            {
                $items = explode('&',$v);
                foreach($items as $consumption_config_id)
                {
                    //验证条件配置
                    if(!ConsumptionConfig::instance()->validConfig($consumption_config_id,$params))
                        continue;
                    $idarr[] = $consumption_config_id;
                }

            }else{
                //验证条件配置
                if(!ConsumptionConfig::instance()->validConfig($v,$params))
                {

                    continue;
                }


                $idarr[] = $v;
            }
        }
        if(!isset($idarr))
        {
            Logger::write('不存在满足条件的消费类活动',__METHOD__);
            return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'不存在满足条件的消费类活动','data'=>[]);
        }
        $log = UserConsumptionLog::instance()->getEveryDayOne($uid,$activity_id,$add_time);

        if($log)
        {//#如果已存在记录  achieve_list{config_id:1}
            if(UserConsumptionLog::instance()->hasNoReceived($uid,$activity_id,$add_time))
            {
                if(!empty($log['achieve_list']))
                {
                    $achieve_list = json_decode($log['achieve_list'],true);
                }else
                    $achieve_list=array();
                foreach($idarr as $id)
                {
                    if(!isset($achieve_list[$id]))
                    {
                        $achieve_list[$id] = 1;//已完成

                    }

                }
                if(UserConsumptionLog::RECEIVE_NO === $log['is_receive'])
                {
                    Logger::write('已经是可领取状态',__METHOD__);
                    return array('code'=>Code::CODEREQUESTTOOMANY,'msg'=>'已经是可领取状态','data'=>$log);
                }
                //验证任务是否已完成
                $isAchieved = $this->validateTaskAchieved($achieve_list,$log['challege_list']);
                if($isAchieved)
                {
                    $data['is_receive'] = UserConsumptionLog::RECEIVE_NO;//可领取状态
                }
                $data['achieve_list'] = json_encode($achieve_list);
                $ret = UserConsumptionLog::instance()->updateStatus($log['id'],$data);
                $log['achieve_list'] = $data['achieve_list'];
                if(false === $ret)
                {
                    Logger::write('添加任务进度失败',__METHOD__);
                    return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'添加任务进度失败','data'=>$log);
                }

                return array('code'=>Code::SUCCESS,'msg'=>'添加任务进度成功','data'=>$log);
            }
            if($log['current_frequency'] <= $log['frequency']-1)
                $new_frequency = $log['current_frequency'] +1;
            else{
                Logger::write('频率超出限制',__METHOD__);
                return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'频率超出限制','data'=>$log);
            }

        }else{
            $new_frequency = 1;

        }

        $achieve_list = [];
        foreach($idarr as $id)
        {
            $achieve_list[$id] =1;//已完成

        }
        $data= array();
        $data['uid']=$uid;
        $data['activity_id']=$activity_id;
        //暂时默认只有一个条件，没有组合情况
        $configInfo = ConsumptionConfig::instance()->getOne($idarr[0]);
        $data['challege_list'] = $consumptions_gift['actions_list'];
        $data['achieve_list'] = json_encode($achieve_list);
        $data['gift_list'] = $configInfo['gift_list'];

        $data['is_receive'] = $this->validateTaskAchieved($data['achieve_list'],$consumptions_gift['actions_list'])?UserConsumptionLog::RECEIVE_NO:UserConsumptionLog::RECEIVE_FORBID;
        $data['frequency'] = $configInfo['frequency'];
        $data['current_frequency'] = $new_frequency;
        $data['img_icon'] = $consumptions_gift['img_icon'];
        $data['add_time'] = $add_time;
        $ret=UserConsumptionLog::instance()->addEveryTask($data);
        if(!$ret)
        {
            Logger::write('添加任务进度失败2',__METHOD__);
            return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'添加任务进度失败2','data'=>$log);
        }

        return array('code'=>Code::SUCCESS,'msg'=>'添加任务进度成功','data'=>$log);
    }

    /**
     * @desc 验证某个任务是否已完成
     * @param array $achieve_list
     * @param array $challege_list
     */
    public function validateTaskAchieved($achieve_list=array(),$challege_list='')
    {
        if(!$challege_list)
            return false;
        if(!is_array($challege_list) && !is_object($challege_list) )
        {
            if(false === strpos($challege_list,','))
                $challege_list = [$challege_list];
            else
                $challege_list = explode(',',trim($challege_list,','));
        }
        if(is_string($achieve_list))
        {
            $achieve_list = json_decode($achieve_list,true);
        }


        foreach($challege_list as $k=>$v)
        {
            if(false !== strpos($v,'&'))
            {
                $items = explode('&',$v);
                $y=1;
                foreach($items as $item)
                {
                   if( ! $achieve_list[$item])
                   {
                       $y = 0;
                       break;
                   }

                }
                if($y)
                    return true;
            }else{
                if(isset($achieve_list[$v]) && $achieve_list[$v])
                   return true;
            }
        }

        return false;
    }
    /**
     * @desc 显示所有每日任务活动的完成状态
     * @param $uid
     * @param $user_role
     */
    public function showActivityStatus($uid,$user_role=0)
    {
        $gifts = ConsumptionGift::instance()->getAll();

        if(!$gifts)
            return array();
        $activity_ids = array_column($gifts,'activity_id');
        $logs = UserConsumptionLog::instance()->getEveryDayAll($uid,$activity_ids);
        $tmp=[];
        foreach($logs as $k=>$v)
        {
            $tmp[$v['activity_id']] = $v;
        }
        $logs = $tmp;unset($tmp);
        $result=[];
        foreach($gifts as $k=>$item)
        {

            $result[$k]['activity_id'] = $item['activity_id'];
            $result[$k]['is_receive'] = isset($logs[$item['activity_id']]['is_receive'])?$logs[$item['activity_id']]['is_receive']:UserConsumptionLog::RECEIVE_FORBID;
            $result[$k]['img_icon'] = $item['img_icon'];
            $result[$k]['gift_list'] = $this->showActivityGifts( $item['activity_id']);
        }

        return $result;
    }

    /**
     * @desc 显示活动奖品,消费活动暂时不支持多个活动配置的与逻辑组合
     * @param $activity_id
     */
    public function showActivityGifts($activity_id)
    {
        $giftInfo = ConsumptionGift::instance()->getOne($activity_id);
        if(false !== strpos($giftInfo['actions_list'],','))
        {
            $actions_list = explode(',',trim($giftInfo['actions_list'],','));
        }else{
            $actions_list = [$giftInfo['actions_list']];
        }
        foreach($actions_list as $id)
        {
            $config = ConsumptionConfig::instance()->getOne($id);

            if($config )
            {
                $giftList = $config['gift_list'];
                break;
            }
        }

        $result=[];
        if(isset($giftList) && is_array(json_decode($giftList,true)))
        {
            $giftList = json_decode($giftList,true);
            foreach($giftList as $k=>$v)
            {
                $r = GiftContentSetting::instance()->getOne($k);
                if($r)
                {

                    $t['name']    = $r['name'];
                    $t['num']     = $this->transGiftNum($k,$v);
                    $t['id']      = $k;
                    $result[]=$t;
                }
            }
        }
        return $result;
    }
    /**
     * @desc 显示活动的参与进度
     * @param $uid
     * @param $activity_id
     */
    public function showJoinStatus($uid,$activity_id,$add_time='')
    {
        $log = UserConsumptionLog::instance()->getEveryDayOne($uid,$activity_id,$add_time);

        if($log)
        {
            $achieve_list = json_decode($log['achieve_list'],true);
        }else{
            $achieve_list = array();
        }

        $giftConfig = ConsumptionGift::instance()->getOne($activity_id);

        if(false !== strpos($giftConfig['actions_list'],','))
        {
            $challege_list = explode(',',trim($giftConfig['actions_list'],','));
        }else{
            $challege_list = [$giftConfig['actions_list']];
        }

        if(!$challege_list)
            return array();
        foreach($challege_list as $k=>$v)
        {
            if(false !== strpos($v,'&'))
            {
                $items = explode('&',$v);
                foreach($items as $item)
                {
                    $t['is_receive'] = isset($log['is_receive'])?$log['is_receive']:UserConsumptionLog::RECEIVE_FORBID;
                    $t['achieve_status'] = isset($achieve_list[$item])?$achieve_list[$item]:0;
                    $t['challege_config_id'] = $item;
                    $config = ConsumptionConfig::instance()->getOne($item);
                    $t['total_num'] = $this->transTotalNum($activity_id,$config);

                    $t['achieve_num'] =  $this->showAchieveNum($uid,$activity_id,$config);

                    $t['desc'] = $giftConfig['desc'];
                    $t['redirect_id'] = $giftConfig['redirect_id'];
                    $t['base_activity_id'] = $giftConfig['base_activity_id'];
                    $t['start_time'] =(int) $giftConfig['start_time'];
                    $t['end_time'] =(int) $giftConfig['end_time'];
                    $t['redirect_id'] =(int) $giftConfig['redirect_id'];
                    $ct[]=$t;
                }
               $result[] = $ct;
            }else{
                $t['is_receive'] = isset($log['is_receive'])?$log['is_receive']:UserConsumptionLog::RECEIVE_FORBID;
                $t['achieve_status'] = isset($achieve_list[$v])?$achieve_list[$v]:0;
                $t['challege_config_id'] = $v;
                $config = ConsumptionConfig::instance()->getOne($v);
                $t['total_num'] = $this->transTotalNum($activity_id,$config);

                $t['achieve_num'] =  $this->showAchieveNum($uid,$activity_id,$config);
                if($t['is_receive'] == UserConsumptionLog::RECEIVE_FORBID && $t['achieve_num']>=$t['total_num'])
                {

                    $data= array();
                    $data['uid']=$uid;
                    $data['activity_id']=$activity_id;
                    //暂时默认只有一个条件，没有组合情况
                    $data['challege_list'] = $giftConfig['actions_list'];
                    $data['achieve_list'] = json_encode([$v=>1]);
                    $data['gift_list'] = $config['gift_list'];

                    $data['is_receive'] = UserConsumptionLog::RECEIVE_NO;
                    $data['frequency'] = $config['frequency'];
                    $data['current_frequency'] = 1;
                    $data['img_icon'] = $giftConfig['img_icon'];

                    if(ConsumptionGift::instance()->isMonthTask($activity_id))
                    {//月活动
                        $add_time = date('Ym');
                    }else{//每日任务
                        $add_time = date('Ymd');
                    }
                    $data['add_time'] = $add_time;

                    if(!UserConsumptionLog::instance()->getEveryDayOne($uid,$activity_id,$add_time) )
                    {

                        $ret=UserConsumptionLog::instance()->addEveryTask($data);
                        if($ret)
                        {

                            $t['is_receive'] = UserConsumptionLog::RECEIVE_NO;

                        }
                    }
                }

                $t['desc'] = $giftConfig['desc'];
                $t['redirect_id'] = $giftConfig['redirect_id'];
                $t['base_activity_id'] = $giftConfig['base_activity_id'];
                $t['start_time'] =(int) $giftConfig['start_time'];
                $t['end_time'] =(int) $giftConfig['end_time'];
                $t['redirect_id'] =(int) $giftConfig['redirect_id'];

                $result[] = $t;
            }
        }
        return $result;

    }
    protected function showAchieveNum($uid,$activity_id,$config)
    {
        $config['activity_id'] = $activity_id;
        $config['uid'] = $uid;
        $config['from_num'] = 0;

        $from_gift_id = $config['from_gift_id'];
        $num = $this->getFromNum($config,1);
        $config['from_num'] = $num;

        if(ConsumptionGift::instance()->isMonthTask($activity_id))
        {//月活动
//            if($config['action_id'] ==ATCode::ACTION_PURCHASE )
//            {
//                if(ConsumptionGift::instance()->isRechargeActivity($activity_id))
//                {
//                    return $num/10;
//                }
//                //#more TODO
//            }else{
//                    return $num;
//            }
            return $this->transTotalNum($activity_id,$config);
        }else{//每日活动
            return UserConsumptionLog::instance()->getUserFrequency($uid,$activity_id,date('Ymd'));
        }


    }
    protected function transTotalNum($activity_id,$config)
    {
        $from_gift_id = $config['from_gift_id'];
        $num = $config['from_num'];

        if(ConsumptionGift::instance()->isMonthTask($activity_id))
        {
            switch($from_gift_id)
            {
                case ATCode::GIFT_DIAMOND:
                    if(ConsumptionGift::instance()->isRechargeActivity($activity_id))
                    {//充值活动，换算成人民币
                        return (int)ATCode::transToMoney($num);
                    }else
                        return $num;
                    break;
                case ATCode::GIFT_JBCARD:
                    return (int)$num;break;
                case ATCode::GIFT_ROOMCARD:
                    return round($num,1);break;
                default:
                    break;
            }
            return $num;
        }else{//每日活动
            return $config['frequency'];
        }

    }

    /**
     * @desc 领取活动任务
     * @param $uid
     * @param $id  user_games id
     */
    public function receiveGift($uid,$activity_id,$add_time='')
    {
        if(!$uid || !$activity_id)
        {
            Logger::write('参数错误',__METHOD__);
            return array('code'=>Code::CODEERRPARAM,'msg'=>'参数错误','data'=>func_get_args());
        }

        $gift = UserConsumptionLog::instance()->getEveryDayOne($uid,$activity_id,$add_time);
        if(!$gift)
        {
            Logger::write('未找到用户消费日志记录',__METHOD__);
            return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'未找到用户消费日志记录','data'=>[]);
        }
        $gift = json_decode($gift['gift_list'],true);

        $c = UserConsumptionLog::instance()->hasNoReceived($uid,$activity_id,$add_time);

        if(!$c)
        {
            Logger::write('已领取奖励,不能多次领取',__METHOD__);
            return array('code'=>Code::CODEREQUESTTOOMANY,'msg'=>'已领取奖励,不能多次领取','data'=>[]);
        }
        foreach($gift as $gift_id=>$num)
        {
            $ret =  GiftService::instance()->insertGift($uid,$gift_id,$num,$desc='消费类活动');

            if($ret['code'] !=Code::SUCCESS)
            {
                Logger::write($ret['msg'],__METHOD__);
                return array('code'=>$ret['code'],'msg'=>$ret['msg'],'data'=>[]);
            }
        }

        $r = UserConsumptionLog::instance()->receiveEveryDayGift($uid,$activity_id,$add_time);
        if(!$r)
        {
            Logger::write('添加任务进度失败2',__METHOD__);
            return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'添加任务进度失败2','data'=>[]);
        }
        return   array('code'=>Code::SUCCESS,'msg'=>'领取活动任务奖励成功','data'=>[]);
    }
    public function hasActivity($activity_id)
    {
        return ConsumptionGift::instance()->hasActivity($activity_id);
    }

    /**
     * @desc 获取当月消耗或购买的数量
     * @param $params
     */
    public function getFromNum($params,$is_get=0)
    {

        switch($params['action_id'])
        {
            case ATCode::ACTION_PURCHASE:
                if($params['from_gift_id'] == ATCode::GIFT_DIAMOND)
                {
                    //params:uid,num
                    return UserPurchaseDiamandLog::instance()->getSetNum($params,date('Ym'),$is_get);
                }

                    return 0;
                break;
            case ATCode::ACTION_USE:
                if($params['from_gift_id'] == ATCode::GIFT_ROOMCARD)
                    return UserUseRoomcardLog::instance()->getSetNum($params,date('Ym'),$is_get);
                elseif($params['from_gift_id'] == ATCode::GIFT_JBCARD)
                    return UserUseJbcardLog::instance()->getSetNum($params,date('Ym'),$is_get);
                elseif(in_array($params['from_gift_id'] ,[ ATCode::GIFT_REMINDCARD_I,ATCode::GIFT_REMINDCARD_II,ATCode::GIFT_REMINDCARD_III]))
                    return UserUseRemindcardLog::instance()->getSetNum($params,date('Ym'),$is_get);
                else
                    return 0;
                break;
            case ATCode::ACTION_EXCHANGE:

                    return UserExchangeLog::instance()->getSetNum($params,date('Ym'),$is_get);
            default:
                return 0;
        }
        return 0;
    }

    public function transGiftNum($gift_id,$num)
    {
        switch($gift_id)
        {
            case ATCode::GIFT_DIAMOND:

            case ATCode::GIFT_JBCARD:
                return (int)$num;break;
            case ATCode::GIFT_ROOMCARD:
                return intval($num*10);break;
            default:
                break;
        }
        return $num;
    }
} 