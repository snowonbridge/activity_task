<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-8
 * Time: 下午8:05
 */

namespace Workerman\Service;


use Workerman\Lib\Code;
use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Model\FunctionConfig;
use Workerman\Model\FunctionGift;
use Workerman\Model\UserEverydayActivityCountsLog;
use Workerman\Model\UserEverydayActivityLog;
use Workerman\Model\UserFunctionLog;
use Workerman\Model\GiftContentSetting;

class FunctionService extends Model{


    /**
     * @param array $params
     * @param int $params:uid
     * @param int $params:activity_id
     * @return array
     */
    public function addLog($params=array())
    {
        $uid = $params['uid'];
        $activity_id = $params['activity_id'];
        $count = UserEverydayActivityCountsLog::instance()->getOne($uid,$activity_id);
        $params['counts'] = $count['achieve_counts'];
        $func_gift = FunctionGift::instance()->getOne($activity_id);

        if(false !== strpos($func_gift['function_list'],','))
        {
            $function_list = explode(',',trim($func_gift['function_list'],','));
        }else{
            $function_list = [$func_gift['function_list']];
        }

        foreach($function_list as $k=>$v)
        {
            if(false !== strpos($v,'&'))
            {
                $items = explode('&',$v);
                foreach($items as $func_config_id)
                {
                    //验证条件配置
                    if(!FunctionConfig::instance()->validConfig($func_config_id,$params))
                        continue;
                    $idarr[] = $func_config_id;
                }

            }else{
                //验证条件配置
                if(!FunctionConfig::instance()->validConfig($v,$params))
                    continue;

                $idarr[] = $v;
            }
        }

        if(!isset($idarr))
        {
            Logger::write('不存在满足条件的消费类活动',__METHOD__);
            return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'不存在满足条件的消费类活动','data'=>[]);
        }
        $log = UserFunctionLog::instance()->getEveryDayOne($uid,$activity_id);
        if($log)
        {//#如果已存在记录  achieve_list{config_id:1}
            if(UserFunctionLog::instance()->hasNoReceived($uid,$activity_id))
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
                        $achieve_list[$id] = 1;

                    }

                }
                if(UserFunctionLog::RECEIVE_NO === $log['is_receive'])
                {
                    Logger::write('已经是可领取状态',__METHOD__);
                    return array('code'=>Code::CODEREQUESTTOOMANY,'msg'=>'已经是可领取状态','data'=>$log);
                }
                //验证任务是否已完成
                $isAchieved = $this->validateTaskAchieved($achieve_list,$log['challege_list']);
                if($isAchieved)
                {
                    $data['is_receive'] = UserFunctionLog::RECEIVE_NO;//可领取状态
                }
                $data['achieve_list'] = json_encode($achieve_list);
                $ret = UserFunctionLog::instance()->updateStatus($log['id'],$data);

                $log['achieve_list'] = $data['achieve_list'];
                if(!$ret)
                {
                    Logger::write('请求次数过多,不能再次添加',__METHOD__);
                    return array('code'=>Code::CODEREQUESTTOOMANY,'msg'=>'请求次数过多,不能再次添加','data'=>$log);
                }
                return array('code'=>Code::SUCCESS,'msg'=>'添加任务进度成功','data'=>$log);
            }
            if($log['current_frequency'] <= $log['frequency']-1)
            {
                $new_frequency = $log['current_frequency'] +1;
            }else{
                Logger::write('频率超出限制',__METHOD__);
                return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'频率超出限制','data'=>$log);
            }

        }else{
            $new_frequency = 1;

        }
        $achieve_list = [];
        foreach($idarr as $id)
        {
            $achieve_list[$id] =1;

        }
        $data= array();
        $data['uid']=$uid;
        $data['activity_id']=$activity_id;
        $data['challege_list'] = $func_gift['function_list'];
        $data['achieve_list'] = json_encode($achieve_list);
        $data['gift_list'] = $func_gift['gift_list'];

        $data['is_receive'] =$this->validateTaskAchieved($achieve_list,$func_gift['function_list'])?UserFunctionLog::RECEIVE_NO:UserFunctionLog::RECEIVE_FORBID;

        $data['frequency'] = $func_gift['frequency'];
        $data['current_frequency'] = $new_frequency;
        $data['img_icon'] = $func_gift['img_icon'];

        $ret=UserFunctionLog::instance()->addEveryTask($data);
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
                if(isset($achieve_list[$v]) && $achieve_list[$v])
                    return true;
            }else{
                if($achieve_list[$v])
                    return true;
            }
        }

        return false;
    }
    /**
     * @desc 显示所有每日任务活动的完成状态
     * @param $uid
     */
    public function showActivityStatus($uid)
    {
        $gifts = FunctionGift::instance()->getAll();
        if(!$gifts)
            return array();
        $activity_ids = array_column($gifts,'activity_id');
        $logs = UserFunctionLog::instance()->getEveryDayAll($uid,$activity_ids);
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
            $result[$k]['is_receive'] = isset($logs[$item['activity_id']]['is_receive'])?$logs[$item['activity_id']]['is_receive']:UserFunctionLog::RECEIVE_FORBID;
            $result[$k]['img_icon'] = $item['img_icon'];
            $result[$k]['gift_list'] = $this->showActivityGifts($item['activity_id']);
        }
        return $result;
    }

    /**
     * @desc 显示活动奖品
     * @param $activity_id
     */
    public function showActivityGifts($activity_id)
    {
        $log = FunctionGift::instance()->getOne($activity_id);
        $result=[];
        if($log && is_array(json_decode($log['gift_list'],true)))
        {
            $giftList = json_decode($log['gift_list'],true);
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
     * $achieve_list   [config_id=>1]
     * @param $uid
     * @param $activity_id
     */
    public function showJoinStatus($uid,$activity_id)
    {
        $log = UserFunctionLog::instance()->getEveryDayOne($uid,$activity_id);
        if($log)
        {
            $achieve_list = json_decode($log['achieve_list'],true);
        }else{
            $achieve_list = [];
        }
        $giftConfig = FunctionGift::instance()->getOne($activity_id);

        if(false !== strpos($giftConfig['function_list'],','))
        {
            $challege_list = explode(',',trim($giftConfig['function_list'],','));
        }else{
            $challege_list = [$giftConfig['function_list']];
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
                    $t['is_receive']= isset($log['is_receive'])?$log['is_receive']:UserFunctionLog::RECEIVE_FORBID;;
                    $t['achieve_status'] = isset($achieve_list[$item])?$achieve_list[$item]:0;
                    $t['challege_config_id'] = $item;
                    $numInfo = UserFunctionLog::instance()->getUserFrequency($uid,$activity_id);
                    $t['total_num'] = FunctionConfig::instance()->getCounts($item);;
                    $t['achieve_num'] = UserEverydayActivityCountsLog::instance()->getAchieveCounts($uid,$activity_id);
                    $t['desc'] = $giftConfig['desc'];
                    $t['redirect_id'] =(int) $giftConfig['redirect_id'];
                    $ct[] =  $t;
                }
                $result[] = $ct;
            }else{
                $t['is_receive']= isset($log['is_receive'])?$log['is_receive']:UserFunctionLog::RECEIVE_FORBID;;
                $t['achieve_status'] = isset($achieve_list[$v])?$achieve_list[$v]:0;
                $t['challege_config_id'] = $v;
                $t['total_num'] = FunctionConfig::instance()->getCounts($v);
                $t['achieve_num'] = UserEverydayActivityCountsLog::instance()->getAchieveCounts($uid,$activity_id);
                if($t['is_receive'] == UserFunctionLog::RECEIVE_FORBID && $t['achieve_num']>=$t['total_num'])
                {
                    $data= array();
                    $data['uid']=$uid;
                    $data['activity_id']=$activity_id;
                    //暂时默认只有一个条件，没有组合情况
                    $data['challege_list'] = $giftConfig['function_list'];
                    $data['achieve_list'] = json_encode([$v=>1]);
                    $data['gift_list'] = $giftConfig['gift_list'];

                    $data['is_receive'] = UserFunctionLog::RECEIVE_NO;
                    $data['frequency'] = $giftConfig['frequency'];
                    $data['current_frequency'] = 1;
                    $data['img_icon'] = $giftConfig['img_icon'];
                    //月任务咱时没有，注意后面还有个时间参数
                    if(!UserFunctionLog::instance()->getEveryDayOne($uid,$activity_id) )
                    {
                        $ret=UserFunctionLog::instance()->addEveryTask($data);
                        if($ret)
                        {
                            $t['is_receive'] = UserFunctionLog::RECEIVE_NO;
                        }
                    }

                }
                $t['desc'] = $giftConfig['desc'];
                $t['redirect_id'] =(int) $giftConfig['redirect_id'];
                $result[] = $t;
            }
        }
        return $result;

    }

    /**
     * @desc 领取活动任务
     * @param $uid
     * @param $id  user_games id
     */
    public function receiveGift($uid,$activity_id)
    {
        if(!$uid || !$activity_id)
        {
            Logger::write('参数错误',__METHOD__);
            return array('code'=>Code::CODEERRPARAM,'msg'=>'参数错误','data'=>func_get_args());
        }
        $r = UserFunctionLog::instance()->receiveEveryDayGift($uid,$activity_id);
        if(0 === $r)
        {
            Logger::write('已领取奖励,不能多次领取',__METHOD__);
            return array('code'=>Code::CODEREQUESTTOOMANY,'msg'=>'已领取奖励,不能多次领取','data'=>[]);
        }
        if(false === $r)
        {
            Logger::write('领取操作异常',__METHOD__);
            return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'领取操作异常','data'=>[]);
        }
        $gift = FunctionGift::instance()->getOne($activity_id);
        $gift = json_decode($gift['gift_list'],true);
        foreach($gift as $gift_id=>$num)
        {
            $ret =  GiftService::instance()->insertGift($uid,$gift_id,$num,$desc='功能类活动');
            if($ret['code'] !=Code::SUCCESS)
            {
                Logger::write($ret['msg'],__METHOD__);
                return array('code'=>$ret['code'],'msg'=>$ret['msg'],'data'=>[]);
            }
        }

        return   array('code'=>Code::SUCCESS,'msg'=>'领取活动任务奖励成功','data'=>[]);
    }
    public function hasActivity($activity_id)
    {
        return FunctionGift::instance()->hasActivity($activity_id);
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