<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/3/27
 * Time: 15:10
 */

namespace Workerman\Controller;


use Workerman\Lib\Controller;
use Workerman\Service\CheckInService;
use Workerman\Service\NewHandService;

class NewHand extends Controller
{
    /**
     * @api {get} /new-hand/get-list  签到奖励列表
     * @apiName  /new-hand/get-list
     * @apiGroup new-hand 新手奖励
     *@apiSampleRequest url  http://测试服地址:9009/new-hand/get-list?

     * @apiParam {int} uid 用户id 【必传】

     * @apiSuccess {Number} code  响应编码
     * @apiSuccess {String} msg 信息注解
     * @apiSuccess {Object} data 数据
     * @apiSuccess {object} list 礼物列表
     * @apiSuccess {Number} time_nd 第几次
     * @apiSuccess {int} is_received 领取状态 1-Y,0 -N
     *
     * @apiSuccessExample {json} 成功的返回:
     *  {
     *  "code": 1,
     *  "msg": "获取新手奖励列表",
     * "data": [
     *  {
     *  "time_nd": 1,
     * "list": [
     * {
     *     "id": 1,
     *   "name": "金币",
     *  "num": 7000
     * }
     *  ],
     *  "is_received": 1
     *  },
     *  {
     *  "time_nd": 2,
     * "list": [
     * {
     * "id": 1,
     * "name": "金币",
     *  "num": 8000
     *  },
     *  {
     *  "id": 110,
     *  "name": "记牌器I-2小时记牌器",
     *  "num": 1
     *  }
     *  ],
     *  "is_received": 1
     *   },
     *   {
     *  "time_nd": 3,
     *  "list": [
     *  {
     *  "id": 1,
     *  "name": "金币",
     *  "num": 10000
     *  },
     *  {
     *  "id": 110,
     *  "name": "记牌器I-2小时记牌器",
     * "num": 1
     * },
     *  {
     *  "id": 117,
     *  "name": "禁比卡",
     *  "num": 5
     *  }
     * ],
     * "is_received": 1
     *  }
     *  ]
     *  }
     *
     */
    public function getList()
    {
        $uid = $this->uid;
        $platform_id = $this->system;
//        $uid = 10326;
//        $platform_id = 1;
        $ret = NewHandService::instance()->getList($uid,$platform_id);
        return $ret;
    }
    /**
     * @api {get} /new-hand/change  新手奖励领取和关闭日志
     * @apiName  /new-hand/change
     * @apiGroup new-hand 新手奖励
     *@apiSampleRequest url  http://测试服地址:9009/new-hand/change

     * @apiParam {int} uid 用户id 【必传】
     * @apiParam {int} param:type  1：领取新手奖励操作; 2:关闭操作【必传】

     * @apiSuccess {Number} code  响应编码
     * @apiSuccess {String} msg 信息注解
     * @apiSuccess {Object} data 数据
     *
     * @apiSuccessExample {json} 成功的返回: type=1
     * {
     * "code": 1,
     * "msg": "当天领取新手奖励成功",
     * "data": [
     * {
     * "id": 1,
     * "name": "金币",
     * "num": 10000
     * },
     *{
     *"id": 110,
     *"name": "记牌器I-2小时记牌器",
     *  "num": 1
     *  },
     *  {
     *  "id": 117,
     *"name": "禁比卡",
     * "num": 5
     * }
     *  ]
     *  }
     * @apiSuccessExample {json} 成功的返回:type=2
     * {
     *  "code": 1,
     *   "msg": "写关闭按钮日志成功",
     *   "data": []
     *  }
     *
     */
    public function change()
    {
        $uid = $this->uid;
        $platform_id = $this->system;
        $type = $this->param['type'];
        $uid = 10326;
        $platform_id = 3;
        $type=2;
        $ret = NewHandService::instance()->change($uid,$platform_id,$type);
        return $ret;
    }


}