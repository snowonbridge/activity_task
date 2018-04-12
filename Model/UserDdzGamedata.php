<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/4/9
 * Time: 17:05
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;

class UserDdzGamedata extends Model
{
    public $db = 'user';
    public $table = 'poker_user_ddz_gamedata';

    public function getPlayCounts($uid)
    {
        if(!$uid)
        {
            Logger::write("用户参数不存在",__METHOD__,"ERROR");
            return 0;
        }
        $row = $this->getRow($this->table,"uid=$uid");
        if(!$row)
        {
            Logger::write("用户没有玩牌记录",__METHOD__,"INFO");
            return 0;
        }
        $count = $row['win']+$row['lose']+$row['draw'];
        return $count;
    }

}