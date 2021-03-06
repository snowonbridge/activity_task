<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-10-13
 * Time: 下午6:04
 */

namespace Workerman\Model;


use Workerman\Lib\Model;
use Workerman\Lib\Okey;
use Workerman\Service\ATCode;

class TurnGiftSetting extends Model{

    public $db = 'activity';
    public $table = 'turn_gift_setting';


    public function getOne($id)
    {
        $list = $this->getAll();
        foreach($list as $k=>$item)
        {
            if($item['id'] == $id)
            {
                return $item;
            }
        }
        return false;
    }
    public function getAll()
    {
       $list = ATCode::getCache(Okey::rTurnGiftSetting(),function(){
           $result = $this->getRows($this->table,"1 order by sort asc",[]);
           return $result?$result:false;
        });
        $i = 1;
        foreach($list as $k=>$item)
        {
            $result[$k+1] = $item;
            $result[$k+1]['id'] = $i++;
        }
        return $result;
    }
} 