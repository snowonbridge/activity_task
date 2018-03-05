<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-11-4
 * Time: 上午11:33
 */

namespace Workerman\Model;


use Workerman\Lib\Model;
use Workerman\Lib\Okey;
use Workerman\Lib\Redis;

class UserMap extends Model{

    public $db = 'user';
    public $table = 'poker_user_map';

    public  function getMap($uid)
    {
        return $this->getRow($this->table,"uid=:uid",[":uid"=>$uid],['mid','sid']);
    }

} 