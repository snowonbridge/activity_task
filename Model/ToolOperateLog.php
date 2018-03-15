<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/3/9
 * Time: 10:23
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;

class ToolOperateLog extends Model{
    public $db = 'oneproxy_slog';
    public $table = 'tool_operate_log';
    /**
     * 操作类型:1@获取,2@过期，3@使用
     */
    const OPERATE_TYPE_GET      =1;
    const OPERATE_TYPE_EXPIRE  =2;
    const OPERATE_TYPE_USE      =3;

    /**
     * 获取道具方式:1@购买,2@系统赠送,3@玩家赠送,4@游戏获得
     */
    const GET_TYPE_PURCHASE = 1;
    const GET_TYPE_SYS_PRESENTER = 2;
    const GET_TYPE_USER_PERSENTER = 3;
    const GET_TYPE_GAME_GAIN = 4;
    /**
     * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户UID',
    `mid` int(11) NOT NULL DEFAULT '0' COMMENT '用户mid',
    `uname` varchar(50) NOT NULL DEFAULT '' COMMENT '昵称',
    `channal_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '渠道ID',
    `tool_id` smallint(6) NOT NULL DEFAULT '0' COMMENT '道具ID',
    `tool_name` varchar(50) NOT NULL DEFAULT '' COMMENT '道具名称',
    `operate_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '操作类型:1@获取,2@过期，3@使用',
    `get_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '获取道具方式:1@购买,2@系统赠送,3@玩家赠送,4@游戏获得',
    `get_type_desc` varchar(100) NOT NULL DEFAULT '' COMMENT '获取方式描述',
    `goods_id` int(11) NOT NULL DEFAULT '0' COMMENT '通过购买获取的商品ID',
    `use_location` varchar(50) NOT NULL DEFAULT '' COMMENT '使用位置',
    `before_num` int(11) NOT NULL COMMENT '使用前数量',
    `after_num` int(11) NOT NULL COMMENT '使用后数量',
    `expire_time` int(11) NOT NULL DEFAULT '0' COMMENT '到期时间',
    `begin_time` int(11) NOT NULL DEFAULT '0' COMMENT '道具起始时间',
    `valid_duration` int(11) NOT NULL DEFAULT '0' COMMENT '有效时间(秒',
    `use_time` int(11) NOT NULL DEFAULT '0' COMMENT '道具操作时间',
    `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
     */

    public function rules()
    {
        return [
            ['uid','required'],
            ['mid','required'],
            ['uname','required'],
            ['channal_id','required'],
            ['tool_id','required'],
            ['tool_name','required'],
            ['operate_type','required'],
            ['get_type','required'],
            ['get_type_desc','required'],
            ['goods_id','required'],
            ['use_location','required'],
            ['before_num','required'],
            ['after_num','required'],
            ['expire_time','required'],
            ['begin_time','required'],
            ['valid_duration','required'],
            ['use_time','required'],

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

    public function queryData()
    {
        return $this->queryAll($this->table);
    }


}