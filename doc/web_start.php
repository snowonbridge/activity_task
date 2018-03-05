<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-9
 * Time: 下午7:10
 */

require_once dirname(__DIR__).'/Autoloader.php';

$webserver = new \Workerman\WebServer('http://0.0.0.0:9006');
// 类似nginx配置中的root选项，添加域名与网站根目录的关联，可设置多个域名多个目录
$webserver->addRoot('www.soultask.com', __DIR__);
// 设置开启多少进程
$webserver->count = 1;
$monitor_dir = realpath(__DIR__);
//$webserver->reloadable = false;
$last_mtime = time();
$webserver->onWorkerStart = function(){
    global $monitor_dir;
    // watch files only in daemon mode
    if(!\Workerman\Worker::$daemonize )
    {
        // chek mtime of files per second
        \Workerman\Lib\Timer::add(1, 'check_files_change', array($monitor_dir),true);
    }
};
function check_files_change($monitor_dir)
{
    global $last_mtime;
    // recursive traversal directory
    $dir_iterator = new RecursiveDirectoryIterator($monitor_dir);
    $iterator = new RecursiveIteratorIterator($dir_iterator);
    foreach ($iterator as $file)
    {
        // only check php files
        if(pathinfo($file, PATHINFO_EXTENSION) != 'php')
        {
            continue;
        }
        // check mtime
        if($last_mtime < $file->getMTime())
        {
            echo $file." update and reload\n";
            // send SIGUSR1 signal to master process for reload
            posix_kill(posix_getppid(), SIGUSR1);
            $last_mtime = $file->getMTime();
            break;
        }
    }
}
\Workerman\Worker::$logFile = './webdoc.log';
\Workerman\Worker::$pidFile='./webdoc.pid';
\Workerman\Worker::runAll();