<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-10-25
 * Time: 下午4:48
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;

class TurnGiftRateLog extends Model{

    public $db = 'activity';
    public $table = 'turn_gift_rate_log';
    public function rules()
    {
        return [
            ['uid','required'],
            ['exchange_id','required'],
            ['gift_setting_id','required'],
            ['gift_content_id','required'],
            ['gift_num','required'],
            ['lucky_value','required'],
            ['rate','required'],
            ['turn_counts','required'],
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

        if(count($data) == count($data,COUNT_RECURSIVE))
        {
            $b = $this->insert($this->table,$data);
        }else{
            $b = $this->insertBatch($this->table,$data);
        }

        return $b;
    }
} 