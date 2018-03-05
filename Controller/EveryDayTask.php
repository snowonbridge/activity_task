<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-13
 * Time: 上午10:48
 */

namespace Workerman\Controller;


use Workerman\Lib\Code;
use Workerman\Lib\Controller;
use Workerman\Lib\Logger;
use Workerman\Model\UserEverydayActivityCountsLog;
use Workerman\Service\EveryDayTaskService;
use Workerman\Model\ActivityCategory;
use Workerman\Model\UserEverydayActivityLog;
use Workerman\Service\ConsumptionService;
use Workerman\Service\CrossChallegeService;
use Workerman\Service\FunctionService;
use Workerman\Service\SocialService;
class EveryDayTask extends Controller{

    /**
     * @api {get} /every-day-task/get-list  每日任务的活动列表
     * @apiName  /every-day-task/get-list
     * @apiGroup every-day-task 每日任务
     *@apiSampleRequest url  http://www.soultask.com:9005/every-day-task/get-list?uid=1

     * @apiParam {int} uid 用户id 【必传】
     *
     * @apiSuccess {Number} code  响应编码
     * @apiSuccess {String} msg 信息注解
     * @apiSuccess {Object} data 数据
     * @apiSuccess {int} achieve_status 完成状态 1y,0n
     * @apiSuccess {int} gift 活动奖励
     * @apiSuccess {int} achieve_num 已完成局数
     * @apiSuccess {int} total_num 总局数
     * @apiSuccess {int} is_receive 领取状态 1y,0n
     * @apiSuccess {string} desc 活动描述
     *
     * @apiSuccessExample {json} 成功的返回:
     *{
     *  "code": 1,
     *  "msg": "获取每日任务列表",
     *  "data": [
     *  {
     *      "activity_id": "3011"
     *      "is_receive": 0,
     *      "achieve_status": 0,
     *      "desc": '',
     *      "challege_config_id": "10",
     *       "gift": [
     *      {
     *       "name": "金币",
     *       "num": 10000,
     *      "id": 1
     *      }
     *      ]
     *  },
     *  {
     *      "activity_id": "309"
     *      "is_receive": 0,
     *      "achieve_status": 1,
     *      "achieve_num": 0,
     *      "total_num": "5",
     *      "desc": '',
     *      "challege_config_id": "3",
     *      "gift": [
     *     {
     *      "name": "房卡 ",
     *      "num": 10,
     *      "id": 3
     *     }
     *     ]
     *  }
     *}
     *
     */
    public function getList()
    {
        $uid = $this->uid;

        $list = EveryDayTaskService::instance()->getList($uid);
        return array('code'=>Code::SUCCESS,'msg'=>'获取每日任务列表','data'=>$list);
    }
    /**
     * @api {get} /every-day-task/receive-gift  领取每日任务奖励
     * @apiName  /every-day-task/receive-gift
     * @apiGroup every-day-task 每日任务
     *@apiSampleRequest url  http://www.soultask.com:9005/every-day-task/receive-gift

     * @apiParam {int} uid 用户id 【必传】
     * @apiParam {int} param:activity_id 活动id 【必传】
     *
     * @apiSuccess {Number} code  响应编码
     * @apiSuccess {String} msg 信息注解
     * @apiSuccess {Object} data 数据
     *
     * @apiSuccessExample {json} 成功的返回:
     *{
     *"code": 1,
     *"msg": "领取活动任务奖励成功",
     * "data": []
     * }
     *@apiErrorExample {json} 失败返回:
     * {
     * "code": -135,
     * "msg": "已领取奖励,不能多次领取",
     * "data": []
     * }
     */
    public function receiveGift()
    {

//        $uid = 507;
//        $activity_id = 304;
        $uid = $this->uid;
        $activity_id = $this->param['activity_id'];
        $ret = EveryDayTaskService::instance()->receiveGift($uid,$activity_id);
        return array('code'=>$ret['code'],'msg'=>$ret['msg'],'data'=>$ret['data']);
    }
    public  function addLog($activity_id,$params)
    {

        $type = ActivityCategory::instance()->getActivityType($activity_id);

        $params['activity_id'] = $activity_id;

        switch($type)
        {
            case self::CONSUMPTION_TYPE:
                if( !$this->isConsumptionScenes($params))
                {
                    Logger::write('数据缺失','ConsumptionScenes',"ERROR");
                    continue;
                }

                $ret = ConsumptionService::instance()->addLog($params);    break;

            case self::CROSS_CHALLEGE_TYPE:
                if( !$this->isCrossScenes($params))
                {
                    Logger::write('数据缺失','CrossScenes',"ERROR");
                    continue;
                }
                $ret =  CrossChallegeService::instance()->addLog($params);    break;
            case self::FUNCTION_TYPE:
                if( !$this->isFunctionScenes($params))
                {
                    Logger::write('数据缺失','FunctionScenes',"ERROR");
                    continue;
                }
                $lastId = $this->setCounts($params);
                $ret =  FunctionService::instance()->addLog($params);
                if($ret['code'] == Code::SUCCESS)
                {
                    UserEverydayActivityCountsLog::instance()->incrAchieveCountsById($lastId);
                }
                break;
            case self::SOCIAL_TYPE:
                if( !$this->isSocialScenes($params))
                {
                    Logger::write('数据缺失','SocialScenes',"ERROR");
                    continue;
                }
                $lastId = $this->setCounts($params);
                $ret =  SocialService::instance()->addLog($params);
                if($ret['code'] == Code::SUCCESS)
                {
                    UserEverydayActivityCountsLog::instance()->incrAchieveCountsById($lastId);
                }
                break;
            default:
                Logger::write('活动类型不支持','default deal',"ERROR");
                continue;
        }


        return true;
    }
    /**
     * @desc 设置用户某活动发生的次数
     * @param $params
     * @return int
     */
    public function  setCounts($params)
    {
        $data=array();
        $data['uid'] = $params['uid'];

        $data['activity_id'] = $params['activity_id'];

        $data['counts'] = 1;
        $data['achieve_counts'] = 0;
        $data['add_time'] = date('Ymd');


        $logInfo = UserEverydayActivityCountsLog::instance()->getOne($data['uid'],$params['activity_id'],date('Ymd'));
        if($logInfo)
        {
            UserEverydayActivityCountsLog::instance()->incrCounts($data['uid'],$params['activity_id'],$data['add_time']);
            return $logInfo['id'];
        }else{
            $lastId = UserEverydayActivityCountsLog::instance()->add($data);
            return $lastId;
        }
    }

} 