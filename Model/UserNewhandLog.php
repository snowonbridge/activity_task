<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/3/27
 * Time: 17:15
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;

class UserNewhandLog extends Model
{
    public $db = 'activity';
    public $table = 'user_newhand_log';

    public function rule()
    {

        return [
            ['uid','required'],
            ['times','required'],
            ['close_times','required'],
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
        $params['day_time'] = date("Ymd");

        $params['create_time'] = time();
//        $this->rmCache($params["uid"],$params['activity_id'],$params['add_time']);
        return  $this->insert($this->table,$params);
    }
    public function updateStatus($uid,$data)
    {
        $today = date("Ymd");
        Logger::write(json_encode(func_get_args()),__METHOD__,"INFO");
        return $this->update($this->table,$data,"uid=:uid and day_time=:day_time",[':day_time'=>$today,':uid'=>$uid]);
    }

    public function getOne($uid)
    {
        $r = $this->getRow($this->table,"uid=$uid and times !=0 order by  create_time desc");
        return $r;
    }

    /**
     * @desc 获取当天记录
     * @param $uid
     * @return mixed
     */
    public function getTodayOne($uid)
    {
        $today = date("Ymd");
        $r = $this->getRow($this->table,"uid=$uid and day_time=$today order by  create_time desc");
        return $r;
    }

}