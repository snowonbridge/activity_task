<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/3/14
 * Time: 11:36
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;

class FriendOperateNumLog extends Model{

    public $db = 'slog';
    public $table = 'friend_operatenum_log';


    /**
     * 操作类型:1@结为好友,2@解除好友
     */
    const OPERATE_TYPE_FRIEND_MAKE = 1;
    const OPERATE_TYPE_FRIEND_UNBIND = 2;

    /**
     * 主被类型:1@主动,2@被动
     */
    const FTYPE_POSITIVE = 1;
    const FTYPE_PASSIVE = 2;

    /**
     * @return array
     *  `uid` int(11) NOT NULL,
    `operate_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '操作类型:1@加好友,2@解除好友',
    `ftype` tinyint(4) NOT NULL DEFAULT '0' COMMENT '主被关系:1@主动,2@被动',
    `num` int(11) NOT NULL DEFAULT '0' COMMENT '当日操作次数',
    `vip` tinyint(4) NOT NULL DEFAULT '0' COMMENT '同意/解绑时uid的vip等级',
    `gift_content` varchar(255) NOT NULL DEFAULT '' COMMENT '加好友时礼物内容:json格式[{id:1,num:1}]',
    `day_time` int(11) NOT NULL DEFAULT '0' COMMENT '操作日期',
    `operate_time` int(11) NOT NULL DEFAULT '0' COMMENT '同意/解绑时间',
     */
    public function rules()
    {
        return [
            ['uid','required'],
            ['operate_type','required'],
            ['ftype','required'],
            ['num','required'],
            ['vip','required'],
            ['gift_content','required'],
            ['day_time','required'],
            ['operate_time','required']
        ];
    }
    public function add($data)
    {
        if(!$this->validate($data,$this->rules()))
        {
            Logger::write('数据插入rules验证不通过'.json_encode($data),__METHOD__,"ERROR");
            return false;
        }
        $data['create_time'] = time();

        return $this->insert($this->table,$data);
    }
    public function getOneById($id)
    {
        return  $this->getRow($this->table,"id=:id",[':id'=>$id]);
    }
}