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

class NewhandSetting extends Model
{
    public $db = 'activity';
    public $table = 'newhand_setting';


    //platform_id` varchar(11) NOT NULL DEFAULT '' COMMENT '开放系统:1@android,2@IOS',
    const PLATFORM_ID_IOS=2;
    const PLATFORM_ID_ANDROID=1;

    /**
     * @desc gift_content 格式[{time_nd:1,list:[{id:1,name:'',num:12,}...] },..{}]
     * @return array|bool
     */
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
                $item['gift_content'] = json_decode($item['gift_content'],true);
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
        return ATCode::rmCache(Okey::getNewhandSetting());
    }


}