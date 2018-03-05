<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2017/12/11
 * Time: 17:45
 */

namespace Workerman\Model;


use Workerman\Lib\Model;
use Workerman\Lib\Logger;
use Workerman\Lib\Okey;
use Workerman\Lib\Redis;

class FirstChargeActivity extends Model{

    public $db = 'user';
    public $table = 'poker_activity';
    //购买类型@0不可购买,1不可重复购买,2可重复购买',
    const TYPE_FORBIDDEN=0;
    const TYPE_NOREPEAT=1;
    const TYPE_REPEAT=2;
    public function __construct($db='')
    {
        parent::__construct($db);

    }




    public function getOne($id)
    {
        $result =  $this->getRow($this->table,'id=:id',[':id'=>$id]);
        return $result;
    }
}