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
use Workerman\Lib\Redis;

class TurnUserLog extends Model{

    public $db = 'activity';
    public $table = 'turn_user_log';


    public function getOne($id)
    {
        return $this->getRow($this->table,"id=:id",[':id'=>$id]);
    }

    public function rules()
    {
        return [
            ['uid','required'],
            ['exchange_id','required'],
            ['expect_min_value','required'],
            ['expect_max_value','required'],
            ['gift_setting_id','required'],
            ['turn_counts','required'],
            ['gift_content_id','required'],
            ['gift_num','required'],
            ['create_time','required'],

        ];
    }

    /**
     * @desc 添加签到日志
     * @param $uid
     * @return bool|string
     */
    public function addLog($data)
    {
        if(! $this->validate($data,$this->rules()))
        {
            Logger::write('验证失败','类:'.__CLASS__.' 方法 '.__METHOD__,'error');
            return false;
        }

        $b = $this->insert($this->table,$data);

        return $b;
    }
    public function addLog2Redis($data)
    {
        if(! $this->validate($data,$this->rules()))
        {
            Logger::write('验证失败','类:'.__CLASS__.' 方法 '.__METHOD__,'error');
            return false;
        }
        $b = Redis::getIns()->rPush(Okey::rTurnUserLogList(),($data));
        if(!$b)
        {
            Logger::write("插入抽奖日志队列失败",__METHOD__,"ERROR");
        }
        return $b;
    }

} 