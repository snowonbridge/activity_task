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
use Workerman\Service\CrossChallegeService;

class CrossChallege extends Controller{

    /**
     * @api {post} /cross-challege/add-log  添加活动日志
     * @apiName  /cross-challege/add-log
     * @apiGroup cross-challege 关卡类活动
     *@apiSampleRequest url http://www.soultask.com:9005//cross-challege/add-log

     * @apiParam {string} uid 用户id 【必传】
     * @apiParam {int} activity_id 活动id 【必传】
     * @apiParam {unid} game_no 游戏ID 【必传】
     * @apiParam {string} win_result 输赢结果，是否一定要赢【比传】@1yes,0no
     * @apiParam {string} friends_num 好友数量【必传】

     * @apiParam {string} own_open_room  是否自己开房【必传】  自己开房@1y,0n,2不验证
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
//        $this->post['activity_id']=305;
//        $this->post['game_no']='1001-1';
//        $this->post['win_result']=0;
//        $this->post['friends_num']=0;
//        $this->post['user_level']=0;
//        $this->post['own_open_room']=1;

        $params['uid'] = $this->uid ;
        $params['activity_id'] = $this->param['activity_id'];
        $params['game_no'] = $this->param['game_no'];

        $params['win_result'] = $this->param['win_result'];
        $params['friends_num'] = $this->param['friends_num'];
        $params['user_level'] = 0;
        $params['own_open_room'] = $this->param['own_open_room'];

        $ret = CrossChallegeService::instance()->addLog($params);
        return $ret;
    }
    /**
     * @api {get} /cross-challege/show-activity-status  获取活动领取奖励状态
     * @apiName  /cross-challege/show-activity-status
     * @apiGroup cross-challege 关卡类活动
     * @apiParam {string} uid 用户id 【必传】
     * @apiParam {int} activity_id 活动id 【必传】
     * @apiParam {unid} game_no 游戏ID 【必传】
     * @apiParam {string} win_result 输赢结果，是否一定要赢【比传】@1yes,0no
     * @apiParam {string} friends_num 好友数量【必传】
     *
     * @apiParam {string} own_open_room  是否自己开房【必传】  自己开房@1y,0n,2不验证
     *
     * @apiSuccess {Number} id  数据唯一id
     * @apiSuccess {Number} activity_id 活动id
     * @apiSuccessExample {json} 成功的返回:
     *{
     *"code": 1,
     *"msg": "获取成功",
     *"data": [
     *  {
     *  "activity_id": "304",
     *  "is_receive": 0,
     *"img_icon": "jinbi.png",
     * "gift_list": [
     * {
     * "name": "金币",
     * "num": 500,
     *  "id": 1
     *   }
     *  ]
     * },
     * {
     *"activity_id": "305",
     * "is_receive": 0,
     * "img_icon": "jinbi.png",
     *"gift_list": [
     * {
     * "name": "金币",
     *  "num": 5000,
     *  "id": 1
     * }
     *  ]
     *  },
     *   {
     *  "activity_id": "306",
     * "is_receive": 0,
     * "img_icon": "fagnka.png",
     *"gift_list": [
     * {
     * "name": "房卡 ",
     *  "num": 10,
     *  "id": 3
     *  }
     * ]
     * },
     *  {
     *  "activity_id": "309",
     *   "is_receive": 0,
     *  "img_icon": "fagnka.png",
     *  "gift_list": [
     * {
     * "name": "房卡 ",
     * "num": 2,
     *"id": 3
     * }
     * ]
     * }
     * ]
     * }
     *
     */
    public function showActivityStatus()
    {
//        $this->post['uid'] = 1;
        $uid = $this->uid;
        $ret = CrossChallegeService::instance()->showActivityStatus($uid);
        return array('code'=>Code::SUCCESS,'msg'=>'获取成功','data'=>$ret);
    }
    /**
     * @api {get} /cross-challege/show-activity-gifts  获取活动相关奖励
     * @apiName  /cross-challege/show-activity-gifts
     * @apiGroup cross-challege 关卡类活动
     *
     * @apiParam {int} activity_id 活动id 【必传】
     *
     * @apiSuccess {Number} code  响应编码
     * @apiSuccess {String} msg 信息注解
     * @apiSuccessExample {json} 成功的返回:
     *{
     *"code": 1,
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
//        $this->get['activity_id']=305;
        $activity_id =  $this->param['activity_id'];
        $ret = CrossChallegeService::instance()->showActivityGifts($activity_id);
        return array('code'=>Code::SUCCESS,'msg'=>'获取成功','data'=>$ret);
    }
    /**
     * @api {get} /cross-challege/show-join-status  获取该活动的用户参与进度
     * @apiName  /cross-challege/show-join-status
     * @apiGroup cross-challege 关卡类活动

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
//        $this->get['uid']=1;
//        $this->get['activity_id']=305;
        $uid = $this->uid;
        $activity_id =  $this->param['activity_id'];
        $ret = CrossChallegeService::instance()->showJoinStatus($uid,$activity_id);
        return array('code'=>Code::SUCCESS,'msg'=>'获取活动参与的进度成功','data'=>$ret);
    }
    /**
     * @api {get} /cross-challege/receive-gift  领取奖励
     * @apiName  /cross-challege/receive-gift
     * @apiGroup cross-challege 关卡类活动

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
//        $this->get['uid']=1;
//        $this->get['activity_id']=305;
        $uid = $this->uid;
        $activity_id =  $this->param['activity_id'];
        $ret = CrossChallegeService::instance()->receiveGift($uid,$activity_id);
        return $ret;
    }

} 