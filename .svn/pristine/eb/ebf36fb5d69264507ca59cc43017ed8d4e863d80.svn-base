<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2017/12/28
 * Time: 18:57
 */

namespace Workerman\Model;


use Workerman\Lib\Model;
use Workerman\Lib\Okey;
use Workerman\Service\ATCode;

class FaControlStoreSetting extends Model{
//fa_control_store_setting
    public $db = 'cms';
    public $table = 'fa_control_store_setting';


    /**
     * 获取渠道控制设置
     * @param $app_id
     * @return array|int|mixed
     */
    public function getControlStoreSetting($app_id){
        if(empty($app_id)) return 0;
        $result = ATCode::getCache(Okey::rControlStoreSetting($app_id),function($app_id){
            $list = $this->getRows($this->table,"status=1 AND app_id=:app_id",[":app_id"=>$app_id]);
            return $list?json_encode($list,JSON_UNESCAPED_UNICODE):false;
        },[$app_id],Okey::EX_ONE_DAY,false); //从缓存中取信息--后期必要时改成hash
      return json_decode($result,true);
    }
}