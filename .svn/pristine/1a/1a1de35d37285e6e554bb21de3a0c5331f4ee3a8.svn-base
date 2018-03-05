<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-8-31
 * Time: 下午12:03
 */

namespace Workerman\Model;


use Workerman\Lib\Model;
use Workerman\Lib\Logger;

class DiamondLog extends Model{
    public $db = 'user';
    public $table = 'poker_diamondlog';
    const PLUS = 0;
    const MINUS=1;
    /**
     * 金币渠道
     */

    const CLMODE_FIRST_LOGIN_GIFT = 1;//每日首登 赠送奖励
    const CLMODE_MONTH_ACCUM_GIFT = 14;//月累积奖励
    public function __construct($db='')
    {
        parent::__construct($db);
    }
    public function rules()
    {
        return [
            ['uid','required'],
            ['clmode','required'],
            ['clflag','required'],
            ['cldiamond','required'],
            ['clleftdiamond','required'],
            ['clremark','required'],
            ['cldesc','required'],

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
        $data['cltime'] =time();
        $b = $this->insert($this->table,$data);

        return $b;
    }

} 