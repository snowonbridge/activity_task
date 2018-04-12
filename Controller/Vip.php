<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/4/4
 * Time: 14:38
 */

namespace Workerman\Controller;


use Workerman\Lib\Code;
use Workerman\Lib\Controller;
use Workerman\Service\VipService;

class Vip extends Controller
{

    /**
     * @api {get} /vip/get-list  vip等级列表
     * @apiName  /vip/get-list
     * @apiGroup vip VIP模块
     *@apiSampleRequest url  http://测试服地址:9009/vip/get-list
     *
     * @apiSuccess {Number} code  响应编码
     * @apiSuccess {String} msg 信息注解
     * @apiSuccess {Object} data 数据
     * @apiSuccess {Number} charge 当前的积分(充值金额)
     * @apiSuccess {Number} vip 当前的VIP等级
     * @apiSuccess {Number} next_vip 下级的VIP等级
     * @apiSuccess {Number} next_charge 下级的VIP等级对应的充值金额
     * @apiSuccess {Number} balance 离下个VIP等级的金额差值
     * @apiSuccess {Object} list vip列表
     * @apiSuccess {String} name  vip名称
     * @apiSuccess {Number} vip vip等级
     * @apiSuccess {Number} charge vip等级对应的充值金额
     * @apiSuccess {String} privs_text vip等级对应的特权说明
     * @apiSuccessExample {json} 成功的返回:
     *
     * {
     *  "code": 1,
     *  "msg": "获取列表",
     *"data": {
     * "charge": 68889,
     * "vip": 12,
     * "next_vip": 12,
     *  "next_charge": 68888,
     *  "balance": 0,
     *   "list": [
     * {
     * "name": "VIP1",
     * "vip": 1,
     * "charge": 10,
     * "privs_text": "会员标志\r\n好友追踪\r\n好友上限18\r\n"
     *},
     * {
     * "name": "VIP12",
     *"vip": 12,
     * "charge": 68888,
     *"privs_text": "每日补足1000000金币\r\n会员标志\r\n专属牌背\r\n好友追踪\r\n好友上限500\r\n"
     * }
     * ]
     * }
     * }
     */
    public function getList()
    {
        $uid = $this->uid;
//        $uid=10326;
        $ret = VipService::instance()->getList($uid);
        return ['code'=>Code::SUCCESS,'msg'=>'获取列表','data'=>$ret];
    }
    /**
     * @api {get} /vip/get-gift  VIP用户每日补足列表
     * @apiName  /vip/get-gift
     * @apiGroup vip VIP模块
     *@apiSampleRequest url  http://测试服地址:9009/vip/get-gift
     *
     * @apiSuccess {Number} code  响应编码
     * @apiSuccess {String} msg 信息注解
     * @apiSuccess {Object} data 数据
     * @apiSuccess {String} name 特权名称
     * @apiSuccess {Number} num 补足到多少
     * @apiSuccess {Number} id  记录的ID,用不到这个字段，可以不管
     * @apiSuccess {Number} gift_id  道具或货币ID
     *
     * @apiSuccessExample {json} 成功的返回:
     *
     *  {
     *  "code": 1,
     *  "msg": "获取列表",
     *  "data": [
     *  {
     * "id": 8,
     * "name": "每日补足1000000金币",
     * "num": 1000000,
     *  "gift_id": 1
     *  },
     *  {
     *   "id": 8,
     *  "name": "每日补足30禁比卡",
     *  "num": 30,
     * "gift_id": 117
     * }
     *  ]
     * }
     */
    public function getGift()
    {
        $uid = $this->uid;
//        $uid=10326;
        $ret = VipService::instance()->getGift($uid);
        return ['code'=>Code::SUCCESS,'msg'=>'获取列表','data'=>$ret];
    }

    /**
     * @api {post} /vip/recv  vip用户领取补足
     * @apiName  /vip/recv
     * @apiGroup vip VIP模块
     *@apiSampleRequest url  http://测试服地址:9009/vip/get-gift
     *
     * @apiSuccess {Number} code  响应编码
     * @apiSuccess {String} msg 信息注解
     * @apiSuccess {Object} data 数据
     * @apiSuccess {Number} num 补足了多少
     * @apiSuccess {Number} id  道具或货币ID
     *
     * @apiSuccessExample {json} 成功的返回:
     * {
     *  "code": 1,
     *  "msg": "领取成功",
     *  "data": [
     *  {
     *   "id": 1,
     *   "num": 962900
     *  },
     *  {
     *   "id": 117,
     *  "num": 25
     *  }
     *  ]
     *   }
     */

    public function recv()
    {
        $uid = $this->uid;
//        $uid=10326;
        $ret = VipService::instance()->recv($uid);
        return ['code'=>$ret['code'],'msg'=>$ret['msg'],'data'=>$ret['data']];
    }

}