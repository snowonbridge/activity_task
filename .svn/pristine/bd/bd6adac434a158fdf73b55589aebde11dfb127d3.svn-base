<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-10-17
 * Time: 下午12:10
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;

class TurnExchangeLog extends Model{
    public $db = 'activity';
    public $table = 'turn_exchange_log';

    public function rules()
    {
        return [
            ['uid','required'],
            ['lottery_id','required'],
            ['diamond','required'],
            ['counts','required'],
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
} 