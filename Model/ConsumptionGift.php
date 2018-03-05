<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-8
 * Time: 下午5:30
 */

namespace Workerman\Model;


use Workerman\Lib\Model;
use Workerman\Lib\Okey;
use Workerman\Service\ATCode;

class ConsumptionGift extends Model{

    public $db = 'activity';
    public $table = 'consumption_gift';

    public function rule()
    {
        return [
            ['uid','required'],
            ['activity_id','required'],
            ['actions_list','required'],//id-id,id格式。-表示and,','表示或关系
            ['login_check','required'],
            ['magic_expression','required'],
            ['gift_list','required'],
            ['desc','required'],
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

    public function getOne($activity_id)
    {
        $result = ATCode::getCache(Okey::ConsumptionGiftByActivityId($activity_id),function($activity_id){
            $all = ATCode::getCache(Okey::ConsumptionGift(),function(){
                $result = $this->getRows($this->table);
                return $result?$result:false;
            });
            $r = array_filter($all,function($v)use($activity_id){
                if($v['activity_id'] == $activity_id)
                    return true;
                else
                    false;
            });
            return $r?array_values($r)[0]:false;
        },[$activity_id]);
        return $result;
    }

    public function hasActivity($activity_id)
    {
        $r = $this->getOne($activity_id);

        return !empty($r)?1:0;
    }

    public function getListByTabId($tab_id,$start_time='',$end_time='')
    {
        $now = time();
        $result = ATCode::getCache(Okey::ConsumptionGiftByTabId($tab_id),function($tab_id,$start_time,$end_time){
            $start_time = $start_time?$start_time:mktime(0,0,0,date("m"),0,date("Y"));
            $end_time = $end_time?$end_time:mktime(59,59,59,date("m"),date("t"),date("Y"));
            $all = ATCode::getCache(Okey::ConsumptionGift(),function(){
                $result = $this->getRows($this->table,'1 order by sort asc');
                return $result?$result:false;
            });
            if(!$all)
                return false;

            $r = array_filter($all,function($v)use($tab_id,$end_time){
                if($v['tab_id'] == $tab_id )
                    return true;
                else
                    false;
            });
            return array_values($r);
        },[$tab_id,$start_time,$end_time]);
        foreach($result as $k=>$v)
        {
            if( ($v['start_time'] <= $now) &&($now <= $v['end_time'] ))
            {
                $resultList[] = $v;
            }
        }
        return isset($resultList)?$resultList:[];

    }

    /**
     * @desc 是否是月活动
     * @param $activity_id
     */
    public function isMonthTask($activity_id)
    {
        $r = $this->getOne($activity_id);
        return (int)$r['tab_id'];
    }

    public function validConfig($activity_id,$params)
    {
        $r = $this->getOne($activity_id);
        //验证有效期
        if($r['start_time'] && $r['start_time']<=$params['add_time'])
        {
            return true;
        }
        if($r['end_time'] && $r['end_time']>=$params['add_time'])
        {
            return true;
        }
        return false;
    }

    /**
     * @desc 获取下一个任务id，如果已经是最后一个任务，返回false
     * @param $activity_id
     */
    public function getNextActivityId($activity_id)
    {
        $r = $this->getOne($activity_id);
        if($r['base_activity_id'] == 0)
            return false;
        else{
            return $r['base_activity_id'];
        }
    }

    public function isRechargeActivity($activity_id)
    {
        $r = $this->getOne($activity_id);
        return $r['tab_id'] == ATCode::TAB_EXCHANGE?1:0;
    }
    public function getBaseActivityIds($activity_ids=array())
    {
        $result = ATCode::getCache(Okey::ConsumptionBaseIds(),function($activity_ids){
            $all = ATCode::getCache(Okey::ConsumptionGift(),function(){
                $result = $this->getRows($this->table);
                return $result?$result:false;
            });
            $baseIds=[];
            foreach ($all as $k=>$v) {
                if(in_array($v['activity_id'],$activity_ids))
                {
                    $baseIds[$v['activity_id']] = $v['base_activity_id'];
                }
            }
            return $baseIds;
        },[$activity_ids]);
        return $result;
    }
} 