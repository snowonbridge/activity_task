<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/3/13
 * Time: 18:09
 */

namespace Workerman\Model;


use Workerman\Lib\Model;

class UserGift extends Model{

    public $db = 'user';
    public $table = 'poker_user_gift';


    /**
     *  礼物货币购买类型:1@金币,2@钻石,3@房卡
     */
    const M_TYPE_GOLDEN = 1;
    const M_TYPE_DIAMOND = 2;
    const M_TYPE_ROOMCARD = 3;


    public function getOneById($id)
    {
        return  $this->getRow($this->table,"id=$id");
    }
}