<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-10-13
 * Time: 下午6:04
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Lib\Okey;
use Workerman\Service\ATCode;

class TurnLotterySetting extends Model{

    public $db = 'activity';
    public $table = 'turn_lottery_setting';


    public function getOne($id)
    {
        $list =  ATCode::getCache(Okey::rTurnLotterySetting(),function(){
            $result = $this->getRows($this->table);
            return $result?$result:false;
        });
        foreach($list as $k=>$v)
        {
            if($v['id'] == $id)
                return $v;
        }
        return false;
    }

    public function getCount($id)
    {
        $r = $this->getOne($id);
        if(!$r)
        {
            Logger::write("无该兑换抽奖配置id",__METHOD__,"ERROR");
        }
        return $r['counts'];
    }

} 