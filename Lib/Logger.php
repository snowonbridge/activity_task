<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-9
 * Time: 下午2:58
 */

namespace Workerman\Lib;


class Logger {
    /**
     *
     * @param $data
     * @param string $category
     * @param string $level
     */
    static function write($data,$category='default',$level="info")
    {
        if(!DEBUG && strtolower($level) !='error')
            return true;
        $fileName = RUNTIME_PATH . 'app-'.date("Y-m-d",time()).'.log';
        $time =  $date = date("Y-m-d H:i:s");
        $content = "\n[{$category} {$level} {$time}] ".var_export($data,true);
        file_put_contents($fileName,$content,FILE_APPEND);
    }
    static function  error($data,$category='default')
    {
        $fileName = RUNTIME_PATH . 'app-error-'.date("Y-m-d",time()).'.log';
        $time =  $date = date("Y-m-d H:i:s");
        $content = "\n[{$category} ERROR {$time}] ".var_export($data,true);
        file_put_contents($fileName,$content,FILE_APPEND);
    }
    static function sql($sql,$sqlparams,$category='default')
    {
        $fileName = RUNTIME_PATH . 'sql-'.date("Y-m-d",time()).'.log';
        $time =  $date = date("Y-m-d H:i:s");
        if($sqlparams)
        {
            $sql = str_replace(array_keys($sqlparams),array_values($sqlparams),$sql);
        }
        $content = "\n[{$category}  {$time}] $sql";
        file_put_contents($fileName,$content,FILE_APPEND);

    }

    static function redis($cmd,$param,$category='default')
    {
        $fileName = RUNTIME_PATH . 'redis-'.date("Y-m-d",time()).'.log';
        $time =  $date = date("Y-m-d H:i:s");

        if($param)
        {
            $param_str = implode(' ',$param);
        }else{
            $param_str = '';
        }
        $content = "\n[{$category}  {$time}] $cmd $param_str";
        file_put_contents($fileName,$content,FILE_APPEND);
    }

} 