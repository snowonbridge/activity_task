<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-8
 * Time: 下午4:19
 */

namespace Workerman\Service;


use Workerman\Lib\Code;
use Workerman\Lib\Curl;
use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Lib\Okey;
use Workerman\Lib\Redis;
use Workerman\Model\DiamondLog;
use Workerman\Model\GiftContentSetting;
use Workerman\Model\MoneyLog;
use Workerman\Model\RoomCardLog;
use Workerman\Model\UserGame;
use Workerman\Model\UserTool;

class GiftService extends Model{

    /**
     * @desc 用户过关获得奖励
     * @param $uid int 用户id
     * @param $gift_content_id
     * @param $num
     * @param string $desc 每日首登 或  月积累奖励
     * @param int $isSync 同步到服务器
     * @return array|bool
     */
    public  function insertGift($uid,$gift_content_id,$num,$desc='牌局关卡活动',$clmode='',$isSync=1,$scene_id=0)
    {
        $clmode = $clmode?$clmode:ATCode::CLMODE_ACTIVITY_TASK;
        switch($gift_content_id)
        {
            case GiftContentSetting::GIFT_TYPE_MONEY://金币

                $userInfo = UserGame::instance()->getOne($uid);

                if(!$userInfo)
                {
                    Logger::write('usergame用户信息未找到','FirstDailyLoginService insertGift','ERROR');
                    return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'获取失败','data'=>array());
                }

                try{
                    $u = UserGame::instance()->addGoldCoin($uid,$num);

                    $data=array();
                    $data['uid'] = $uid;
                    $data['clmode'] = $clmode;
                    $data['clflag'] = MoneyLog::PLUS;
                    $data['clchip'] = $num;
                    $data['clleftchip']=isset($userInfo['chip'])?$userInfo['chip']+$num:$num;
                    $data['clremark'] = $desc;
                    $data['cldesc'] = date('Y-m-d H:i:s',time())."{$desc}赠送{$num}金币";

                    $t = MoneyLog::instance()->addLog($data);

                    if(!$u || !$t)
                    {
                        return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'增加金币操作失败','data'=>array());
                    }
                    if($isSync)
                        $this->syncDataToServer($uid,$scene_id);
                    return array('code'=>Code::SUCCESS,'msg'=>'增加金币成功','data'=>array());
                }catch (\Exception $e)
                {
                    return array('code'=>Code::OPERATEEXCEPTION,'msg'=>$e->getMessage(),'data'=>array());
                }
            case GiftContentSetting::GIFT_TYPE_SUB_CARD_II://分牌卡
            case GiftContentSetting::GIFT_TYPE_SUB_CARD_III://分牌卡
            case GiftContentSetting::GIFT_TYPE_SUB_CARD://分牌卡
                //#TODO
                $data = array();
                $data['tlid'] = UserTool::TOOL_SEPREATE_CARD;
                $data['usetimes'] = 1;
                $data['tlusetime'] = 0;
                $data['uid'] = $uid;
                $data['status'] = UserTool::STATUS_CAN_USE;
                $data['send'] = UserTool::SEND_ADMIN;
                $data['ref'] = $clmode;
                $data['source'] = $desc;
                for($i=0;$i<$num;$i++)
                {
                    $batch[] = $data;
                }
                $b = UserTool::instance()->addTools($batch);
                if($b)
                {
                    return array('code'=>Code::SUCCESS,'msg'=>'赠送成功','data'=>array());
                }else{
                    return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'赠送失败1','data'=>array());
                }
                ;break;
            case GiftContentSetting::GIFT_TYPE_REMENBCARD_II:
            case GiftContentSetting::GIFT_TYPE_REMENBCARD_III:
            case GiftContentSetting::GIFT_TYPE_REMENBCARD ://计牌器

                $data = array();
                $data['tlid'] = UserTool::TOOL_REMIND_CARD;
                $data['usetimes'] = 1;
                $data['tlusetime'] = 0;
                $data['uid'] = $uid;
                $data['status'] = UserTool::STATUS_CAN_USE;;
                $data['send'] = UserTool::SEND_ADMIN;
                $data['ref'] = $clmode;
                $data['source'] = $desc;
                for($i=0;$i<$num;$i++)
                {
                    $batch[] = $data;
                }
                $b = UserTool::instance()->addTools($batch);
                if($b)
                {
                    return array('code'=>Code::SUCCESS,'msg'=>'赠送成功','data'=>array());
                }else{
                    return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'赠送失败2','data'=>array());
                }
                break;
//            case GiftContentSetting::GIFT_TYPE_EXPRESSION_PACK://表情包
//                //#TODO 只操作缓存,不做返回值判断
//                $ret = service('Game')->getUserInterPropList($uid);
//                return array('code'=>Code::SUCCESS,'msg'=>'赠送表情包成功','data'=>array());
//                break;
            case GiftContentSetting::GIFT_TYPE_DIAMAND://钻石
                //#TODO
                $userInfo = UserGame::instance()->getOne($uid);
                if(!$userInfo)
                {
                    Logger::write('usergame用户信息未找到','FirstDailyLoginService insertGift','ERROR');
                    return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'获取失败','data'=>array());
                }
//                Model::instance('user')->start();
                try{
                    $u = UserGame::instance()->addDiamond($uid,$num);

                    $data['uid'] = $uid;
                    $data['clmode'] = $clmode;//MoneyLog::CLMODE_CROSS_GIFT;//首次登录赠送
                    $data['clflag'] = MoneyLog::PLUS;
                    $data['cldiamond'] = $num;
                    $data['clleftdiamond']=isset($userInfo['diamond'])?$userInfo['diamond']+$num:$num;
                    $data['clremark'] = $desc;
                    $data['cldesc'] = date('Y-m-d H:i:s',time())."{$desc}赠送{$num}钻石";
                    $t = DiamondLog::instance()->addLog($data);
                    if(!$u)
                    {
//                        Model::instance('user')->rollBack();
                        return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'增加钻石操作失败','data'=>array());
                    }
                    if($isSync)
                        $this->syncDataToServer($uid,$scene_id);
//                    Model::instance('user')->commit();
                    return array('code'=>Code::SUCCESS,'msg'=>'增加钻石成功','data'=>array());
                }catch (\Exception $e)
                {
//                    Model::instance('user')->rollBack();
                    return array('code'=>Code::OPERATEEXCEPTION,'msg'=>$e->getMessage(),'data'=>array());
                }
                ;break;
            case GiftContentSetting::GIFT_TYPE_ROOMCARD://房卡
                //#TODO
                $userInfo = UserGame::instance()->getOne($uid);
                if(!$userInfo)
                {
                    Logger::write('usergame用户信息未找到','FirstDailyLoginService insertGift','ERROR');
                    return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'获取失败','data'=>array());
                }
//                Model::instance('user')->start();
                try{

                    $u = UserGame::instance()->addRoomCard($uid,$num);

                    $data['uid'] = $uid;
                    $data['clmode'] = $clmode;//RoomCardLog::CLMODE_MONTH_ACCUM_GIFT;
                    $data['clflag'] = RoomCardLog::PLUS;
                    $data['clroomcard'] = $num;
                    $data['clleftroomcard']=isset($userInfo['roomcard'])?$userInfo['roomcard']+$num:$num;
                    $data['clremark'] = $desc;
                    $data['cldesc'] = date('Y-m-d H:i:s',time())."{$desc}赠送{$num}房卡";
                    $t = RoomCardLog::instance()->addLog($data);
                    if(!$u || !$t)
                    {
//                        Model::instance('user')->rollBack();
                        return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'增加房卡操作失败','data'=>array());
                    }
                    if($isSync)
                        $this->syncDataToServer($uid,$scene_id);
//                    Model::instance('user')->commit();
                    return array('code'=>Code::SUCCESS,'msg'=>'增加房卡成功','data'=>array('name'=>'房卡','num'=>$num*10));
                }catch (\Exception $e)
                {
//                    Model::instance('user')->rollBack();
                    return array('code'=>Code::OPERATEEXCEPTION,'msg'=>$e->getMessage(),'data'=>array());
                }
                break;
            case GiftContentSetting::GIFT_TYPE_JBCARD:
                //#TODO

                $data = array();
                $data['tlid'] = UserTool::TOOL_JB_CARD;
                $data['usetimes'] = 1;
                $data['tlusetime'] = 0;
                $data['uid'] = $uid;
                $data['status'] = UserTool::STATUS_CAN_USE;;
                $data['send'] = UserTool::SEND_ADMIN;
                $data['ref'] = UserTool::REF_ACTIVITY_TASK;
                $data['source'] = $desc;
                for($i=0;$i<$num;$i++)
                {
                    $batch[] = $data;
                }
                $b = UserTool::instance()->addTools($batch);
                if($b)
                {
                    return array('code'=>Code::SUCCESS,'msg'=>'赠送成功','data'=>array());
                }else{
                    Logger::write('禁比卡赠送失败');
                    return array('code'=>Code::OPERATEEXCEPTION,'msg'=>'赠送失败2','data'=>array());
                }
                break;
            default:
                return false;
        }
    }
    public  function syncDataToServer($uid,$scene_id=0)
    {
        return Curl::sendJsonToServer($uid,$scene_id);
    }

    public function rules()
    {
        return [
            ['uid','required'],
            ['gift_content_id','required'],
            ['num','required'],
            ['desc','required'],
            ['clmode','required'],

        ];
    }
    public function add2Redis($data)
    {
        if(! $this->validate($data,$this->rules()))
        {
            Logger::write('验证失败','类:'.__CLASS__.' 方法 '.__METHOD__,'error');
            return false;
        }
        $b = Redis::getIns()->rPush(Okey::rTurnGiftsUserList(),$data);
        if(!$b)
        {
            Logger::write("插入奖励配发队列失败",__METHOD__,"ERROR");
        }
        return $b;
    }
    public function reduceDiamond($uid,$num)
    {
        if($b = UserGame::instance()->reduceDiamond($uid,$num))
        {
            return true;
        }
        return false;
    }




} 