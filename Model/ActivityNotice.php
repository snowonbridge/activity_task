<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-18
 * Time: 下午12:17
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Lib\Okey;
use Workerman\Service\ATCode;

class ActivityNotice extends Model{
    public $db = 'activity';
    public $table = 'activity_notice';

    public function getAll($sid,$version='1.0.0')
    {
        if(!$sid)
        {
            $sid = 10002;
        }
        $result = ATCode::getCache(Okey::getNoticeList($sid,$version),function($sid,$version){
            $result = $this->getRows($this->table,"`version` in ('{$version}','0') and sid like :sid",[':sid'=>"%$sid%"]);
            return $result?$result:false;
        },[$sid,$version]);

        return $result;
    }

    /**
     * @desc 缓存清除
     * @return int
     */
    public function rmCache($sid,$version)
    {
        return ATCode::rmCache(Okey::getNoticeList($sid,$version));
    }
} 