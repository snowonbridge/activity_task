<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/3/12
 * Time: 17:49
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;

class GiftLog extends Model{

    public $db = 'oneproxy_slog';
    public $table = 'gift_log';

    /**
     * @操作类型:1@变卖,2@赠送,3@接收
     */
    const OPERATE_TYPE_SELL = 1;
    const OPERATE_TYPE_PRESENT = 2;
    const OPERATE_TYPE_RECV = 3;

    /**
     *  礼物货币购买类型:1@金币,2@钻石,3@房卡
     */
    const M_TYPE_GOLDEN = 1;
    const M_TYPE_DIAMOND = 2;
    const M_TYPE_ROOMCARD = 3;

    public function rules()
    {
        return [
            ['uid','required'],
            ['mid','required'],
            ['uname','required'],
            ['channal_id','required'],
            ['gift_id','required'],
            ['gift_name','required'],
            ['operate_type','required'],
            ['m_type','required'],
            ['before_num','required'],
            ['after_num','required'],
            ['give_uid','required'],
            ['give_mid','required'],
            ['desc','required'],
            ['operate_time','required'],

        ];
    }
    public function insert($data)
    {
        if(!$this->validate($data,$this->rules()))
        {
            Logger::write('数据插入rules验证不通过',__METHOD__,"ERROR");
            return false;
        }
        $data['create_time'] = time();

        return $this->insertRaw($this->table,$data);
    }


}