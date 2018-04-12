<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/3/1
 * Time: 17:51
 */

require_once 'Autoloader.php';
define('CFG_PATH',__DIR__.'/Config/');
define('RUNTIME_PATH',__DIR__.'/Runtime/');
define('DEBUG',true);
$data['uid']=43;
$data ['mid']=23;
$data['uname']='asdf';
$data['channal_id']=23;
$data['tool_id']=3;
$data['tool_name']='二小时记牌器';
$data['operate_type']='1';
$data['get_type']='2';
$data['get_type_desc']='1aa';
$data['goods_id']=91;
$data['use_location']='斗地主';
$data['before_num']=23;
$data['after_num']=22;
$data['expire_time']=time();
$data['begin_time']=time()-12;
$data['valid_duration']=12;
$data['use_time']=time()-23;
//$ret = \Workerman\Model\ToolOperateLog::instance()->insert($data);
//$ret = \Workerman\Model\ToolOperateLog::instance()->queryData();
//var_export($ret);
//\Workerman\Controller\TaskDispatcher::instance()->pushExpireLog();
//\Workerman\Controller\TaskDispatcher::instance()->dealExpireTool();
$ret = \Workerman\Model\SwitchSetting::instance()->getLoginOpen(23);
var_export($ret);