<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-7
 * Time: 下午3:01
 */

namespace Workerman\Service;


use Workerman\Lib\FileCache;
use Workerman\Lib\Okey;
use Workerman\Lib\Redis;
use Workerman\Model\GiftContentSetting;

class  ATCode{

//    //牌局关卡活动ID
//    const ATCODE_PHONE_BIND=113;//手机绑定;
//    const ATCODE_WX_REDPACK=115;//元微信红包（03）;
//    const ATCODE_GOLD_LOCATION_5TIMES=304;//金币场对局5局
//    const ATCODE_CREATE_ROOM_1TIMES = 305;//创建房间一次,并完成游戏
//    const ATCODE_REATE_ROOM_5TIMES = 306;//创建房间五次,并完成游戏
//    const ATCODE_JOIN_FRIEND_5TIMES=309;//参与好友局5次（非房主）;
//
//    //渠道名称
//
    const CLMODE_ACTIVITY_TASK=16;
    const CLMODE_WIN_STREAK = 17;//启用
    const CLMODE_ACTIVITY_TURN= 18;

    /**
     * 动作id配置
     */
    const ACTION_GT = 1;
    const ACTION_LT = 2;
    const ACTION_PURCHASE = 3;
    const ACTION_EXCHANGE = 4;
    const ACTION_USE = 5;

    //使用的货币，道具id
    const GIFT_GOLDCOIN = 1;
    const GIFT_ROOMCARD = 3;
    const GIFT_DIAMOND = 2;
    const GIFT_JBCARD = 117;//禁比卡
    const GIFT_REMINDCARD_I = 110;//记牌器I-2小时记牌器
    const GIFT_REMINDCARD_II = 111;//记牌器II-24小时记牌器
    const GIFT_REMINDCARD_III = 112;//记牌器III


//为了显示方便 ：1 充值活动tab页  ，   2消费有奖励Tab页  3  边玩边收钱 tab页
    const TAB_EXCHANGE = 1;
    const TAB_COMSUMPTION_REWARD = 2;
    const TAB_PLAY_AND_MAKE_MONEY = 3;

    //转盘奖励级别
    const TURN_GIFT_LEVEL_1=1;//  白送型
    const TURN_GIFT_LEVEL_2=2;// 大众型
    const TURN_GIFT_LEVEL_3=3;// 普通型
    const TURN_GIFT_LEVEL_4=4;//稀有型


    const PLATFORM_ONLINE = 10001;//线上
    const PLATFORM_OFFLINE = 10002;//线下

    //1普通玩家2高级玩家',0无限制玩家级别
    const USER_LEVEL_NO_LIMIT=0;
    const USER_LEVEL_NORMAL=1;
    const USER_LEVEL_HIGH=2;

    /**
     * 游戏ID
     */
    const GAME_ID_DDZ=1004;
    const GAME_ID_ZJH=1001;
    const GAME_ID_LHD=1002;
    const GAME_ID_NIUNIU=1003;
    const GAME_ID_MJ=1005;
    const GAME_ID_HUNDRED=1006;//百人场

    const SERVER_SCENE_ID_LOTTERY=1;
    /**
     * 渠道id
     */

    const CHANNEL_ID_APPLE=1;//和activity_channel表的channel_id一致

    /**
     * $player_type_force配置，poker_usergame表
     */
    const PLAYER_TYPE_FORCE_LOW = 1;
    const PLAYER_TYPE_FORCE_HIGHT = 2;

    const SCENE_ID_FIRSTCHARGE =2034;


    const APP_ONLINE_VERSION_1_0_0 = '1.0.0';
    const APP_ONLINE_VERSION_1_1_0 = '1.1.0';


    /**
     * @desc 获取奖励
     * @param $gift_content
     * @return array
     */
    public function getGiftList($gift_content)
    {
        $result=[];
        if(isset($gift_content) && is_array(json_decode($gift_content,true)))
        {
            $giftList = json_decode($gift_content,true);
            foreach($giftList as $k=>$v)
            {
                $r = GiftContentSetting::instance()->getOne($k);
                if($r)
                {

                    $t['name']    = $r['name'];
                    $t['num']     = $v;
                    $t['id']      = $k;
                    $result[]=$t;
                }
            }
        }
        return $result;
    }

    /**
     * @desc 获取设置缓存
     * @param $key
     * @param null $callback
     * @param array $params
     * @return false|mixed
     */
    public  static function getCache($key,$callback=null,$params=array(),$expire=86400,$localCache=true)
    {
        if($localCache)
        {
            $localCacheValue = FileCache::getInstance()->get($key);
            if(false !== $localCacheValue)
            {
                return $localCacheValue;
            }
        }

        $value = Redis::getIns()->get($key);

        if(false === $value)
        {
            if(null !== $callback)
            {
                $result = call_user_func_array($callback,$params);
                if(is_string($result) || is_numeric($result) || is_bool($result))
                {
                    if(false !== $result)
                    {
                        Redis::getIns()->nsetex($key,$result,$expire);
                    }
                }elseif(is_array($result) || is_object($result))
                {
                    Redis::getIns()->nsetex($key,$result,$expire,false,true);

                }
                $localCache && FileCache::getInstance()->set($key,$result,$expire);
                return $result;
            }
        }
        $ret = @unserialize($value);
        $localCache &&  FileCache::getInstance()->set($key,false !== $ret ?$ret:$value,$expire);
        return false !== $ret ?$ret:$value;
    }
    public static function rmCache($key)
    {
        FileCache::getInstance()->rm($key);
        return Redis::getIns()->delete($key);
    }
    public static function transToMoney($diamond_num=0)
    {
        return (int)$diamond_num/2;
    }

    public static function getGameAll($version='')
    {
        if($version == self::APP_ONLINE_VERSION_1_0_0)
        {
            return [ATCode::GAME_ID_DDZ,ATCode::GAME_ID_ZJH];

        }elseif($version == self::APP_ONLINE_VERSION_1_1_0){

            return [ATCode::GAME_ID_DDZ,ATCode::GAME_ID_ZJH,ATCode::GAME_ID_NIUNIU];
        }else{

            return [ATCode::GAME_ID_DDZ,ATCode::GAME_ID_ZJH,ATCode::GAME_ID_NIUNIU];
        }
    }
    public static function getGameSimple()
    {
        return [ATCode::GAME_ID_DDZ];
    }

    public static function money2Diamond($money)
    {
        return (int)($money*2);
    }
} 