<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-11
 * Time: 上午11:18
 */

namespace Workerman\Controller;


use Workerman\Lib\Code;
use Workerman\Lib\Controller;
use Workerman\Service\SocialService;

class Social extends Controller{
    /**
     * @api {post} /social/add-log  添加活动日志
     * @apiName  /social/add-log
     * @apiGroup social 社交类活动
     *@apiSampleRequest url http://www.soultask.com:9005/social/add-log

     * @apiParam {string} uid 用户id 【必传】
     * @apiParam {int} activity_id 活动id 【必传】
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
     *      "activity_id": "3011",
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
     *  "msg": "频率超出限制",
     *  "data": []
     *  }
     *
     */
    public function addLog()
    {
//        $this->post['uid'] = 1;
//        $this->post['activity_id']=307;
        $params['uid'] = $this->uid;

        $params['activity_id'] = $this->param['activity_id'];
        $ret = SocialService::instance()->addLog($params);
        return $ret;
    }
    /**
     * @api {get} /social/show-activity-status  获取活动领取奖励状态
     * @apiName  /social/show-activity-status
     * @apiGroup social 社交类活动

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
        $uid = $this->uid;
        $ret = SocialService::instance()->showActivityStatus($uid);
        return array('code'=>Code::SUCCESS,'msg'=>'获取成功','data'=>$ret);
    }
    /**
     * @api {get} /social/show-activity-gifts  获取活动相关奖励
     * @apiName  /social/show-activity-gifts
     * @apiGroup social 社交类活动
     *
     * @apiParam {int} activity_id 活动id 【必传】
     *
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
//        $this->get['activity_id'] = 308;
        $activity_id =  $this->param['activity_id'];
        $ret = SocialService::instance()->showActivityGifts($activity_id);
        return array('code'=>Code::SUCCESS,'msg'=>'获取成功','data'=>$ret);
    }
    /**
     * @api {get} /social/show-join-status  获取该活动的用户参与进度
     * @apiName  /social/show-join-status
     * @apiGroup social 社交类活动

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
//        $this->get['activity_id'] = 307;
//        $this->get['uid'] = 1;
        $uid = $this->uid;
        $activity_id =  $this->param['activity_id'];
        $ret = SocialService::instance()->showJoinStatus($uid,$activity_id);
        return array('code'=>Code::SUCCESS,'msg'=>'获取成功','data'=>$ret);
    }
    /**
     * @api {get} /social/receive-gift  领取奖励
     * @apiName  /social/receive-gift
     * @apiGroup social 社交类活动

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
//        $this->get['activity_id'] = 3011;
//        $this->get['uid'] = 1;
        $uid = $this->uid;
        $activity_id =  $this->param['activity_id'];
        $ret = SocialService::instance()->receiveGift($uid,$activity_id);
        return $ret;
    }
} 