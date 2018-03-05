<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-7
 * Time: 下午2:40
 */

namespace Workerman\Service;


use Workerman\Lib\Code;
use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Model\CrossChallegeConfig;
use Workerman\Model\CrossChallegeGift;
use Workerman\Model\GameActivityMap;
use Workerman\Model\UserCrossChallegeLog;
use Workerman\Model\GiftContentSetting;

class CrossChallegeService extends Model{

    /**
     * @desc 添加每日任务的日志
     * @param array $params
     * @param params:activity_id 活动编号
     * @param params:game_no  游戏编号  游戏名称-玩法-经典场
     * @param params:win_result 是否赢
     * @param params:friends_num 好友数量
     * @param params:own_open_room 自己开房@1y,0n,2不验证
     * @param params:time 时间戳
     */
    public function addLog($params=array())
    {
        $uid = $params['uid'];
        $activity_id = $params['activity_id'];
        $game_no = $params['game_no'];
        $win_result = $params['win_result'];
        $friends_num = $params['friends_num'];
        $own_open_room = $params['own_open_room'];
        $add_time = $params['add_time']= isset($params['time'])?$params['time']:time();

        if(CrossChallegeGift::instance()->isMonthTask($activity_id))
        {//月活动
            $add_time = date('Ym',$add_time);
            //暂时是验证有效时间
            if(!CrossChallegeGift::instance()->validConfig($activity_id,$params))
            {
                Logger::write('不满足该月活动条件',__METHOD__);
                return array('code'=>Code::NOT_ALLOWED_OPERATION,'msg'=>'不满足该月活动条件','data'=>[]);
            }
        }else{//每日任务
            $add_time = date('Ymd',$add_time);
        }
        $cross_challege = CrossChallegeGift::instance()->getOne($activity_id);
        if(false !== strpos($cross_challege['challege_list'],','))
        {
            $challege_list = explode(',',trim($cross_challege['challege_list'],','));
        }else{
            $challege_list = [$cross_challege['challege_list']];
        }

        foreach($challege_list as $k=>$v)
        {
            if(false !== strpos($v,'&'))
            {
                $items = explode('&',$v);
                foreach($items as $cross_config_id)
                {
                    //验证关卡条件配置
                    if(!CrossChallegeConfig::instance()->validConfig($cross_config_id,$params))
                        continue;
                    $cross_config_idarr[] = $cross_config_id;
                }

            }else{

                //验证关卡条件配置
                if(!CrossChallegeConfig::instance()->validConfig($v,$params))
                    continue;
                $cross_config_idarr[] = $v;
            }
        }

        if(!isset($cross_config_idarr))
        {
            return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'该活动不存在该游戏关卡,无相关关卡条件','data'=>[]);
        }

        $log = UserCrossChallegeLog::instance()->getEveryDayOne($uid,$activity_id,$add_time);
        if($log)
        {//#如果已存在记录
            if(UserCrossChallegeLog::instance()->hasNoReceived($uid,$activity_id,$add_time))
            {
                if(!empty($log['achieve_list']))
                {
                    $achieve_list = json_decode($log['achieve_list'],true);
                }else
                    $achieve_list=array();
                foreach($cross_config_idarr as $id)
                {

                    if(isset($achieve_list[$id]))
                    {
                        $achieve_list[$id][0] ++;
                        if($win_result)
                            $achieve_list[$id][1]++;
                    }else{
                        $achieve_list[$id][0] =1;
                        $achieve_list[$id][1] =$win_result?1:0;
                    }

                }
                if(UserCrossChallegeLog::RECEIVE_NO === $log['is_receive'])
                {
                    Logger::write('已经是可领取状态',__METHOD__);
                    return array('code'=>Code::CODEREQUESTTOOMANY,'msg'=>'已经是可领取状态','data'=>$log);
                }
                //验证任务是否已完成
                $isAchieved = $this->validateTaskAchieved($achieve_list,$log['challege_list']);
                if($isAchieved)
                {
                    $data['is_receive'] = UserCrossChallegeLog::RECEIVE_NO;//可领取状态
                }
                $data['achieve_list'] = json_encode($achieve_list);
                $ret = UserCrossChallegeLog::instance()->updateStatus($log['id'],$data);

                $log['achieve_list'] = $data['achieve_list'];
                if(!$ret)
                {
                    Logger::write('添加任务进度失败',__METHOD__);
                    return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'添加任务进度失败','data'=>$log);
                }
                return array('code'=>Code::SUCCESS,'msg'=>'添加任务进度成功1','data'=>$log);
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
        foreach($cross_config_idarr as $id)
        {
            $achieve_list[$id][0] =1;
            if($win_result)
                $achieve_list[$id][1]=1;
            else
                $achieve_list[$id][1]=0;
        }
        $data= array();
        $data['uid']=$uid;
        $data['activity_id']=$activity_id;
        $data['challege_list'] = $cross_challege['challege_list'];
        $data['achieve_list'] = json_encode($achieve_list);
        $data['gift_list'] = $cross_challege['gift_list'];
        $data['is_receive'] = $this->validateTaskAchieved($achieve_list,$cross_challege['challege_list'])?UserCrossChallegeLog::RECEIVE_NO:UserCrossChallegeLog::RECEIVE_FORBID;
        $data['frequency'] = $cross_challege['frequency'];
        $data['current_frequency'] = $new_frequency;
        $data['img_icon'] = $cross_challege['img_icon'];
        $data['add_time'] = $add_time;
        $ret=UserCrossChallegeLog::instance()->addEveryTask($data);
        if(!$ret)
        {
            Logger::write('添加任务进度失败2',__METHOD__);
            return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'添加任务进度失败2','data'=>$log);
        }

        return array('code'=>Code::SUCCESS,'msg'=>'添加任务进度成功2','data'=>$log);
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
        $r = UserCrossChallegeLog::instance()->receiveEveryDayGift($uid,$activity_id,$add_time);
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
        $gift = CrossChallegeGift::instance()->getOne($activity_id);
        $gift = json_decode($gift['gift_list'],true);
        foreach($gift as $gift_id=>$num)
        {
            $ret =  GiftService::instance()->insertGift($uid,$gift_id,$num,$desc='牌局关卡活动');
            if($ret['code'] !=Code::SUCCESS)
            {
                Logger::write($ret['msg'],__METHOD__);
                return array('code'=>$ret['code'],'msg'=>$ret['msg'],'data'=>[]);
            }
        }

        return   array('code'=>Code::SUCCESS,'msg'=>'领取活动任务奖励成功','data'=>[]);
    }

    /**
     * @desc 显示活动奖品
     * @param $activity_id
     */
    public function showActivityGifts($activity_id)
    {
        $log = CrossChallegeGift::instance()->getOne($activity_id);
        $result=[];
        if($log && is_array(json_decode($log['gift_list'],true)))
        {
            $giftList = json_decode($log['gift_list'],true);
            foreach($giftList as $k=>$v)
            {
                if(is_numeric($v))
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
        $log = UserCrossChallegeLog::instance()->getEveryDayOne($uid,$activity_id,$add_time);

        if($log)
        {
            $achieve_list = json_decode($log['achieve_list'],true);
        }else{
            $achieve_list = [];
        }

        $giftConfig = CrossChallegeGift::instance()->getOne($activity_id);
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
                    $t['is_receive'] = isset($log['is_receive'])?$log['is_receive']:UserCrossChallegeLog::RECEIVE_FORBID;;
                    $config = CrossChallegeConfig::instance()->getOne($item);
                    $t['total_num'] = intval($config['game_num']);
                    if($config['win_result'] ==0)
                    {
                        $result[$v]['achieve_num'] = isset($achieve_list[$item][0])?$achieve_list[$item][0]:0;
                    }else{
                        $result[$v]['achieve_num'] =isset($achieve_list[$item][1])?$achieve_list[$item][1]:0;
                    }
                    $t['challege_config_id'] = $item;
                    $t['desc'] = $giftConfig['desc'];
                    $t['base_activity_id'] = $giftConfig['base_activity_id'];
                    $t['redirect_id'] = (int)$giftConfig['redirect_id'];
                    $t['start_time'] =(int) $giftConfig['start_time'];
                    $t['end_time'] =(int) $giftConfig['end_time'];
                    $ct[] = $t;

                }
                $result[$v]=$ct;
            }else{
                $result[$v]['is_receive']= isset($log['is_receive'])?$log['is_receive']:UserCrossChallegeLog::RECEIVE_FORBID;;
                $config = CrossChallegeConfig::instance()->getOne($v);
                $result[$v]['total_num'] = intval($config['game_num']);
                if($config['win_result'] ==0)
                {
                    $result[$v]['achieve_num'] = isset($achieve_list[$v][0])?$achieve_list[$v][0]:0;
                }else{
                    $result[$v]['achieve_num'] =isset($achieve_list[$v][1])?$achieve_list[$v][1]:0;
                }
                if($result[$v]['is_receive'] == UserCrossChallegeLog::RECEIVE_FORBID && $result[$v]['achieve_num']>=$result[$v]['total_num'])
                {
                    $data= array();
                    $data['uid']=$uid;
                    $data['activity_id']=$activity_id;
                    //暂时默认只有一个条件，没有组合情况
                    $data['challege_list'] = $giftConfig['challege_list'];
                    $data['achieve_list'] = json_encode([$v=>1]);
                    $data['gift_list'] = $giftConfig['gift_list'];

                    $data['is_receive'] = UserCrossChallegeLog::RECEIVE_NO;
                    $data['frequency'] = $giftConfig['frequency'];
                    $data['current_frequency'] = 1;
                    $data['img_icon'] = $giftConfig['img_icon'];
                    $data['add_time'] = $add_time;
                    if(!UserCrossChallegeLog::instance()->getEveryDayOne($uid,$activity_id,$add_time) )
                    {
                        $ret=UserCrossChallegeLog::instance()->addEveryTask($data);
                        if($ret)
                        {
                            Logger::write("补偿成功");
                            $result[$v]['is_receive'] = UserCrossChallegeLog::RECEIVE_NO;
                        }else{
                            Logger::write("补偿失败");
                        }
                    }


                }
                $result[$v]['challege_config_id'] = $v;
                $result[$v]['base_activity_id'] = $giftConfig['base_activity_id'];
                $result[$v]['redirect_id'] = (int)$giftConfig['redirect_id'];
                $result[$v]['start_time'] = (int)$giftConfig['start_time'];
                $result[$v]['end_time'] = (int)$giftConfig['end_time'];
                $result[$v]['desc'] = $giftConfig['desc'];
            }
        }
        return array_values($result);

    }

    /**
     * @desc 显示所有每日任务活动的完成状态
     * @param $uid
     */
    public function showActivityStatus($uid)
    {
        $gifts = CrossChallegeGift::instance()->getAll();
        if(!$gifts)
            return array();
        $activity_ids = array_column($gifts,'activity_id');
        $logs = UserCrossChallegeLog::instance()->getEveryDayAll($uid,$activity_ids);
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
            $result[$k]['is_receive'] = isset($logs[$item['activity_id']]['is_receive'])?$logs[$item['activity_id']]['is_receive']:UserCrossChallegeLog::RECEIVE_FORBID;
            $result[$k]['img_icon'] = $item['img_icon'];
            $result[$k]['gift_list'] = $this->showActivityGifts($item['activity_id']);
        }
        return $result;
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
                $flag = true;
                foreach($items as $item)
                {
                    $config = CrossChallegeConfig::instance()->getOne($item);
                    if($config['win_result'] ==0)
                    {
                        if($config['game_num'] >$achieve_list[$v][0])
                        {
                            $flag = false;
                        }
                    }else{
                        if($config['game_num'] > $achieve_list[$v][1])
                        {
                            $flag = false;
                        }
                    }
//                   $t['achieve_status'] = CrossChallegeConfig::instance()->validGameNums(isset($achieve_list[$item][1])?$achieve_list[$item][1]:0,$items);
//                   $t['achieve_num'] = isset($achieve_list[$item][1])?$achieve_list[$item][1]:0;
//                    $config = CrossChallegeConfig::instance()->getOne($item);
//                   $t['total_num'] = $config['game_num'];
                }
                if($flag)
                    return true;

            }else{

                $config = CrossChallegeConfig::instance()->getOne($v);
                if($config['win_result'] ==0)
                {
                    if($config['game_num'] <=$achieve_list[$v][0])
                    {
                        return true;
                    }
                }else{
                    if($config['game_num'] <=$achieve_list[$v][1])
                    {
                        return true;
                    }
                }

            }
        }


        return false;
    }

    public function hasActivity($activity_id)
    {
        return CrossChallegeGift::instance()->hasActivity($activity_id);
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