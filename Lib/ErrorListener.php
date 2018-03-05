<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-10-30
 * Time: 下午6:31
 */

namespace Workerman\Lib;


class ErrorListener extends \Exception{


    static public function shutdownLog()
    {
        $err = error_get_last();
        if ($err && in_array($err['type'],array(E_ERROR,E_WARNING))) {
            Logger::error(var_export($err,true),__METHOD__);
        }

    }
    static public function register()
    {
        register_shutdown_function(['\\Workerman\\Lib\\ErrorListener','shutdownLog']);
        set_error_handler(['\\Workerman\\Lib\\ErrorListener','shutdownLog']);
    }
} 