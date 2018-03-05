<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-11
 * Time: 上午11:17
 */

namespace Workerman\Controller;


use Workerman\Lib\Code;
use Workerman\Lib\Controller;
use Workerman\Service\ConsumptionService;

class Consumption extends Controller{

    /**
     * @api {post} /consumption/add-log  添加活动日志
     * @apiName  /consumption/add-log
     * @apiGroup consumption 消费类活动
     * @apiSampleRequest url http://www.soultask.com:9005/consumption/add-log

     * @apiParam {string} uid 用户id 【必传】
     * @apiParam {int} activity_id 活动id 【必传】
     * @apiParam {int} from_gift_id 货币，道具ID 【必传】
     * @apiParam {string} action_id 消费动作id,参考excel
     * @apiParam {string} from_num  from_gift_id的数量【必传】
     * @apiParam {string} magic  是否使用魔法表情【必传】 @1y,0n,2不验证
     * @apiParam {string} login_check  是否登录状态调用@1y,0n【必传】
     *
     * @apiSuccess {Number} id  数据唯一id
     * @apiSuccess {Number} activity_id 活动id
     * @apiSuccessExample {json} 成功的返回:
     *{
     * "code": 1,
     *  "msg": "添加任务进度成功",
     *  "data": {
     *      "id": "2",
     *      "uid": "1",
     *      "activity_id": "304",
     *       "challege_list": "1",
     *      "achieve_list": "{\"1\":[9,0]}",
     *      "gift_list": "{\"1\":500}",
     *       "is_receive": "2",
     *       "frequency": "1",
     *      "current_frequency": "1",
     *      "img_icon": "jinbi.png",
     *      "add_time": "20170912",
     *       "create_time": "1505192535",
     *      "update_time": "1505192535"
     *      }
     * }
     * @apiErrorExample {json} 失败返回:
     *  {
     *  "code": -238,
     *  "msg": "该活动不存在该游戏关卡,无相关关卡条件",
     *  "data": []
     *  }
     *
     */
    public function addLog()
    {
//        $this->post['uid'] = 1;
//        $this->post['activity_id']=301;
//        $this->post['from_gift_id']=5;
//        $this->post['action_id']=5;
//        $this->post['from_num']=1;
//        $this->post['user_level']=0;
//        $this->post['magic']=1;
//        $this->post['login_check']=0;
        $params['uid'] = $this->uid;
        $params['activity_id'] = $this->param['activity_id'];
        $params['from_gift_id'] = $this->param['from_gift_id'];
        $params['action_id'] = $this->param['action_id'];
        $params['from_num'] = $this->param['from_num'];
        $params['user_level'] = 0;
        $params['magic'] = $this->param['magic'];
        $params['login_check'] = $this->param['login_check'];
        $ret = ConsumptionService::instance()->addLog($params);
        return $ret;
    }
    /**
     * @api {get} /consumption/show-activity-status  获取活动领取奖励状态
     * @apiName  /consumption/show-activity-status
     * @apiGroup consumption 消费类活动

     * @apiParam {string} uid 用户id 【必传】

     *
     * @apiSuccess {Number} id  数据唯一id
     * @apiSuccess {Number} activity_id 活动id
     * @apiSuccess {Number} is_receive 奖励领取状态@1已领取,0不能领取 2.未领取
     * @apiSuccessExample {json} 成功的返回:
     *{
     *"code": 1,
     *"msg": "获取成功",
     *"data":
     * [
     * {
     * "activity_id": "131",
     * "is_receive": 0,
     *"img_icon": "1",
     *"gift_list": [
     *  {
     * "name": "金币",
     * "num": 30000,
     * "id": 1
     * }
     * ]
     * },
     * {
     * "activity_id": "301",
     * "is_receive": 0,
     * "img_icon": "2",
     * "gift_list": [
     * {
     * "name": "金币",
     *"num": 500,
     *"id": 1
     *  }
     *  ]
     *  },
     * ]
     * }
     *
     */
    public function showActivityStatus()
    {
//        $this->get['uid'] = 1;
//        $this->get['user_level'] = 1;
        $uid = $this->uid;
        $user_level = 0;
        $ret = ConsumptionService::instance()->showActivityStatus($uid,$user_level);
        return array('code'=>Code::SUCCESS,'msg'=>'获取成功','data'=>$ret);
    }
    /**
     * @api {get} /consumption/show-activity-gifts  获取活动相关奖励
     * @apiName  /consumption/show-activity-gifts
     * @apiGroup consumption 消费类活动
     *
     * @apiParam {int} activity_id 活动id 【必传】

     * @apiSuccess {Number} code  响应编码
     * @apiSuccess {String} msg 信息注解
     * @apiSuccessExample {json} 成功的返回:
     *{
     *  "code": 1,
     *  "msg": "获取成功",
     *  "data": [
     * {
     *  "name": "金币",
     *  "num": 5000,
     * "id": 1
     * }
     * ]
     * }
     * @apiErrorExample {json} 失败返回:
     *  {
     *  "code": -238,
     *  "msg": "该活动不存在该游戏关卡,无相关关卡条件",
     *  "data": []
     *  }
     *
     */
    public function showActivityGifts()
    {
//        $this->get['activity_id'] = 302;
//        $this->get['user_level'] = 1;
        $activity_id =  $this->param['activity_id'];
        $user_level =  0;
        $ret = ConsumptionService::instance()->showActivityGifts($activity_id);
        return array('code'=>Code::SUCCESS,'msg'=>'获取成功','data'=>$ret);
    }
    /**
     * @api {get} /consumption/show-join-status  获取该活动的用户参与进度
     * @apiName  /consumption/show-join-status
     * @apiGroup consumption 消费类活动

     * @apiParam {int} activity_id 活动id 【必传】
     * @apiParam {int} uid 用户id 【必传】
     * @apiParam {int} scene_id 场景id 【必传】
     *
     * @apiSuccess {Number} code  响应编码
     * @apiSuccess {String} msg 信息注解
     * @apiSuccess {Object} data 数据
     * @apiSuccessExample {json} 成功的返回:
     * {
     * "code": 1,
     *  "msg": "获取活动参与的进度成功",
     *  "data": [
     *    {
     * "achieve_status": true,
     *  "achieve_num": 0,
     *  "total_num": "1",
     *  "challege_config_id": "2"
     *  }
     *  ]
     *  }
     *
     */
    public function showJoinStatus()
    {
//        $this->get['activity_id'] = 301;
//        $this->get['uid'] = 1;
        $uid = $this->uid;
        $activity_id =  $this->param['activity_id'];
        $ret = ConsumptionService::instance()->showJoinStatus($uid,$activity_id);
        return array('code'=>Code::SUCCESS,'msg'=>'获取成功','data'=>$ret);
    }
    /**
     * @api {get} /consumption/receive-gift  领取奖励
     * @apiName  /consumption/receive-gift
     * @apiGroup consumption 消费类活动

     * @apiParam {int} activity_id 活动id 【必传】
     * @apiParam {int} uid 用户id 【必传】
     * @apiParam {int} scene_id 场景id 【必传】
     *
     * @apiSuccess {Number} code  响应编码
     * @apiSuccess {String} msg 信息注解
     * @apiSuccess {Object} data 数据
     * @apiSuccessExample {json} 成功的返回:
     * {
     * "code": 1,
     * "msg": "领取活动任务奖励成功",
     * "data": []
     * }
     *
     */
    public function receiveGift()
    {
//        $this->get['activity_id'] = 301;
//        $this->get['uid'] = 1;
        $uid = $this->uid;
        $activity_id =  $this->param['activity_id'];
        $ret = ConsumptionService::instance()->receiveGift($uid,$activity_id);
        return $ret;
    }


} 