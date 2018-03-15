<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-9
 * Time: 下午6:00
 */

namespace Workerman\Controller;


use Workerman\Events\Event;
use Workerman\Filter\MonthTaskInFilter;
use Workerman\Filter\MonthTaskOutFilter;
use Workerman\Lib\Code;
use Workerman\Lib\Controller;
use Workerman\Lib\Curl;
use Workerman\Lib\Logger;
use Workerman\Lib\Okey;
use Workerman\Lib\Redis;
use Workerman\Lib\Timer;
use Workerman\Model\ActivityCategory;
use Workerman\Model\GiftLog;
use Workerman\Model\ToolOperateLog;
use Workerman\Model\TurnUserLog;
use Workerman\Model\UserEverydayActivityLog;
use Workerman\Service\ATCode;
use Workerman\Service\ConsumptionService;
use Workerman\Service\CrossChallegeService;
use Workerman\Service\DataLogService;
use Workerman\Service\FunctionService;
use Workerman\Service\GiftService;
use Workerman\Service\SocialService;

class TaskDispatcher extends Controller{

    public  $scenesConfig=array();
    public  $typeConfig=array();


    public function __construct()
    {

        $this->scenesConfig = require(CFG_PATH . 'action.php');

    }



    /**
     * 从redis数据库取任务,一个数据库对应一个任务
     */
    public function dealTask()
    {
        //$this->pushList();//test
        $start = microtime(true);
        $r = Redis::getIns('user')->blPop(Okey::rTaskList());

        if(!$r)
        {
//            Logger::write('无队列数据','dispatcher',"INFO");
            return false;
        }
        //场景id
        if(!isset($r['scene_id']))
        {
            Logger::write('数据格式错误','dispatcher',"ERROR");
            return false;
        }


        $data=array();
        //sid最大为127
        $data['uid'] = $r['uid'] ;
        if($data['uid'] < 10000)
        {
            Logger::write('机器人的定时任务不予处理','dispatcher',"INFO");
            return true;
        }
        $data['scene_id'] = $r['scene_id'];

        $data['params_content'] = json_encode($r);
        $data['counts'] = 1;
        $data['achieve_counts'] = 0;
        $data['add_time'] = date('Ymd');


        $last_id = UserEverydayActivityLog::instance()->add($data);
        $ids = $this->getActivityIds($r['scene_id']);

        $activityDayList = ActivityCategory::instance()->getEveryDayActivityList();
        $activityMonthList = ActivityCategory::instance()->getMonthActivityList();
        if($activityDayList)
        {
            $activityDayListIds   = array_column($activityDayList,'activity_id');
        }else{
            $activityDayListIds = array();
        }
        if($activityMonthList)
        {
            $activityMonthListIds   = array_column($activityMonthList,'activity_id');
        }else{
            $activityMonthListIds = array();
        }

        foreach($ids as $k=>$activity_id)
        {
            if(in_array($activity_id,$activityDayListIds))
                EveryDayTask::instance()->addLog($activity_id,$r);
            if(in_array($activity_id,$activityMonthListIds))
            {
                $r = MonthTaskInFilter::instance()->filter($r);
                MonthTask::instance()->addLog($activity_id,$r);
            }
        }
        UserEverydayActivityLog::instance()->incrAchieveCountsById($last_id);
        $end = microtime(true);
        Logger::write("单个任务".posix_getpid()."运行时间（秒）:".($end-$start));
        return true;

    }
    public function getActivityIds($scene_id)
    {
        return isset($this->scenesConfig[$scene_id])?$this->scenesConfig[$scene_id]:0;
    }

    public function pushList()
    {

        $i=0;
        while($i++<2)
        {
            $params = <<<jsondata
{"id":1,"action_id":4,"from_gift_id":5,"target_gift_id":1,"user_level":0,"magic":2,"login_check":2,"from_num":10,"uid":10,"scene_id":2034,"time":1515756369}
jsondata;
            $params = json_decode($params,true);

            //consumption type
//            $params['uid'] = $i;
//            $params['from_num'] = 30;
//            $params['action_id'] = 3;
//            $params['from_gift_id'] = 2;
//            $params['target_gift_id'] = 0;
//            $params['login_check'] = 0;
//            $params['magic'] = 0;
//            $params['user_level'] = 0;
//            $params['scene_id'] = 2032;



            //cross-challege type
//            $data['uid'] = $i;
//            $data['scene_id'] = 2032;
//            $data['activity_id']=305;
//            $data['game_no']='1001-6';
//            $data['win_result']=1;
//            $data['friends_num']=0;
//            $data['user_level']=0;
//            $data['own_open_room']=1;

            //functions type
//            $data['uid'] = $i;
//            $data['scene_id'] = 1001;

//            $data['uid'] = $i; ok
//            $data['scene_id'] = 1002;
//            $data['uid'] = $i;
//            $data['scene_id'] = 1003;
            //social type
//            $data['uid'] = $i;
//            $data['scene_id'] = 3002;
//            $data['friend_relation'] = 0;

            $r = Redis::getIns('user')->rPush(Okey::rTaskList(),$params);

        }

    }


    /**
     * @desc 处理转盘抽奖 获得的奖励配发奖品队列
     */
    public function dealTurnGiftList()
    {
//        $this->pushList();//test
        $gift = Redis::getIns()->blPop(Okey::rTurnGiftsUserList());
        if($gift)
        {
            Logger::write(json_encode($gift),__METHOD__,"INFO");
            //实际配发奖励
            $ret =GiftService::instance()->insertGift((int)$gift['uid'],(int)$gift['gift_content_id'],(int)$gift['num'],$gift['desc'],$gift['clmode'],1,ATCode::SERVER_SCENE_ID_LOTTERY);
            return $ret;
        }
//        Logger::write(var_export($gift,true),__METHOD__,"INFO");
        return 0;
    }

    /**
     * @desc 转盘抽奖获取的奖励日志入数据库 的队列
     * @return bool|string
     */
    public function dealTurnUserLogList()
    {
//        $this->pushList();//test
        $data = Redis::getIns()->blPop(Okey::rTurnUserLogList());
        if($data)
        {

            //实际配发奖励
            $ret =TurnUserLog::instance()->addLog($data);
            return $ret;
        }
        return false;
    }

    /**
     * @desc 处理存在有效时长的道具
     * 暂时只需要处理两种记牌器，110,111
     */
    public function dealExpireTool()
    {
        $data = Redis::getIns()->lIndex(Okey::rToolExpireList(),-1);
        if(!$data)
        {
            Logger::write('lIndex 超时失败',__METHOD__,'ERROR');
            return false;
        }
        //必备参数验证
        if($diff = array_diff(['uid','ttid','expire_time'],array_keys($data)))
        {
            Logger::write("缺少参数".json_encode($diff)." redis数据为".json_encode($data),__METHOD__,'ERROR');
            return false;
        }
        $now = time();
        if($data['expire_time'] <=$now)
        {//获取时已到过期时间，记录到数据库
            Redis::getIns()->brPop(Okey::rToolExpireList());
            DataLogService::instance()->toolExpireLogLoop($data);
        }else{
            Redis::getIns()->bRpoplpush(Okey::rToolExpireList(),Okey::rToolExpireList());
        }

    }


    /**
     * @tag new
     * @desc 更新好友邀请同房游戏状态
     * @return bool|void
     */
    public function dealInviteStatus()
    {
//        $this->putInviteList();//test
        $data = Redis::getIns()->blPop(Okey::rGameInviteLogList());
        if(false === $data)
        {
            Logger::write('blPop 超时失败',__METHOD__,'ERROR');
            return false;
        }

        //必备参数验证
        if($diff = array_diff(['game_id','game_type_id','game_room_id','uid','friend_uid','invite_time'],array_keys($data)))
        {
            Logger::write("缺少参数".json_encode($diff)." redis数据为".json_encode($data),__METHOD__,'ERROR');
            return false;
        }
        $ret =DataLogService::instance()->updateToolUsingStatus($data);
        return false;
    }

    /**
     * @desc 处理道具的使用，购买日志
     * @return bool
     */
    public function dealToolLog()
    {
        $data = Redis::getIns()->blPop(Okey::rToolLogList ());
        if(false === $data)
        {
            Logger::write('blPop 超时  失败',__METHOD__,'ERROR');
            return false;
        }
        if(!$data['operate_type'])
        {
            Logger::error("operate_type参数必须存在".json_encode($data),__METHOD__,'ERROR');
        }

        if($data['operate_type'] == ToolOperateLog::OPERATE_TYPE_GET)
        {
            //必备参数验证
            if($diff = array_diff(['uid','ttid','before_num','after_num','operate_time'],array_keys($data)))
            {
                Logger::write("缺少参数".json_encode($diff)." redis数据为".json_encode($data),__METHOD__,'ERROR');
                return false;
            }
            DataLogService::instance()->toolPurchaseLogLoop($data);
        }elseif($data['operate_type'] == ToolOperateLog::OPERATE_TYPE_USE)
        {
            //必备参数验证
            if($diff = array_diff(['uid','ttid'],array_keys($data)))
            {
                Logger::write("缺少参数".json_encode($diff)." redis数据为".json_encode($data),__METHOD__,'ERROR');
                return false;
            }
            DataLogService::instance()->toolUseLogLoop($data);
        }
        return true;
    }

    /**

     * reason   礼物操作日志处理
     * operate_time
     */
    public function dealGiftLog()
    {
        $data = Redis::getIns()->blPop(Okey::rGiftLogList());
        if(false === $data)
        {
            Logger::write('blPop 失败',__METHOD__,'ERROR');
            return false;
        }
        if(!$data['operate_type'])
        {
            Logger::error("operate_type参数必须存在".json_encode($data),__METHOD__,'ERROR');
        }

        if($data['operate_type'] == GiftLog::OPERATE_TYPE_SELL)
        {
            //必备参数验证
            if($diff = array_diff(['uid','gift_auto_id','before_num','after_num','operate_time','reason'],array_keys($data)))
            {
                Logger::write("缺少参数".json_encode($diff)." redis数据为".json_encode($data),__METHOD__,'ERROR');
                return false;
            }
            DataLogService::instance()->giftSellLogLoop($data);
        }elseif($data['operate_type'] == GiftLog::OPERATE_TYPE_RECV)
        {
            //必备参数验证
            if($diff = array_diff(['uid','gift_auto_id','before_num','after_num','give_uid'],array_keys($data)))
            {
                Logger::write("缺少参数".json_encode($diff)." redis数据为".json_encode($data),__METHOD__,'ERROR');
                return false;
            }
            DataLogService::instance()->giftRecvLogLoop($data);
        }elseif($data['operate_type'] == GiftLog::OPERATE_TYPE_PRESENT)
        {
            //必备参数验证
            if($diff = array_diff(['uid','gift_id','m_type','before_num','after_num','give_uid','reason','operate_time'],array_keys($data)))
            {
                Logger::write("缺少参数".json_encode($diff)." redis数据为".json_encode($data),__METHOD__,'ERROR');
                return false;
            }
            DataLogService::instance()->giftPresentLogLoop($data);
        }
        return true;
    }

    /**
     * @desc 好友操作日志处理
     * @return bool
     */
    public function dealFriendLog()
    {
        $data = Redis::getIns()->blPop(Okey::rFriendLogList());
        if(false === $data)
        {
            Logger::write('blPop 超时失败',__METHOD__,'ERROR');
            return false;
        }
        //必备参数验证
        if($diff = array_diff(['apply_id','f_operate_id','t_operate_id'],array_keys($data)))
        {
            Logger::write("缺少参数".json_encode($diff)." redis数据为".json_encode($data),__METHOD__,'ERROR');
            return false;
        }
        DataLogService::instance()->friendLogLoop($data);

        return true;
    }




    public function putInviteList()
    {
        $params = <<<jsondata
{"uid":10444,"friend_uid":12356,"invite_time":1515756369}
jsondata;
        $params = json_decode($params,true);
        $r = Redis::getIns()->rPush(Okey::rGameInviteLogList(),$params);
        if(!$r)
        {
            var_export('rpush 失败');
        }
    }
    public function pushToolList()
    {
        $params = <<<jsondata
{"operate_type":3,"uid":10444,"ttid":12356}
jsondata;
        $params = json_decode($params,true);
        $r = Redis::getIns()->rPush(Okey::rToolLogList(),$params);
        if(!$r)
        {
            var_export('rpush 失败');
        }
    }
    public function pushExpireLog()
    {
        $now = time()+20;
        $params = <<<jsondata
{"uid":21,"ttid":213,"expire_time":{$now}}
jsondata;
        $params = json_decode($params,true);
        $r = Redis::getIns()->lPush(Okey::rToolExpireList(),$params);
        $params = <<<jsondata
{"uid":33,"ttid":213,"expire_time":{$now}}
jsondata;
        $params = json_decode($params,true);
        $r = Redis::getIns()->lPush(Okey::rToolExpireList(),$params);

    }




} 