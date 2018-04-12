<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/3/28
 * Time: 10:51
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;

class UserFirstchargeLog extends Model
{
    public $db = 'activity';
    public $table = 'user_firstcharge_log';

    public function rules()
    {
        return [
            ['uid','required'],
            ['firstcharge_id','required'],

        ];
    }

    /**
     * @desc 添加首充日志
     * @param $uid
     * @return bool|string
     */
    public function add($data)
    {
        if(!$this->validate($data,$this->rules()))
        {
            Logger::write('数据验证失败',__METHOD__,"ERROR");
            return false;
        }
        $data['create_time'] = time();
        $b = $this->insert($this->table,$data);
        return $b;
    }

    public function getOne($uid)
    {
        $r = $this->getRow($this->table,"uid=$uid");
        return $r;
    }

    /**
     * @desc 是否已购买过首充
     * @param $uid
     * @return bool
     */
    public function has($uid)
    {
        $r = $this->getOne($uid);
        return $r?true:false;
    }
}