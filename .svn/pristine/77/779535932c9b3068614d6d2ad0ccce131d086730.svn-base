<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-10-9
 * Time: 下午8:10
 */

namespace Workerman\Service;


use Workerman\Lib\Code;
use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Model\CrossChallegeConfig;
use Workerman\Model\CrossChallegeGift;
use Workerman\Model\UserWinStreakLog;

class WinStreakService extends Model{

    /**
     * @param $params:uid
     * @param $params:activity_id
     * @param $params:game_num
     * @param $params:room_token
     * @param $params:game_no
     * @param $params:win_result
     * @param $params:friends_num
     * @param $params:own_open_room
     */
    public function addLog($params)
    {
        $uid = $params['uid'];
        $activity_id = $params['activity_id'];
        $achieve_counts = $params['game_num'];
        $room_token = $params['room_token'];
        $game_no = $params['game_no'];
        $win_result = $params['win_result'];
        $friends_num = $params['friends_num'];
        $own_open_room = $params['own_open_room'];
        $cross_challege = CrossChallegeGift::instance()->getOne($activity_id);
        //连胜配置中challege_list是一个int值
        if(!$cross_challege || !$cross_challege['challege_list'])
        {
            Logger::write('连胜配置有问题',__METHOD__);
            return false;
        }
        $config_id = $cross_challege['challege_list'];

        if(!isset($config_id))
        {
            return false;
        }
        //验证关卡条件配置
        if(!CrossChallegeConfig::instance()->validWinStreakConfig(intval($config_id),$params))
        {
            Logger::write('验证不通过',__METHOD__);
            return false;
        }
        if(UserWinStreakLog::instance()->validateTaskAchieved($uid,$activity_id,$room_token))
        {//已领取奖励
            Logger::write('已领取奖励',__METHOD__);
            return false;
        }
        $data['uid']=$uid;
        $data['activity_id']=$activity_id;
        $data['room_token']=$room_token;
        $data['game_no']=$game_no;
        $config = CrossChallegeConfig::instance()->getOne($config_id);
        $data['counts'] = $config['game_num'];
        $data['achieve_counts'] = $achieve_counts;
        $data['is_receive'] = UserWinStreakLog::RECEIVE_YES;
        $ret=UserWinStreakLog::instance()->add($data);
        if(!$ret)
        {
            Logger::write('添加连胜日志失败1',__METHOD__);
           return false;
        }
        foreach(json_decode($cross_challege['gift_list'],true)  as $gift_content_id=>$num)
        {
            GiftService::instance()->insertGift($uid,$gift_content_id,$num,'连胜活动',ATCode::CLMODE_WIN_STREAK);
        }

        return $cross_challege['gift_list'];
    }



} 