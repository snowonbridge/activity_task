<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/3/14
 * Time: 14:40
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;

class FriendInviteGameLog extends Model{

    public $db = 'slog';
    public $table = 'friend_invite_game_log';

    public function rules()
    {
        return [
            ['uid','required'],
            ['friend_uid','required'],
//            ['game_id','required'],
//            ['game_type_id','required'],
//            ['game_room_id','required'],
//            ['invite_time','required']
        ];
    }
    public function insert($data)
    {
        if(!$this->validate($data,$this->rules()))
        {
            Logger::write('数据插入rules验证不通过'.json_encode($data),__METHOD__,"ERROR");
            return false;
        }
        $data['create_time'] = time();

        return $this->insertRaw($this->table,$data);
    }
}