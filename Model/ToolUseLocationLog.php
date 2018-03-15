<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/3/13
 * Time: 11:10
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Service\ATCode;

class ToolUseLocationLog extends Model{

    public $db = 'slog';
    public $table = 'tool_uselocation_log';


    public function rules()
    {
        return [
            ['ttid','required'],
            ['game_id','required'],
            ['game_type_id','required'],
            ['game_room_id','required'],
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

    public function getOne($ttid)
    {
       $r =  $this->queryOne($this->table,"ttid=:ttid",[ ':ttid'=>$ttid]);
        return $r;
    }


}