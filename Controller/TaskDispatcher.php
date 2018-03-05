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
use Workerman\Model\TurnUserLog;
use Workerman\Model\UserEverydayActivityLog;
use Workerman\Service\ATCode;
use Workerman\Service\ConsumptionService;
use Workerman\Service\CrossChallegeService;
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

    public function test()
    {

    }





} 