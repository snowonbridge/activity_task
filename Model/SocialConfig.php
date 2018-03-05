<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-9
 * Time: 上午9:42
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Lib\Okey;
use Workerman\Service\ATCode;

class SocialConfig extends Model{

    public $db = 'activity';
    public $table = 'social_config';


    const IS_FRIEND_Y = 1;//是否需要好友@1y,0n,2忽略
    const IS_FRIEND_N = 0;
    const IS_FRIEND_IGNORE = 2;

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
            ['title','required'],//1-12-32&2-11-30,2-1-*,*,(*表示任意 ，第一个数字游戏 第二个玩法 第三个经典场)
            ['counts','required'],//局数
            ['is_friend','required'],
//            ['condition','required'],

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

    public function getOne($id)
    {
        $result = $this->getAll();
        $r = array_filter($result,function($v)use($id){
            if($v['id'] == $id)
                return true;
            else
                false;
        });
        return array_values($r)[0];
    }
    public function getAll()
    {
        $result = ATCode::getCache(Okey::SocialConfig(),function(){
            $result =  $this->getRows($this->table);
            return $result?$result:false;
        });
        return $result;
    }
    /**
     * @desc 缓存清除
     * @return int
     */
    public function rmCache()
    {
        return  ATCode::rmCache(Okey::SocialConfig());
    }
    /**
     * @desc 验证条件,不用验证局数
     * @param $id
     * @param $game_no
     * @param $friend_relation
     * @return bool
     */
    public   function validCounts($counts,$r)
    {
        if(!$r)
            return false;
        if( $counts < $r['counts'])
            return true;
        Logger::write('不满足该条件',__METHOD__);
        return false;
    }

    public  function validFriend($friend_relation,$r='')
    {
        if(!$r)
            return false;
        if($r['is_friend'] == self::IS_FRIEND_N)
            return true;
        if($r['is_friend'] == $friend_relation)
            return true;
        Logger::write('不满足该条件',__METHOD__);
        return false;

    }


    /**
     * @param int $params:counts
     * @param int $params:friend_relation
     * @return bool
     */
    public function validConfig($id,$params)
    {
        $r = $this->getOne($id);
        if($this->validCounts($params['counts'],$r)
            && $this->validFriend($params['friend_relation'],$r)
        )
            return true;

        return false;
    }
    /**
     * @desc 邀请好友次数，被申请加好友的次数
     * @param $id
     * @return int
     */
    public function getCounts($id)
    {
        $r = $this->getOne($id);
        return intval($r['counts']);
    }
} 