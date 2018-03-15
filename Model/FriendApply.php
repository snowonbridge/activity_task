<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/3/14
 * Time: 10:40
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;

class FriendApply extends Model{

    public $db = 'user';
    public $table = 'poker_friend_apply';

    //好友状态@0:申请中,1:成功添加,2:被拒绝,3:解除好友关系
    const STATUS_APPLYING = 0;
    const STATUS_AGREE=1;
    const STATUS_REJECTED=2;
    const STATUS_UNBINDING=3;
    const STATUS_UNBINDING_UNNORMAL=4;
    //消息读状态:0未读,1已读
    const READ_Y=1;
    const READ_N=0;

    public function getOneById($id)
    {
        return $this->getRow($this->table,"id=:id",[':id'=>$id]);
    }

    public function getOne($uid,$friend_uid)
    {
        $r = $this->getRow($this->table,"( (fuid=:fuid and tuid=:tuid) or (fuid=:tuid and tuid=:fuid )  ) and status=:status order by create_time desc ",[':fuid'=>$uid,':tuid'=>$friend_uid,':status'=>self::STATUS_AGREE]);
        return $r;
    }

    public function updateInviteStatus($id)
    {
        $r = $this->update($this->table,['is_invite_game'=>1],"id=:id",[':id'=>$id]);
        if(!$r)
        {
            Logger::error('更新操作失败 id:'.$id,__METHOD__);
        }
        return $r;

    }

}
