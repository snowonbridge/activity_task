<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-8-25
 * Time: 下午3:28
 */

namespace Workerman\Model;

use Workerman\Lib\Logger;

use Workerman\Lib\Model;
use Workerman\Lib\Okey;
use Workerman\Lib\Redis;

class UserTool extends Model
{
    public $db = 'user';
    //@var $table 用户道具表
    public $table = 'poker_usertool';
    //0还可使用，1已经结束使用
    const STATUS_CAN_USE = 0;
    const STATUS_CANNOT_USE = 1;
    /**
     * 赠送者
     */
    const SEND_ADMIN = 0;//赠送人是管理员，玩家自己购买道具的时候也会是0

    /**
     * 工具id配置
     */
    const TOOL_REMIND_CARD = 110;//记牌器I id
    const TOOL_SEPREATE_CARD = 113;//分牌器I id
    const TOOL_JB_CARD = 117;//禁比卡 id

    /**
     * 工具的渠道id
     */
    const REF_SHOP_EXCHANGE = 1;//商城兑换
    const REF_EMAIL_GIVE = 2; //邮件赠送
    const  REF_FIRST_LOGIN=3;//首登奖励
    const REF_ACTIVITY_TASK=6;

    public function __construct($db='')
    {
        parent::__construct($db);
    }
    public function rules()
    {
        return [
            ['tlid','required'],
            ['usetimes','required'],
            ['tlusetime','required'],//
            ['uid','required'],
            ['status','required'],
            ['send','required'],
            ['ref','required'],
            ['source','required'],

        ];
    }
    /**
     * @desc 增加金币
     */
    public function addTool($data)
    {
        if(! $this->validate($data,$this->rules()))
        {
            Logger::write('验证失败'.var_export([$this->rules(),$data],true),'类:'.__CLASS__.' 方法 '.__METHOD__,'error');
            return false;
        }
        $this->rmCache($data['uid']);
        $data['gettime'] = time();
        $r = $this->insert($this->table,$data);
        //用户工具相关缓存操作----
        //#TODO
        return $r;
    }
    public function addTools($data)
    {
        $uid = 0;
        foreach($data as $k=>$v)
        {
            if(! $this->validate($v,$this->rules()))
            {
                Logger::write('验证失败'.var_export([$this->rules(),$data],true),'类:'.__CLASS__.' 方法 '.__METHOD__,'error');
                return false;
            }
            !$uid && $uid = $v['uid'];
            $data[$k]['gettime'] = time();
            $r[] = $data[$k];
        }
        $this->rmCache($uid);
        return $this->insertBatch($this->table,$r);

    }

    /**
     * @desc 用户首登奖励，系统赠送记牌器
     * @param $uid
     * @param $num
     */
    public function giveRemindCardTool($uid,$num)
    {
        $data = array();
        $data['tlid'] = self::TOOL_REMIND_CARD;
        $data['usetimes'] = $num;
        $data['tlusetime'] = 0;//赠送时，不设置
        $data['uid'] = $uid;
        $data['status'] = self::STATUS_CAN_USE;
        $data['send'] = self::SEND_ADMIN;
        $data['ref'] = self::SEND_ADMIN;
        $r = $this->addTool($data);
        return $r;
    }

    public function rmCache($uid)
    {
        Redis::getIns('log')->delete(Okey::rUserTool($uid,'All'));
        Redis::getIns('log')->delete(Okey::rUserTool($uid,'Counter'));
    }


}