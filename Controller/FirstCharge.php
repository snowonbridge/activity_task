<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/3/28
 * Time: 15:47
 */

namespace Workerman\Controller;


use Workerman\Lib\Controller;
use Workerman\Service\FirstChargeService;

class FirstCharge extends Controller
{

    /**
     *
     * @api {get} /first-charge/get  首充
     * @apiName  /first-charge/get
     * @apiGroup first-charge 首充
     *@apiSampleRequest url  http://测试服地址:9009/first-charge/get

     * @apiParam {int} uid 用户id （现有的参数够用,不需要另外添加）【必传】
     *
     * @apiSuccess {Number} code  响应编码
     * @apiSuccess {String} msg 信息注解
     * @apiSuccess {Object} data 数据
     * @apiSuccess {Object} base_gift_content 基础购买的物品
     * @apiSuccess {Object} extra_gift_content 额外赠送物品
     * @apiSuccess {float} money 金额
     * @apiSuccess {int} goods_id 商品ID
     *
     * @apiSuccessExample {json} 成功的返回:
     * {
     *"code": 1,
     *"msg": "获取首充配置",
     * "data": {
     *"id": 1,
     * "goods_id":126,
     * "money": 0,
     * "title": "首充礼包",
     * "base_gift_content": [
     * {
     * "id": 1,
     * "name": "金币",
     *  "num": 60000
     * },
     * {
     *"id": 111,
     *"name": "记牌器",
     * "num": 3
     *  }
     *],
     *"extra_gift_content": [
     * {
     *"id": 1,
     * "name": "金币",
     * "num": 60000
     *},
     *{
     * "id": 2,
     *"name": "钻石",
     * "num": 30
     * }
     * ],
     * "desc": ""
     * }
     * }
     */
    public function get()
    {
        $uid = $this->uid;
        $platform_id = $this->system;
        $channel_id = $this->unid;
//        $uid = 10326;
//        $platform_id = 1;
//        $channel_id = 1;
        $ret = FirstChargeService::instance()->get($uid,$platform_id,$channel_id);
        return $ret;
    }
}