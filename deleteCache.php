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
\Workerman\Lib\FileCache::getInstance()->rm('AT_3|11');
\Workerman\Lib\FileCache::getInstance()->rm('AT_3|12');
\Workerman\Lib\Redis::getIns()->delete('AT_3|11');
\Workerman\Lib\Redis::getIns()->delete('AT_3|12');