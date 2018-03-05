<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-13
 * Time: 下午2:18
 */

namespace Workerman\Model;


use Workerman\Lib\FileCache;
use Workerman\Lib\Model;
use Workerman\Lib\Okey;
use Workerman\Lib\Redis;

class User extends Model{
    public $db = 'user';
    public $table = 'poker_user';
    public function getOneByUid($uid)
    {
        $map = $this->getMap($uid);
        if(!$map)
        {
            return false;
        }
        $r = $this->getRow($this->table,'id=:id',[':id'=>$map['mid']]);
        if($r)
        {
            return array_merge($r,$map) ;
        }else{
            return false;
        }

    }
    public function getMap($uid)
    {
        return UserMap::instance()->getMap($uid);
    }

    public function getUBase($uid, $cache = true)
    {

        if(($userInfo = FileCache::getInstance()->get(Okey::rU($uid))) !== false)
        {
            return $userInfo;
        }
        $aPcache =  Redis::getIns('user')->hGetAll(Okey::rU($uid));
        if ($cache && isset($aPcache['openid'])&& isset($aPcache['uid']))
        {
            FileCache::getInstance()->set(Okey::rU($uid),$aPcache, Okey::EX_ONE_MINUTE);
            return $aPcache;

        }
        !is_array($aPcache) && $aPcache = [];
        $aP = $this->getOneByUid($uid);
        $aP &&  $aP['uid'] =$uid;
        $aP && Redis::getIns('user')->hMSet(Okey::rU($uid), $aP); //设置缓存中的信息
        $aP && Redis::getIns('user')->setTimeout(Okey::rU($uid), 24 * 3600); //设置过期
        FileCache::getInstance()->set(Okey::rU($uid),$aP, Okey::EX_ONE_MINUTE);
        $aP = array_merge($aPcache, (array)$aP);
        return $aP;
    }
} 