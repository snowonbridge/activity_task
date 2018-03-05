<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-7
 * Time: 下午4:14
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Lib\Okey;
use Workerman\Lib\Redis;
use Workerman\Service\ATCode;

class ActivityCategory extends Model{
    public $db = 'activity';
    public $table = 'activity_category';

    const CATE_EVERYDAY = 19;
    const CATE_TEN_MONTH = 32;
    const STATUS_ON=1;
    const STATUS_OFF=2;
    public function rule()
    {
        return [
            ['cate_name','required'],//1-12-32&2-11-30,2-1-*,*,(*表示任意 ，第一个数字游戏 第二个玩法 第三个经典场)
            ['cate_desc','required'],//局数
            ['status','required'],
            ['activity_id','required'],//好友前置条件@0无,1与两个好友一起玩,2自己开房,3不要自己开房
            ['parent_id','required'],
        ];
    }
    public function add($params=array())
    {
        if(!$this->validate($params,$this->rules()))
        {
            return false;
        }
        ATCode::rmCache(Okey::ActivityList());
        return  $this->insert($this->table,$params);
    }

    public function getOne($id)
    {


        $item = ATCode::getCache(Okey::ActivityCatById($id),function($id){
            $result = $this->getAll();
            $r = array_filter($result,function($v)use($id){
                if($v['id'] == $id)
                {

                    return true;
                }
                else
                    false;
            });
            $item = array_values($r)[0];
            return $item;
        },[$id]);

        return $item;
    }

    /**
     * @desc 获取每日任务列表
     * @return bool
     */
    public function getEveryDayActivityList()
    {

        $result = $this->getAll();
        $result = array_filter($result,function($v){
            if($v['parent_id'] == self::CATE_EVERYDAY && $v['status'] == self::STATUS_ON)
                return true;
            else
                false;
        });
        return array_values($result);
    }
    /**
     * @desc 获取十月活动列表
     * @return bool
     */
    public function getMonthActivityList()
    {
        $result = $this->getAll();
        $result = array_filter($result,function($v){
            if($v['parent_id'] == self::CATE_TEN_MONTH  && $v['status'] == self::STATUS_ON)
                return true;
            else
                false;
        });
        if(!$result)
            return array();
        foreach($result as $k=>&$v)
        {
            unset($v['status'],$v['channel_id_str'],$v['sort_value']);
            $r[$v['activity_id']] = $v;
        }
        return $r;

    }
    public function getActivityType($activity_id)
    {
        $result = ATCode::getCache(Okey::ActivityCatByActivityId($activity_id),function($activity_id){
            $result = $this->getAll();
            $r = array_filter($result,function($v)use($activity_id){
                if($v['activity_id'] == $activity_id)
                    return true;
                else
                    false;
            });
            $r = array_values($r)[0];
            return $r;
        },[$activity_id]);
        return isset($result['activity_type'])?$result['activity_type']:0;
    }
    public function getAll()
    {
        $result = ATCode::getCache(Okey::ActivityList(),function(){
            $result =  $this->getRows($this->table,'1 order by sort_value desc');
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
        return  ATCode::rmCache(Okey::ActivityList());
    }

    public function isActive($activity_id)
    {
        $result = ATCode::getCache(Okey::ActivityCatByActivityId($activity_id),function($activity_id){
            $r = $this->getAll();
            foreach($r as $k=>$v)
            {
                if($v['activity_id'] == $activity_id)
                {
                    return  $v;
                }
            }
            return false;
        },[$activity_id]);

        if($result && $result['status'] == self::STATUS_ON && $result['activity_id'] == $activity_id)
        {
            return true;
        }else{
            return false;
        }
    }

    /**
     *玩游戏类活动
     */
    public function isCrossActivity($activity_id)
    {
        $result = ATCode::getCache(Okey::ActivityCatByActivityId($activity_id),function($activity_id){
            $r = $this->getAll();
            foreach($r as $k=>$v)
            {
                if($v['tab_id'] == ATCode::TAB_PLAY_AND_MAKE_MONEY && $v['activity_id'] == $activity_id)
                {
                   return $v;
                }
            }
            return false;
        },[$activity_id]);
        if($result && $result['tab_id'] == ATCode::TAB_PLAY_AND_MAKE_MONEY && $result['activity_id'] == $activity_id)
        {
            return true;
        }else{
            return false;
        }
    }


} 