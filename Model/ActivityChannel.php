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

class ActivityChannel extends Model{

    public $db = 'activity';
    public $table = 'activity_channel';

    const STATUS_ON=1;
    const STATUS_OFF=0;
    public function getAll()
    {
        $result = ATCode::getCache(Okey::getChannelList(),function(){
            $result = $this->getRows($this->table);
            return $result?$result:false;
        });

        return $result;
    }
    public function isActive($channel_id)
    {
        $all = $this->getAll();
        foreach($all as $item)
        {
            if($item['channel_id'] == $channel_id)
            {
                if(!$item['status'])
                {
                    Logger::write("activity_control_id $channel_id 被设置为隐藏状态",__METHOD__,"WARN");
                }
                return $item['status'] == self::STATUS_ON?true:false;
            }
        }
        Logger::write("activity_control_id $channel_id 不存在,在activity_channel表找不到",__METHOD__,"ERROR");
        return false;
    }
}