<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-8
 * Time: 下午4:59
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Lib\Okey;
use Workerman\Service\ATCode;


class ConsumptionConfig extends Model{

    public $db = 'activity';
    public $table = 'consumption_config';
    const LOGIN_CHECK_Y = 1;
    const LOGIN_CHECK_N = 0;
    const LOGIN_CHECK_IGNORE = 2;
//    const WIN_RESULT_Y = 1;
//    const WIN_RESULT_N = 0;
    const USER_LEVEL_A1 = 1;//
    const USER_LEVEL_B1 = 2;//
    const USER_LEVEL_A2 = 3;//
    const USER_LEVEL_B2 = 4;//
    const USER_LEVEL_ALL=0;//无限制

    const MAGIC_Y = 1;//魔法道具使用
    const MAGIC_N = 0;
    const MAGIC_IGNORE = 2;

    /**
     * 动作id配置
     */
    const ACTION_GT = 1;
    const ACTION_LT = 2;
    const ACTION_PURCHASE = 3;
    const ACTION_EXCHANGE = 4;
    const ACTION_USE = 5;
    public function rule()
    {
        return [
            ['from_gift_id','required'],//1-12-32&2-11-30,2-1-*,*,(*表示任意 ，第一个数字游戏 第二个玩法 第三个经典场)
            ['action_id','required'],//局数
            ['from_num','required'],
            ['target_gift_id','required'],//好友前置条件@0无,1与两个好友一起玩,2自己开房,3不要自己开房

            ['magic','required'],
            ['frequency','required'],
            ['login_check','required'],
            ['remark','required'],
        ];
    }
    public function add($params=array())
    {
        if(!$this->validate($params,$this->rules()))
        {
            return false;
        }
        $params['create_time'] = time();
        return  $this->insert($this->table,$params);
    }

    public function getOne($id)
    {

        return ATCode::getCache(Okey::ConsumptionConfig($id),function($id){
            $result =  $this->getRow($this->table,'id=:id',[':id'=>$id]);
            return $result?$result:false;
        },[$id]);

    }

    /**
     * @desc 缓存清除
     * @return int
     */
    public function rmCacheById($id)
    {
        return  ATCode::rmCache(Okey::ConsumptionConfig($id));
    }

    /**
     * @desc 验证条件,不用验证局数
     * @param $id
     * @param $game_no
     * @param $friend_relation
     * @return bool
     */
    public   function validlogining($login_check,$r)
    {
        if(!$r)
            return false;
        if(in_array($r['login_check'],[self::LOGIN_CHECK_N,self::LOGIN_CHECK_IGNORE]))
            return true;
        if( $login_check == $r['login_check'])
            return true;
        Logger::write('不满足该条件',__METHOD__);
        return false;
    }

    /**
     * @desc 验证动作条件
     * @param $from_gift_id
     * @param $action_id
     * @param $from_num
     * @param $r
     * @return bool
     */
    public function validActionNum($params,$r)
    {
        if(!$r)
            return false;
        $from_gift_id = $params['from_gift_id'];
        $action_id = $params['action_id'];
        $from_num = $params['from_num'];

        if($r['from_gift_id'] == $from_gift_id && $action_id == $r['action_id'])
        {

            switch($action_id)
            {
                case self::ACTION_GT:
                    if($from_num >= $r['from_num'])
                        return true;
                case self::ACTION_LT:
                    if($from_num < $r['from_num'])
                        return true;
                case self::ACTION_PURCHASE:
                    if($from_num >= $r['from_num'])
                        return true;
                case self::ACTION_EXCHANGE:
                    if($from_num >= $r['from_num'] && $params['target_gift_id']==$r['target_gift_id'])
                        return true;
                case self::ACTION_USE:
                    if($from_num >= $r['from_num'])
                        return true;
                default:
                    return false;
            }
        }
        Logger::write('不满足该条件',__METHOD__);
        return false;
    }

    /**
     * @desc 验证是否使用了魔法道具
     * @param $num
     * @param int $id
     * @param string $r
     * @return bool
     */
    public  function validUserMagic($magic,$r='')
    {
        if(!$r)
            return false;
        if($r['magic'] == self::MAGIC_IGNORE || $r['magic'] == self::MAGIC_N)
            return true;
        if($r['magic'] == $magic)
            return true;

        return false;

    }
    public  function validUserLevel($level,$r='')
    {
        if(!$r)
        {
            Logger::write('没有相关配置记录',__METHOD__);
            return false;
        }

        if($r['user_level'] == self::USER_LEVEL_ALL)
            return true;
        if($r['user_level'] == $level)
            return true;
        Logger::write('不满足该条件',__METHOD__);
        return false;

    }


    /**
     * @param int $params:from_gift_id
     * @param int $params:action_id
     * @param int $params:from_num
     * @param int $params:user_level
     * @param int $params:magic
     * @param int $params:login_check
     * @return bool
     */
    public function validConfig($id,$params)
    {

        $r = $this->getOne($id);
        if($this->validActionNum($params,$r)
            && $this->validlogining($params['login_check'],$r)
            && $this->validUserMagic($params['magic'],$r))
            return true;

        return false;
    }

    /**
     * @desc 当前设置为1，
     * @param $id
     * @return int
     */
    public function getCounts($id)
    {
        return 1;
    }

} 