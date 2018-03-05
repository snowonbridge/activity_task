<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-8
 * Time: 下午8:07
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;

class OnlineConfig extends Model{
    public $db = 'activity';
    public $table = 'online_config';
    const USER_LEVEL_A1 = 1;//
    const USER_LEVEL_B1 = 2;//
    const USER_LEVEL_A2 = 3;//
    const USER_LEVEL_B2 = 4;//
    const USER_LEVEL_ALL=0;//无限制
    public function rule()
    {
        return [
            ['title','required'],
            ['online_time','required'],//次数
            ['user_level','required'],//
        ];
    }
    public function add($params=array())
    {
        if(!$this->validate($params,$this->rules()))
        {
            return false;
        }
        return  $this->insert($this->table,$params);
    }
    public function getAll()
    {
        return $this->getRows($this->table);
    }
    public function getOne($id)
    {
        return $this->getRow($this->table,'id=:id',[':id'=>$id]);
    }

    /**
     * @desc 验证条件,不用验证局数
     * @param $id
     * @param $game_no
     * @param $friend_relation
     * @return bool
     */
    public   function validOnlineTime($counts,$r)
    {
        if(!$r)
            return false;
        if($counts >= $r['online_time'])
            return true;
        Logger::write('不满足该条件',__METHOD__);
        return false;
    }
    public  function validUserLevel($level,$r='')
    {
        if(!$r)
            return false;
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
        if($this->validOnlineTime($params['online_time'],$r)
        && $this->validUserLevel($params['user_level'],$r))
            return true;

        return false;
    }
    /**
     * @desc 一个频率内的获取奖励允许次数
     * @param $id
     * @return int
     */
    public function getCounts($id)
    {

        return 1;
    }
} 