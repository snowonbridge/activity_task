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
use Workerman\Lib\Okey;
use Workerman\Service\ATCode;

class FunctionConfig extends Model{
    public $db = 'activity';
    public $table = 'function_config';

    public function rule()
    {
        return [
            ['title','required'],
//            ['counts','required'],//次数
            ['condition','required'],//
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
        return ATCode::getCache(Okey::FunctionConfig($id),function($id){
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
        return  ATCode::rmCache(Okey::FunctionConfig($id));
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
        if($counts < $r['counts'])
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
        if($this->validCounts($params['counts'],$r))
            return true;

        return false;
    }
    /**
     * @desc 功能次数
     * @param $id
     * @return int
     */
    public function getCounts($id)
    {
        $r = $this->getOne($id);
        return isset($r['counts'])? intval($r['counts']):0;
    }
} 