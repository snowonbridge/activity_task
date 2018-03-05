<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-10-13
 * Time: 下午5:40
 */

namespace Workerman\Controller;


use Workerman\Lib\Code;
use Workerman\Lib\Controller;
use Workerman\Model\TurnUserLuckyValue;
use Workerman\Service\TurnLotteryService;

class TurnLottery extends Controller {


    /**
     * @api {post} /turn-lottery/get-list  获取奖品列表
     * @apiName  /turn-lottery/get-list
     * @apiGroup turn-lottery 转盘活动
     * @apiSampleRequest url http://www.soultask.com:9005/turn-lottery/get-list
     *
     * @apiSuccess {Number} gift_content_id  奖品id
     * @apiSuccess {Object} lottery_list
     * @apiSuccess {Number}  lottery_list:id 兑换类型id
     * @apiSuccess {Number}  lottery_list:diamond 钻石数量
     * @apiSuccess {Number}  lottery_list:counts 可抽奖次数
     * @apiSuccessExample {json} 成功的返回:
     *{
     *    "code": 1,
     *    "msg": "获取成功",
     *    "data": {
     *    "gift_list": [
     *
     *        {
     *           "id": 11,
     *           "name": "禁比卡5张",
     *          "gift_content_id": 117,
     *          "num": "5"
     *      },
     *      {
     *           "id": 12,
     *           "name": "禁比卡1张",
     *           "gift_content_id": 117,
     *           "num": "1"
     *       }
     *   ],
     *   "lottery_list": [
     *   {
     *       "id": 1,
     *       "diamond": 10,
     *       "counts": 5
     *   },
     *   {
     *  "id": 2,
     *   "diamond": 20,
     *  "counts": 10
     *  }
     *  ]
     *   }
     * }
     *
     */

    public function getList()
    {
        $data = TurnLotteryService::instance()->getList();
        return ['code'=>Code::SUCCESS,'msg'=>'获取成功','data'=>$data];
    }


    /**
     * @api {post} /turn-lottery/lottery  兑换抽奖
     * @apiName  /turn-lottery/lottery
     * @apiGroup turn-lottery 转盘活动
     * @apiSampleRequest url http://www.soultask.com:9005/turn-lottery/get-list
     * @apiParam {string} uid 用户id 【必传】
     * @apiParam {Object} param
     * @apiParam {Number} param:lottery_id 兑换类型id
     * @apiSuccess {Number} uid  奖品id
     * @apiSuccess {Object} data
     * @apiSuccess {Number}  gift_content_id 奖励id
     * @apiSuccess {Number}  nummond 奖励数量
     * @apiSuccessExample {json} 成功的返回:
     *{
     * "msg": "获取列表",
     * "code": 1,
     * "data": {
     * "gift_list": [
     *{
     * "gift_content_id": 117,
     * "num": 2
     * },
     *  {
     * "gift_content_id": 1,
     * "num": 7000
     *  },
     *  {
     *"gift_content_id": 111,
     * "num": 2
     * },
     * {
     *"gift_content_id": 3,
     * "num": 2
     * }
     * ],
     *"gift_ids": [
     * {
     * "id": 1
     * },
     *  {
     * "id": 2
     *  },
     *{
     * "id": 2
     *  },
     * {
     * "id": 1
     *  },
     * {
     * "id": 2
     *}
     *  ]
     * }
     *  }
     *
     */
    public function lottery()
    {
        $uid = $this->uid;
        $lottery_id = $this->param['lottery_id'];
//        $uid=816;
//        $lottery_id=1;
        $result = TurnLotteryService::instance()->lottery($uid,$lottery_id);
        return ['msg'=>$result['msg'],'code'=>$result['code'],'data'=>$result['data']];

    }

} 