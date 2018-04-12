<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/4/8
 * Time: 11:50
 */

namespace Workerman\Model;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;

class SwitchSetting extends Model
{
    public $db = 'user';
    public $table = 'poker_switch_setting';
    public function getByChannelId($channel_id)
    {
        $r = $this->getRow($this->table,"channel_id=:channel_id and status=1",[':channel_id'=>$channel_id]);

        return $r;
    }

    /**
     * @desc 判断该系统平台是否开放
     * @param $channel_id
     * @param $platform_id
     */
    public function isPlatformOpen($channel_id,$platform_id)
    {
        $r = $this->getRow($this->table,"channel_id=:channel_id and FIND_IN_SET('{$platform_id}',platform_id) and status=1 order by update_time desc  ",
            [':channel_id'=>$channel_id]);

        return $r?true:false;
    }
    /**
     * @desc 判断该注册方式是否开放
     * @param $channel_id
     * @param $register_way_id
     */
    public function isRegisterOpen($channel_id,$register_way_id)
    {
        $r = $this->getRow($this->table,"channel_id=:channel_id and FIND_IN_SET('{$register_way_id}',register) and status=1 order by update_time desc  ",
            [':channel_id'=>$channel_id]);

        return $r?true:false;
    }
    /**
     * @desc 判断该登录方式是否开放
     * @param $channel_id
     * @param $login_way_id 登录方式
     */
    public function isLoginOpen($channel_id,$login_way_id)
    {
        $r = $this->getRow($this->table,"channel_id=:channel_id and FIND_IN_SET('{$login_way_id}',login) and status=1 order by update_time desc  ",
            [':channel_id'=>$channel_id]);

        return $r?true:false;
    }
    /**
     * @desc 判断该版本是否开放
     * @param $channel_id
     * @param $version  string 版本号
     */
    public function isVersionOpen($channel_id,$version)
    {
        $r = $this->getRow($this->table,"channel_id=:channel_id and FIND_IN_SET('{$version}',version) and status=1 order by update_time desc  ",
            [':channel_id'=>$channel_id]);

        return $r?true:false;
    }
    /**
     * @desc 判断该登录方式是否开放
     * @param $channel_id
     * @param $login_way_id 登录方式
     */
    public function getLoginOpen($channel_id)
    {
        $r = $this->getRow($this->table,"channel_id=:channel_id and status=1 order by update_time desc  ",
            [':channel_id'=>$channel_id]);
        return $r?$r['login']:'';
    }
    /**
     * @desc 发送警告
     * weixin:[
     * update_time
     * origin_ceil
     * origin_floor
     * new_ceil
     * new_floor
     * msg_format
     * ]
     */
    public function sendWarn($channel_id)
    {
        $r = $this->getRow($this->table,"channel_id=:channel_id and status=1 order by update_time desc  ",
            [':channel_id'=>$channel_id]);
        if(!$r)
        {
            Logger::write("该渠道未配置或未开启，不能发送警告",__METHOD__,"INFO");
            return false;
        }
        $weixin_warnging = $r['warning']['weixin_waning'];
        $update_time = $weixin_warnging['update_time'];
        $origin_ceil = $weixin_warnging['origin_ceil'];
        $origin_floor = $weixin_warnging['origin_floor'];
        $new_ceil = $weixin_warnging['new_ceil'];
        $new_floor = $weixin_warnging['new_floor'];
        $msg = $weixin_warnging['msg'];
        $today = mktime(0,0,0,date('m'),date('d'),date("Y"));
        if($update_time > $today)
        {
            $ceil = $new_ceil;
            $floor = $new_floor;
        }else{
            $ceil = $origin_ceil;
            $new_ceil=$origin_ceil;
            $floor = $origin_floor;
        }
        $user_pays = 0;//用户支付总额
        if($user_pays >= $ceil)
        {
            $update_time = time();
            $new_ceil = $new_ceil + $new_ceil*0.2;
            //发送警告
            $weixin_warnging['update_time'] = $update_time;
            $weixin_warnging['origin_ceil'] = $origin_ceil;
            $weixin_warnging['origin_floor'] = $origin_floor;
            $weixin_warnging['new_ceil'] = $new_ceil;
            $weixin_warnging['new_floor'] = $new_floor;
            $weixin_warnging['msg'] = $msg;
            $s = $this->update($this->table,['update_time'=>time(),'warnging'=>json_encode(['weixin_waning'=>$weixin_warnging])],"id={$r['id']}");
            if(!$s)
            {
                Logger::write("更改支付警告配置信息失败",__METHOD__,"ERROR");
            }
        }
    }
    public function getOpenGames($uid,$channel_id)
    {
        $user_info = UserGame::instance()->getOne($uid);
        $user_vip = $user_info['vip'];
        $user_charge = $user_info['charge'];
        $user_total_play_hour = $user_info['total_online_time']/3600;//玩牌时长
        $user_total_online_hour = $user_info['online_time']/3600;//在线时长？
        $user_regtime = (time()-$user_info['sid_regtime'])/3660;//minute
        $user_play_num['ddz'] = UserDdzGamedata::instance()->getPlayCounts($uid);
        $user_play_num['niuniu'] = UserNNGamedata::instance()->getPlayCounts($uid);
        $user_play_num['zjh'] = UserZJHGamedata::instance()->getPlayCounts($uid);
        $user_play_num['mj'] = UserMajiangGamedata::instance()->getPlayCounts($uid);
        $user_chip_flow = $user_info['chip_flow'];//金币流水
        $r = $this->getRow($this->table,"channel_id=:channel_id  and status=1 order by update_time desc  ",
            [':channel_id'=>$channel_id]);

        $games = json_decode($r['game'],true);
        $niuniu_setting  = $games['niuniu'];
        $ddz_setting  = $games['ddz'];
        $zjh_setting  = $games['zjh'];
        $mj_setting  = $games['mj'];
        $niuniu_count =$niuniu_setting['counts'];
        $ddz_count =$ddz_setting['counts'];
        $zjh_count =$zjh_setting['counts'];
        $mj_count  =$mj_setting['counts'];

        $niuniu_arr = [
            $niuniu_setting['total_play_hour']<=$user_total_play_hour?1:0,
            $niuniu_setting['total_online_hour']<=$user_total_online_hour?1:0,
            $niuniu_setting['register_hour']<=$user_regtime?1:0,
            $niuniu_setting['vip']<=$user_vip?1:0,
            $niuniu_setting['charge']<=$user_charge?1:0,
            $niuniu_setting['chip_flow']<=$user_chip_flow?1:0,
            $niuniu_setting['ddz_num']<=$user_play_num['ddz']?1:0,
            $niuniu_setting['niuniu_num']<=$user_play_num['niuniu']?1:0,
            $niuniu_setting['zjh_num']<=$user_play_num['zjh']?1:0,
            $niuniu_setting['mj_num']<=$user_play_num['mj']?1:0,
        ];
        $ddz_arr = [
            $ddz_setting['total_play_hour']<=$user_total_play_hour?1:0,
            $ddz_setting['total_online_hour']<=$user_total_online_hour?1:0,
            $ddz_setting['register_hour']<=$user_regtime?1:0,
            $ddz_setting['vip']<=$user_vip?1:0,
            $ddz_setting['charge']<=$user_charge?1:0,
            $ddz_setting['chip_flow']<=$user_chip_flow?1:0,
            $ddz_setting['ddz_num']<=$user_play_num['ddz']?1:0,
            $ddz_setting['niuniu_num']<=$user_play_num['niuniu']?1:0,
            $ddz_setting['zjh_num']<=$user_play_num['zjh']?1:0,
            $ddz_setting['mj_num']<=$user_play_num['mj']?1:0,
        ];
        $zjh_arr = [
            $zjh_setting['total_play_hour']<=$user_total_play_hour?1:0,
            $zjh_setting['total_online_hour']<=$user_total_online_hour?1:0,
            $zjh_setting['register_hour']<=$user_regtime?1:0,
            $zjh_setting['vip']<=$user_vip?1:0,
            $zjh_setting['charge']<=$user_charge?1:0,
            $zjh_setting['chip_flow']<=$user_chip_flow?1:0,
            $zjh_setting['ddz_num']<=$user_play_num['ddz']?1:0,
            $zjh_setting['niuniu_num']<=$user_play_num['niuniu']?1:0,
            $zjh_setting['zjh_num']<=$user_play_num['zjh']?1:0,
            $zjh_setting['mj_num']<=$user_play_num['mj']?1:0,
        ];
        $mj_arr = [
            $mj_setting['total_play_hour']<=$user_total_play_hour?1:0,
            $mj_setting['total_online_hour']<=$user_total_online_hour?1:0,
            $mj_setting['register_hour']<=$user_regtime?1:0,
            $mj_setting['vip']<=$user_vip?1:0,
            $mj_setting['charge']<=$user_charge?1:0,
            $mj_setting['chip_flow']<=$user_chip_flow?1:0,
            $mj_setting['ddz_num']<=$user_play_num['ddz']?1:0,
            $mj_setting['niuniu_num']<=$user_play_num['niuniu']?1:0,
            $mj_setting['zjh_num']<=$user_play_num['zjh']?1:0,
            $mj_setting['mj_num']<=$user_play_num['mj']?1:0,
        ];

        return [
            'niuniu'=>$niuniu_count<=count(array_filter(array_values($niuniu_arr),function ($val){if($val == 1 )return true;}))?1:0,
            'ddz'=>$ddz_count<=count(array_filter($ddz_arr,function ($val){if($val == 1 )return true;}))?1:0,
            'zjh'=>$zjh_count<=count(array_filter($zjh_arr,function ($val){if($val == 1 )return true;}))?1:0,
            'mj'=>$mj_count<=count(array_filter($mj_arr,function ($val){if($val == 1 )return true;}))?1:0
        ];
    }


}