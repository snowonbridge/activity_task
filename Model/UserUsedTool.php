<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-8-25
 * Time: 下午3:28
 */

namespace Workerman\Model;

use Workerman\Lib\Logger;

use Workerman\Lib\Model;
use Workerman\Lib\Okey;
use Workerman\Lib\Redis;
use Workerman\Service\ATCode;

class UserUsedTool extends Model
{
    public $db = 'user';
    //@var $table 用户道具表
    public $table = 'poker_userusedtool';


    public function getUsedToolNum($uid,$tool_id,$time)
    {
        if($tool_id == ATCode::GIFT_JBCARD)
            return 0;
       return  ATCode::getCache(Okey::ToolUsedNum($uid,$tool_id),function($uid,$tool_id,$time){
            $c = $this->count($this->table,"uid={$uid} and tlid={$tool_id} and expire>{$time}");
            return $c;
       },[$uid,$tool_id,$time],Okey::EX_ONE_HOUR,false);
    }


}