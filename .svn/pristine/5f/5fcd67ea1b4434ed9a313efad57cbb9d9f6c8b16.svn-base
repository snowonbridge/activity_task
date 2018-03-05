<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-10-13
 * Time: 下午6:04
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Lib\Okey;
use Workerman\Lib\Redis;
use Workerman\Service\ATCode;

class TurnUserLuckyValue extends Model{

    public $db = 'activity';
    public $table = 'turn_user_lucky_value';


    public function getOne($id)
    {
        return $this->getRow($this->table,"id=:id",[':id'=>$id]);
    }
    public function getValueByUid($uid,$gift_setting_id=0)
    {
       return  ATCode::getCache(Okey::rUserLuckyValue($uid,$gift_setting_id),function($uid,$gift_setting_id){
            $r = $this->getRow($this->table,"uid=:uid and gift_setting_id=:gift_setting_id",[":uid"=>$uid,":gift_setting_id"=>$gift_setting_id]);
            return isset($r['value'])?$r['value']:false;;
        },[$uid,$gift_setting_id]);

    }
    public function rules()
    {
        return [
            ['uid','required'],
            ['gift_setting_id','required'],
            ['value','required'],
        ];
    }

    /**
     * @desc 添加签到日志
     * @param $uid
     * @return bool|string
     */
    public function add($data)
    {
        if(! $this->validate($data,$this->rules()))
        {
            Logger::write('验证失败','类:'.__CLASS__.' 方法 '.__METHOD__,'error');
            return false;
        }
        $data['update_time'] = time();
        $b = $this->insert($this->table,$data);

        return $b;
    }

    /**
     * @desc 更新用户幸运值
     * @param $uid
     * @param int $gift_setting_id
     * @return bool
     */
    public function updateValue($uid,$gift_setting_id=0)
    {
        if(!$uid || !$gift_setting_id)
        {
            Logger::write('参数错误','类:'.__CLASS__.' 方法 '.__METHOD__,'ERROR');
            return false;
        }
        $value = $this->getValueByUid($uid,$gift_setting_id);
        if(false !== $value)
        {

            $newNum = $value +$this->getSettingValues($gift_setting_id);
            if($newNum > 100)
            {
                $newNum = $newNum %100;
            }
            Redis::getIns()->set(Okey::rUserLuckyValue($uid,$gift_setting_id),$newNum);

            $b = $this->update($this->table,["value"=>$newNum,'update_time'=>time()],"uid=:uid and gift_setting_id=:gift_setting_id",[":uid"=>$uid,":gift_setting_id"=>$gift_setting_id]);
            if(false === $b)
            {
                Logger::write('更新幸运值失败','类:'.__CLASS__.' 方法 '.__METHOD__,'ERROR');
                return false;
            }

        }else{

            $data["uid"] = $uid;
            $data["gift_setting_id"] = $gift_setting_id;
            $data["value"] = $this->getSettingValues($gift_setting_id);
            Redis::getIns()->set(Okey::rUserLuckyValue($uid,$gift_setting_id),$this->getSettingValues($gift_setting_id));
            if($this->add($data) ===false)
            {
                Logger::write('增加活跃值失败','类:'.__CLASS__.' 方法 '.__METHOD__,'ERROR');
                return false;
            }
        }
        return true;
    }

    public function getSettingValues($gift_setting_id)
    {
        $r = TurnGiftSetting::instance()->getOne($gift_setting_id);
        if($r)
        {
            return $r['lucky_value'];
        }else{
            return 1;
        }
    }
    public function clearValue($uid,$gift_setting_id)
    {
        Redis::getIns()->set(Okey::rUserLuckyValue($uid,$gift_setting_id),0);
        $value = $this->getRow($this->table,"uid=:uid and gift_setting_id=:gift_setting_id",[":uid"=>$uid,":gift_setting_id"=>$gift_setting_id]);;
        if(false !== $value)
        {
            $b = $this->update($this->table,["value"=>0,'update_time'=>time()],"uid=:uid and gift_setting_id=:gift_setting_id",[":uid"=>$uid,":gift_setting_id"=>$gift_setting_id]);
            if(false === $b)
            {
                Logger::write('幸运值失清零败','类:'.__CLASS__.' 方法 '.__METHOD__,'ERROR');
                return false;
            }
        }else{
            $data["uid"] = $uid;
            $data["gift_setting_id"] = $gift_setting_id;
            $data["value"] = 0;
            if($this->add($data) ===false)
            {
                Logger::write('增加活跃值失败','类:'.__CLASS__.' 方法 '.__METHOD__,'ERROR');
                return false;
            }
        }


        return true;

    }

    /**
     * @desc 判断是否为当天第一次转
     * @param $uid
     * @return bool
     */
    public function isNotFirstTurn($uid,$status=false)
    {
        $r = true;
        if($status){
            $r = Redis::getIns()->getSet(Okey::isFirstTurn($uid,date("Ymd")),1);
            Redis::getIns()->setTimeout(Okey::isFirstTurn($uid,date("Ymd")),Okey::EX_ONE_DAY);
        }
        return !$r;
    }

    /**
     * @desc 获取该用户在所有奖品上的幸运值
     * @param $uid
     * @return bool
     */
    public function getGiftsLuckyValues($uid)
    {
        $gift_setting_id=1;
        $result=[];
        while( $gift_setting_id <= 12)
        {
            $value = ATCode::getCache(Okey::rUserLuckyValue($uid,$gift_setting_id),function($uid,$gift_setting_id){
                $r = $this->getRow($this->table,"uid=:uid and gift_setting_id=:gift_setting_id",[":uid"=>$uid,":gift_setting_id"=>$gift_setting_id],['gift_setting_id','value']);
                return isset($r['value'])?$r['value']:false;
            },[$uid,$gift_setting_id]);
            $result[$gift_setting_id] = $value === false?0:$value;
            $gift_setting_id++;
        }

        return $result;
    }

} 