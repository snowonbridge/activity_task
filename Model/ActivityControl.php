<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2017/12/21
 * Time: 15:36
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Lib\Okey;
use Workerman\Service\ATCode;

class ActivityControl extends Model{

    public $db = 'activity';
    public $table = 'activity_control';

    const STATUS_ON=1;
    const STATUS_OFF=0;
    public function getAll()
    {
        $result = ATCode::getCache(Okey::getControlList(),function(){
            $result = $this->getRows($this->table);
            return $result?$result:false;
        });

        return $result;
    }

    public function isActive($id)
    {
        $all  = $this->getAll();
        foreach ($all as $item) {
            if($item['id'] == $id )
            {
                if(!$item['status'])
                {


                    Logger::write("activity type id $id 被设置为隐藏状态".var_export($item,true),__METHOD__,"WARN");
                }
                return $item['status'] == self::STATUS_ON?true:false;

            }
        }
        Logger::write("activity type id $id 不存在","ERROR");
        return false;

    }
}