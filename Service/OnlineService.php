<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-9
 * Time: 上午9:40
 */

namespace Workerman\Service;


use Workerman\Lib\Code;
use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Model\OnlineConfig;
use Workerman\Model\OnlineGift;
use Workerman\Model\SocialConfig;
use Workerman\Model\SocialGift;
use Workerman\Model\UserOnlineLog;
use Workerman\Model\UserSocialLog;
use Workerman\Model\GiftContentSetting;

class SocialService extends Model{

    /**
     * @param array $params
     * @param int $params:uid
     * @param int $params:activity_id
     * @param int $params:online_time
     * @param int $params:user_level

     * @return array
     */
    public function addLog($params=array())
    {
        $uid = $params['uid'];
        $activity_id = $params['activity_id'];
        $online_time = $params['online_time'];
        $user_level = $params['user_level'];
        $social_gift = OnlineGift::instance()->getOne($activity_id);
        if(false !== strpos($social_gift['challege_list'],','))
        {
            $action_list = explode(',',trim($social_gift['challege_list'],','));
        }else{
            $action_list = [$social_gift['challege_list']];
        }
        foreach($action_list as $k=>$v)
        {
            if(false !== strpos($v,'&'))
            {
                $items = explode('&',$v);
                foreach($items as $config_id)
                {
                    //验证条件配置
                    if(!OnlineConfig::instance()->validConfig($config_id,$params))
                        continue;
                    $idarr[] = $config_id;
                }

            }else{
                //验证条件配置
                if(!OnlineConfig::instance()->validConfig($v,$params))
                    continue;

                $idarr[] = $v;
            }
        }
        if(!isset($idarr))
        {
            Logger::write('不存在满足条件的社交类活动',__METHOD__);
            return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'不存在满足条件的社交类活动','data'=>[]);
        }
        $log = UserOnlineLog::instance()->getEveryDayOne($uid,$activity_id);
        if($log)
        {//#如果已存在记录  achieve_list{config_id:1}
            if(UserOnlineLog::instance()->hasNoReceived($uid,$activity_id))
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
                //验证任务是否已完成
                $isAchieved = $this->validateTaskAchieved($achieve_list,$log['challege_list']);
                if($isAchieved)
                {
                    $data['is_receive'] = UserSocialLog::RECEIVE_NO;//可领取状态
                }
                $data['achieve_list'] = json_encode($achieve_list);
                $ret = UserOnlineLog::instance()->updateStatus($log['id'],$data);
                $log['achieve_list'] = $data['achieve_list'];
                if(!$ret)
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
            $achieve_list[$id] =1;

        }
        $data= array();
        $data['uid']=$uid;
        $data['activity_id']=$activity_id;
        $data['challege_list'] = $action_list['action_list'];
        $data['achieve_list'] = json_encode($achieve_list);
        $data['gift_list'] = $action_list['gift_list'];
        $data['is_receive'] = $this->validateTaskAchieved($achieve_list,$action_list['challege_list'])?UserOnlineLog::RECEIVE_NO:UserOnlineLog::RECEIVE_FORBID;
        $data['frequency'] = $action_list['frequency'];
        $data['current_frequency'] = $new_frequency;
        $data['img_icon'] = $action_list['img_icon'];

        $ret=UserOnlineLog::instance()->addEveryTask($data);
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
            return array();
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
     */
    public function showActivityStatus($uid)
    {
        $gifts = OnlineGift::instance()->getAll();
        if(!$gifts)
            return array();
        $activity_ids = array_column($gifts,'activity_id');
        $logs = UserOnlineLog::instance()->getEveryDayAll($uid,$activity_ids);
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
            $result[$k]['is_receive'] = isset($logs[$item['activity_id']]['is_receive'])?$logs[$item['activity_id']]['is_receive']:UserOnlineLog::RECEIVE_FORBID;
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
        $log = OnlineGift::instance()->getOne($activity_id);
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
                    $t['num']     = $v;
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
    public function showJoinStatus($uid,$activity_id)
    {
        $log = UserOnlineLog::instance()->getEveryDayOne($uid,$activity_id);
        if($log)
        {
            $achieve_list = json_decode($log['achieve_list'],true);
        }else{
            $achieve_list =[];
        }
        $giftConfig = OnlineGift::instance()->getOne($activity_id);
        if(false !== strpos($giftConfig['challege_list'],','))
        {
            $challege_list = explode(',',trim($giftConfig['challege_list'],','));
        }else{
            $challege_list = [$giftConfig['challege_list']];
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
                    $t['is_receive']= isset($log['is_receive'])?$log['is_receive']:UserOnlineLog::RECEIVE_FORBID;;
                    $t['achieve_status'] = isset($achieve_list[$item])?$achieve_list[$item]:0;
                    $t['challege_config_id'] = $item;

                    $t['total_num'] = OnlineConfig::instance()->getCounts($item);
                    $t['achieve_num'] = UserOnlineLog::instance()->getUserFrequency($uid,$activity_id);
                    $t['desc'] = $giftConfig['desc'];
                    $t['redirect_id'] = (int)$giftConfig['redirect_id'];
                    $ct[] = $t;
                }
                $result[] = $ct;
            }else{
                $t['is_receive']= isset($log['is_receive'])?$log['is_receive']:UserOnlineLog::RECEIVE_FORBID;;
                $t['achieve_status'] = isset($achieve_list[$v])?$achieve_list[$v]:0;
                $t['challege_config_id'] = $v;
                $t['total_num'] = OnlineConfig::instance()->getCounts($v);
                $t['achieve_num'] = UserOnlineLog::instance()->getUserFrequency($uid,$activity_id);;
                $t['desc'] = $giftConfig['desc'];
                $t['redirect_id'] = (int)$giftConfig['redirect_id'];
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
        $r = UserOnlineLog::instance()->receiveEveryDayGift($uid,$activity_id);
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
        $gift = OnlineGift::instance()->getOne($activity_id);
        $gift = json_decode($gift['gift_list'],true);
        foreach($gift as $gift_id=>$num)
        {
            $ret =  GiftService::instance()->insertGift($uid,$gift_id,$num,$desc='时长类活动');
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
        return OnlineGift::instance()->hasActivity($activity_id);
    }
} 