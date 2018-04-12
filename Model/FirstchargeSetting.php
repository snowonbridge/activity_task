<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/3/28
 * Time: 11:03
 */

namespace Workerman\Model;


use Workerman\Lib\Model;

class FirstchargeSetting extends Model
{
    public $db = 'activity';
    public $table = 'firstcharge_setting';






    public function getOne($channel_id,$platform_id)
    {
        $result =  $this->getRow($this->table,"channel_id=:channel_id and  FIND_IN_SET('{$platform_id}',`platform_id`) and status=1",[':channel_id'=>$channel_id]);
        if($result)
        {
            $result['base_gift_content'] = @json_decode($result['base_gift_content'],true);
            $result['extra_gift_content'] = @json_decode($result['extra_gift_content'],true);
        }
        $result['id'] = (int)$result['id'];
        $result['channel_id'] = (int)$result['channel_id'];
        $result['platform_id'] = (int)$result['platform_id'];
        $result['goods_id'] = (int)$result['goods_id'];
        $result['money'] = (float)$result['money'];
        $result['total_value'] = (float)$result['total_value'];
        unset($result['status'],$result['create_time']);
        return $result;
    }
    public function getById($id)
    {
        $r = $this->getRow($this->table,"id=:id",[':id'=>$id]);
        return $r;
    }
}