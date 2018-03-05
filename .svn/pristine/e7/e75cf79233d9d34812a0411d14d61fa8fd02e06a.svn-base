<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-8-31
 * Time: 上午10:09
 */

namespace Workerman\Model;

use Workerman\Lib\FileCache;
use Workerman\Lib\Okey;
use Workerman\Lib\Model;
use Workerman\Lib\Logger;
use Workerman\Lib\Redis;

class UserGame extends Model{
    public $db = 'user';
    public $table = 'poker_usergame';

    public function __construct($db='')
    {
        parent::__construct($db);
    }
    public function getOne($uid)
    {
//        hMSet(Okey::rU($uid), $user)
        if(($userInfo = FileCache::getInstance()->get(Okey::rU($uid))) !== false)
        {
            return $userInfo;
        }
        $r =  Redis::getIns('user')->hGetAll(Okey::rU($uid));
        if(isset($r['chip']))
        {
            FileCache::getInstance()->set(Okey::rU($uid),$r, Okey::EX_ONE_MINUTE);
            return $r;
        }
        $r = $this->getRow($this->table,"uid=:uid",[':uid'=>$uid]);
        if($r)
        {
            Redis::getIns('user')->hMSet(Okey::rU($uid),$r);
            Redis::getIns('user')->setTimeout(Okey::rU($uid), Okey::EX_ONE_DAY);
            FileCache::getInstance()->set(Okey::rU($uid),$r, Okey::EX_ONE_MINUTE);
        }
        return $r;
    }

    /**
     * @desc 增加金币
     */
    public function addGoldCoin($uid,$num=0)
    {
        if($uid <=0 || $num<=0)
        {
            Logger::write('验证失败','类:'.__CLASS__.' 方法 '.__METHOD__,'error');
            return false;
        }
        $r = $this->incr($this->table,"uid=:uid",[':uid'=>$uid],'chip',$num);
        //用户金币相关缓存操作----
        //#TODO
        if($r && $this->rExistGoldCoin($uid))
        {
            $this->rAddGoldCoin($uid,$num);
        }
        FileCache::getInstance()->rm(Okey::rU($uid));
        return $r;
    }
    /**
     * @desc 增加钻石
     */
    public function addDiamond($uid,$num=0)
    {
        if($uid <=0 || $num<=0)
        {
            Logger::write('验证失败','类:'.__CLASS__.' 方法 '.__METHOD__,'error');
            return false;
        }
        $r = $this->incr($this->table,"uid=:uid",[':uid'=>$uid],'diamond',$num);
        //用户金币相关缓存操作----
        //#TODO
        if($r && $this->rExistDiamond($uid))
        {
            $this->rAddDiamond($uid,$num);
        }
        FileCache::getInstance()->rm(Okey::rU($uid));
        return $r;
    }
    public function reduceDiamond($uid,$num)
    {
        if(!$uid || $num<=0)
        {
            Logger::write('验证失败','类:'.__CLASS__.' 方法 '.__METHOD__,'error');
            return false;
        }
        if(  false === ($userDiamond = Redis::getIns('user')->hGet(Okey::rU($uid),'diamond')) )
        {
            $this->getOne($uid);
            $userDiamond = Redis::getIns('user')->hGet(Okey::rU($uid),'diamond');
        }
        if(  $userDiamond< $num)
        {
            Logger::write("金币不够");
            return false;
        }else{
            $this->rReduceDiamond($uid,$num);
            $r = $this->decr($this->table,"uid=:uid",[':uid'=>$uid],'diamond',$num);
            FileCache::getInstance()->rm(Okey::rU($uid));
            return $r?true:false;
        }
    }
    /**
     * @desc 增加房卡
     */
    public function addRoomCard($uid,$num=0)
    {
        if($uid <=0 || $num<=0)
        {
            Logger::write('验证失败','类:'.__CLASS__.' 方法 '.__METHOD__,'error');
            return false;
        }
        $r = $this->incr($this->table,"uid=:uid",[':uid'=>$uid],'roomcard',$num*10);
        //用户金币相关缓存操作----
        //#TODO
        if($r && $this->rExistRoomCard($uid))
        {
            $this->rAddRoomCard($uid,$num*10);
        }
        FileCache::getInstance()->rm(Okey::rU($uid));
        return $r;
    }



    //****************************start 缓存相关操作--------
    /**
     * @desc 增加用户金币
     * @param $uid
     * @param $num
     * @return int
     */
    public function rAddGoldCoin($uid,$num)
    {
        FileCache::getInstance()->rm(Okey::rU($uid));
        return Redis::getIns('user')->hIncrBy(Okey::rU($uid),'chip',$num);
    }
    /**
     * @desc 增加用户房卡
     * @param $uid
     * @param $num
     * @return int
     */
    public function rAddRoomCard($uid,$num)
    {
        FileCache::getInstance()->rm(Okey::rU($uid));
        return Redis::getIns('user')->hIncrBy(Okey::rU($uid),'roomcard',$num);
    }
    /**
     * @desc 增加用户钻石
     * @param $uid
     * @param $num
     * @return int
     */
    public function rAddDiamond($uid,$num)
    {
        FileCache::getInstance()->rm(Okey::rU($uid));
        return Redis::getIns('user')->hIncrBy(Okey::rU($uid),'diamond',$num);
    }
    /**
     * @desc 减少用户钻石
     * @param $uid
     * @param $num
     * @return int
     */
    public function rReduceDiamond($uid,$num)
    {
        FileCache::getInstance()->rm(Okey::rU($uid));
        return Redis::getIns('user')->hIncrBy(Okey::rU($uid),'diamond',-$num);
    }
    public function rExistDiamond($uid)
    {

        return  Redis::getIns('user')->hExists(Okey::rU($uid),'diamond');
    }
    public function rExistRoomCard($uid)
    {

        return  Redis::getIns('user')->hExists(Okey::rU($uid),'roomcard');
    }
    public function rExistGoldCoin($uid)
    {
        return  Redis::getIns('user')->hExists(Okey::rU($uid),'chip');
    }

    //增加救济次数
    public function rAddSubsidyCount($uid)
    {
        FileCache::getInstance()->rm(Okey::rU($uid));
        return Redis::getIns('user')->hIncrBy(Okey::rU($uid), 'subsidy_count',1);
    }


} 