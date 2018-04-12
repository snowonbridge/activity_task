<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/4/3
 * Time: 17:36
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;

class VipChangeLog extends Model
{
    public $db = 'activity';
    public $table = 'vip_change_log';

    public function rule()
    {

        return [
            ['uid','required'],
            ['new_vip','required'],
            ['desc','required'],
        ];
    }

    /**
     * @desc
     * @param array $params
     * @return bool
     */
    public function add($params=array())
    {
        if(!$this->validate($params,$this->rules()))
        {
            Logger::write('数据验证失败',__METHOD__,"ERROR");
            return false;
        }
        $params['create_time'] = time();
//        $this->rmCache($params["uid"],$params['activity_id'],$params['add_time']);
        return  $this->insert($this->table,$params);
    }


}