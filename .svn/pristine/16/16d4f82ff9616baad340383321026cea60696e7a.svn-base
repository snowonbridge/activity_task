<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/4/4
 * Time: 10:14
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;

class VipRecvGiftLog extends Model
{
    public $db = 'activity';
    public $table = 'vip_recv_gift_log';
    const STATUS_Y=1;
    const STATUS_N=0;

    /**
     * @desc 获取当天已经领取的补足列表
     * @param $uid
     * @param $day
     * @return bool
     */
    public function getOne($uid,$vip,$day)
    {
        $r = $this->getRows($this->table,"uid=:uid and vip=:vip and day_time=:day_time and receive_status=1",[":uid"=>$uid,':vip'=>$vip,':day_time'=>$day]);
        return $r;
    }
    public function rules()
    {
        return [
            ['uid','required'],
            ['vip','required'],
            ['priv_id','required'],
            ['gift_id','required'],
            ['gift_num','required'],//
            ['receive_status','required'],
            ['day_time','required'],

        ];
    }
    public function add($data)
    {
        if(! $this->validate($data,$this->rules()))
        {
            Logger::write('数据验证失败',__METHOD__,'ERROR');
            return false;
        }
        $data['create_time'] = time();
        return $this->insert($this->table,$data);
    }
    public function updateStatus($id)
    {
        return $this->update($this->table,['receive_status'=>self::STATUS_Y],"id=:id",[':id'=>$id]);
    }


}