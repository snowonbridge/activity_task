<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-15
 * Time: 上午9:43
 */

namespace Workerman\Controller;



use Workerman\Lib\Code;
use Workerman\Lib\Controller;

use Workerman\Lib\Logger;
use Workerman\Model\ActivityNotice;
use Workerman\Service\EveryDayTaskService;
use Workerman\Service\MonthTaskService;
use Workerman\Model\ActivityCategory;
use Workerman\Model\UserEverydayActivityLog;
use Workerman\Service\ConsumptionService;
use Workerman\Service\CrossChallegeService;
use Workerman\Service\FunctionService;
use Workerman\Service\SocialService;
class MonthTask extends Controller{

    /**
     * @api {post} /month-task/get-list  月任务的活动列表
     * @apiName  /month-task/get-list
     * @apiGroup month-task 月任务
     *@apiSampleRequest url  http://www.soultask.com:9005/month-task/get-list?uid=1

     * @apiParam {int} uid 用户id 【必传】
     *
     * @apiSuccess {Number} code  响应编码
     * @apiSuccess {String} msg 信息注解
     * @apiSuccess {int} month 月份
     * @apiSuccess {int} start_time 活动开始时间戳(秒为单位)，北京时区
     * @apiSuccess {int} end_time 活动结束时间戳(秒为单位)，北京时区
     * @apiSuccess {Object} data 数据
     * @apiSuccess {Object} tab_id 属于哪个tab  1 充值活动,2 消费有奖励,3边玩边收钱
     * @apiSuccess {int} gift 活动奖励
     * @apiSuccess {int} achieve_num 已完成局数
     * @apiSuccess {int} total_num 总局数
     * @apiSuccess {int} is_receive 领取状态 1y,0n，2未领取，已完成
     * @apiSuccess {string} desc 活动描述
     * @apiSuccess {int} redirect_id 跳转id 1 到商城购买钻石  2 房卡模式选房界面。  3 必下场选场界面 4 商城--钻石兑金币 tab页  5:经典场--选场界面  6必下场--选场界面  7房卡模式开房进房界面
     *
     * @apiSuccessExample {json} 成功的返回:
     *  {
     * "code": 1,
     *  "msg": "获取月任务列表",
     *   "data": {
     *   "1": {
     *    "items": [
     *    {
     *    "is_receive": 0,
     *    "achieve_status": 0,
     *     "total_num": 1,
     *     "achieve_num": 0,
     *     "desc": "2101",
     *    "redirect_id": 1,
     *   "base_activity_id": "2102",
     *   "gift": [
     *   {
     *    "name": "金币",
     *   "num": 20000,
     *   "id": 1
     *   },
     *   {
     *  "name": "房卡 ",
     *  "num": 1,
     *  "id": 3
     *   },
     *   {
     *   "name": "禁比卡",
     *    "num": 5,
     *    "id": 117
     *    }
     *   ],
     *  "tab_id": "1",
     *  "activity_id": 2101
     *  }
     *   ],
     *      "start_time": 1504108800,
     *   "end_time": 1506916799,
     *  "title"=>"充值活动"
     *   },
     *  "2": {
     *  "items": [
     *  {
     *  "is_receive": 0,
     *  "achieve_status": 0,
     *  "total_num": 1,
     *  "achieve_num": 0,
     *  "desc": "",
     *  "redirect_id": 4,
     *  "base_activity_id": "2111",
     *  "gift": [
     * {
     * "name": "金币",
     *  "num": 3000,
     *  "id": 1
     *  }
     *  ],
     *   "tab_id": "2",
     *   "activity_id": 2110
     *   }
     *    ],
     *   "start_time": 1504108800,
     *   "end_time": 1506916799,
     *   "title"=>"消费有奖励"
     *   },
     *     "3": {
     *    "items": [
     *     {
     *     "is_receive": 0,
     *    "achieve_status": 0,
     *     "achieve_num": 0,
     *  "total_num": 10,
     *  "base_activity_id": "2114",
     *  "redirect_id": 5,
     *   "desc": "2113",
     *   "gift": [
     *    {
     *    "name": "金币",
     *     "num": 1000,
     *    "id": 1
     *    }
     *     ],
     *     "tab_id": "3",
     *     "activity_id": 2113
     *     }
     *     ],
     *  "start_time": 1504108800,
     *   "end_time": 1506916799,
     *   "title": "边玩边收钱"
     *   }
     *   }
     *    }
     *
     */
    public function getList()
    {
      $uid = $this->uid;
      $sid = $this->sid;
      $version = $this->version;
      $unid = $this->unid;
//        $uid = 178;
//        $sid = 10001;
//        $version = '1.0.0';
//        $unid = 2;
      $ret =  MonthTaskService::instance()->getList($uid,$sid,$version,$unid);
      return array('code'=>Code::SUCCESS,'msg'=>'获取月任务列表','data'=>$ret);
    }
    /**
     * @api {get} /month-task/receive-gift  领取月任务奖励
     * @apiName  /month-task/receive-gift
     * @apiGroup month-task 月任务
     *@apiSampleRequest url  http://www.soultask.com:9005/month-task/receive-gift

     * @apiParam {int} uid 用户id 【必传】
     * @apiParam {int} param:activity_id 活动id 【必传】
     *
     * @apiSuccess {Number} code  响应编码
     * @apiSuccess {String} msg 信息注解
     * @apiSuccess {Object} data 数据
     * @apiSuccess {int} gift 活动奖励
     * @apiSuccess {int} activity_id 活动id
     * @apiSuccess {int} achieve_num 已完成局数
     * @apiSuccess {int} total_num 总局数
     * @apiSuccess {int} is_receive 领取状态 1y,0n，2未领取，已完成
     *@apiSuccessExample {json} 失败返回:
     * {
     * "code": 1,
     * "msg": "领取活动任务奖励成功",
     * "data": [
     *  "activity_id"=>2014,
     *  "total_num"=>20,
     *  "achieve_num"=>11,
     *  "is_receive"=>0,
     *  "gift"=>[
     *    {
     *    "name": "金币",
     *     "num": 1000,
     *    "id": 1
     *    }
     * ]
     * ]
     * }
     */
    public function receiveGift()
    {

//        $uid = 507;
//        $activity_id = 2106 ;
        $uid = $this->uid;
        $activity_id = $this->param['activity_id'];
        $ret = MonthTaskService::instance()->receiveGift($uid,$activity_id);
        return array('code'=>$ret['code'],'msg'=>$ret['msg'],'data'=>$ret['data']);
    }
    /**
     * @api {get} /month-task/notice-list  公告列表
     * @apiName  /month-task/notice-list
     * @apiGroup month-task 月任务
     *@apiSampleRequest url  http://www.soultask.com:9005/month-task/notice-list
     *
     * @apiSuccess {Number} code  响应编码
     * @apiSuccess {String} msg 信息注解
     * @apiSuccess {Object} data 数据
     * @apiSuccess {String} data:title 标题
     * @apiSuccess {String} data:content 公告内容
     * @apiSuccess {String} data:label 标签@1热门
     * @apiSuccess {String} data:img_icon 图片
     *
     * @apiSuccessExample {json} 成功的返回:
     *{
     *"code": 1,
     *"msg": "获取公告列表",
     * "data": [
     *  {
     * "id": "1",
     *  "title": "新手通知",
     * "content": "爷爷叫你回家吃饭",
     * "add_time": "2017-09-18 02:01:00",
     * "img_icon": "无",
     *   "label": "1"
     *   }
     *   ]
     *  }
     */
    public function noticeList()
    {

        $list = ActivityNotice::instance()->getAll($this->sid,$this->version);
        foreach($list as $k=>$v)
        {
            if(empty($v['img_icon']))
                $list[$k]['type'] = 1;//文字公告
            else{
                $list[$k]['type'] = 2;//图片公告
            }
            $list[$k]['label'] = (int)$list[$k]['label'];
        }
        return array('code'=>Code::SUCCESS,'msg'=>'获取公告列表','data'=>$list);
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

                $ret = ConsumptionService::instance()->addLog($params);

                break;;
            case self::CROSS_CHALLEGE_TYPE:
                if( !$this->isCrossScenes($params))
                {
                    Logger::write('数据缺失','CrossScenes',"ERROR");
                    continue;
                }
                $ret =  CrossChallegeService::instance()->addLog($params);

                break;
//            case self::FUNCTION_TYPE:
//                if( !$this->isFunctionScenes($params))
//                {
//                    Logger::write('数据缺失','FunctionScenes',"ERROR");
//                    continue;
//                }
//                $ret =  FunctionService::instance()->addLog($params);    break;
//            case self::SOCIAL_TYPE:
//                if( !$this->isSocialScenes($params))
//                {
//                    Logger::write('数据缺失','SocialScenes',"ERROR");
//                    continue;
//                }
//                $ret =  SocialService::instance()->addLog($params);    break;
            default:
                Logger::write('活动类型不支持','default deal',"ERROR");
                continue;
        }

    }
    /**
     * @api {get} /month-task/get-gift-status  获取奖励可领取状态
     * @apiName  /month-task/get-gift-status
     * @apiGroup month-task 月任务
     *@apiSampleRequest url  http://www.soultask.com:9005/month-task/get-gift-status
     * @apiParam {int} uid 用户id 【必传】
     * @apiSuccess {Number} code  响应编码
     * @apiSuccess {String} msg 信息注解
     * @apiSuccess {Object} data 数据
     * @apiSuccess {String} data:monthGiftStatus 月活动奖励可领取数量
     * @apiSuccess {String} data:dayGiftStatus 每日活动奖励可领取数量
     *
     * @apiSuccessExample {json} 成功的返回:
     *{
     *"code": 1,
     *"msg": "获取状态成功",
     * "data": {
     * "monthGiftStatus": 1,
     * "dayGiftStatus": 0
     * }
     *}
     */
    public function getGiftStatus()
    {
        $uid = $this->uid;
        $sid = $this->sid;
        $version = $this->version;
        $unid = $this->unid;
//        $uid = 507;
        $data['monthGiftStatus'] = MonthTaskService::instance()->getGiftStatus($uid,$sid,$version,$unid);
        $data['dayGiftStatus'] = EveryDayTaskService::instance()->getGiftStatus($uid);

        return array('code'=>Code::SUCCESS,'msg'=>'获取状态成功','data'=>$data);
    }

} 