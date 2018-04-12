<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/3/27
 * Time: 10:03
 */

namespace Workerman\Model;


use Workerman\Lib\Model;
use Workerman\Lib\Okey;
use Workerman\Service\ATCode;

class CheckinSetting extends Model
{
    public $db = 'activity';
    public $table = 'checkin_setting';

    //连续规则:1@连续,2@非连续
    const RULE_ID_CONTINUE_Y=1;
    const RULE_ID_CONTINUE_N=2;
    public function getAll()
    {
//        $result = ATCode::getCache(Okey::getCheckinSetting(),function(){
//            $result =  $this->getRows($this->table);
//            return $result?$result:false;
//        });
        $result =  $this->getRows($this->table);
        if(!$result)
            return array();
        foreach($result as $k=>$v)
        {
            $r[$v['id']] = $v;
        }
        return $r;
    }

    public function getOne($channel_id,$register_way_id,$platform_id=0)
    {
        $all = $this->getAll();
        foreach ($all as $k=>$item)
        {
            if($channel_id == $item['channel_id'] && ( false !== strpos($item['register_way_id'],(string)$register_way_id) ) && (false!==strpos($item['platform_id'],(string)$platform_id )) )
            {
                return $item;
            }
        }
        return [];
    }
    /**
     * @desc 缓存清除
     * @return int
     */
    public function rmCache()
    {
        return ATCode::rmCache(Okey::getCheckinSetting());
    }


}