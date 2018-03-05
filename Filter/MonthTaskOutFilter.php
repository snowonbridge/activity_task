<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2017/12/29
 * Time: 10:24
 */

namespace Workerman\Filter;


use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Lib\Okey;
use Workerman\Lib\Redis;
use Workerman\Model\ActivityCategory;
use Workerman\Model\ActivityChannel;
use Workerman\Model\ActivityControl;
use Workerman\Model\ConsumptionConfig;
use Workerman\Model\ConsumptionGift;
use Workerman\Model\CrossChallegeConfig;
use Workerman\Model\CrossChallegeGift;
use Workerman\Model\FaControlAppList;
use Workerman\Model\FaControlAreaSetting;
use Workerman\Model\FaControlStoreSetting;
use Workerman\Model\User;
use Workerman\Model\UserGame;
use Workerman\Service\ATCode;

class MonthTaskOutFilter extends Model{


    public function exec($uid,$sid,$version,$unid,$activityItemList)
    {
        $aG = UserGame::instance()->getOne($uid);
        if(!$aG)
        {
            Logger::write("当获取用户信息错误时,不显示活动",__METHOD__,"ERROR");
            return [];
        }
        //玩家级别 1普通玩家2高级玩家',
        $player_type = $aG['player_type_force']>0?intval($aG['player_type_force']):intval($aG['player_type']);
        $player_type_force = $aG['player_type_force'];

        $aG = User::instance()->getUBase($uid);
        //渠道号

        $city_id = $aG['city_id'];
        //省份
        $region_id = $aG['region_id'];
        $cate = ActivityCategory::instance()->getAll();
        $tmpList=[];
        foreach($cate as $k=>$item)
        {
            $item['parent_id'] == 32 && $tmpList[$item['activity_id']] = $item;
        }
        if(empty($tmpList))
        {
            Logger::write("unid=$unid  苹果不受控制，返回多游戏模式",__METHOD__);
            return [];
        }
        $ruleList = $tmpList;unset($cate,$tmpList);
        $result=[];
        $showGameList = $this->gameShowList($sid,$version,$unid,$player_type,$region_id,$city_id,$player_type_force);
        foreach($activityItemList as $k=>$item)
        {

            $row = isset($ruleList[$item['activity_id']])?$ruleList[$item['activity_id']]:[];
            if($row)
            {
                $channels = json_decode($row['channel_id_str'],true);

                $provinces = isset($channels[$unid])?$channels[$unid]:[];
                if((!empty($provinces) && in_array($region_id,$provinces) && ActivityControl::instance()->isActive($row['activity_control_id'])
                    && ActivityChannel::instance()->isActive($unid))

                )
                {
                    if(ActivityCategory::instance()->isCrossActivity($item['activity_id'])
                        && $this->gamesFilter($this->getCrossGameStr($item['activity_id']),$showGameList)
                    )
                    {
                        $result[] = $item;
                    }elseif(!ActivityCategory::instance()->isCrossActivity($item['activity_id']))
                    {
                        if($player_type == ATCode::PLAYER_TYPE_FORCE_LOW && $item['activity_id'] == 2107)
                        {//如果是普通用户，不显示2107这个活动
                            continue;
                        }
                        $result[] = $item;
                    }


                }else{
//                    Logger::write("活动id：{$item['activity_id']}不满足在activitycategory表中的渠道号或省份".var_export([$item,$row],true),__METHOD__);
                }
            }else{
//                Logger::write("活动id：{$item['activity_id']}在activitycategory表中未设置".var_export([$item,$row],true),__METHOD__);
            }
        }

        return $result;
    }

    /**
     * 控制哪些游戏是否显示
     */
    public function gameShowList($sid,$version,$unid,$player_type,$region_id,$city_id,$player_type_force)
    {
        $allGameList = ATCode::getGameAll($version);
        $simpleGameList = ATCode::getGameSimple();
        if($unid == ATCode::CHANNEL_ID_APPLE){ //A01 苹果不受控制，返回多游戏模式
            Logger::write("unid=$unid  苹果不受控制，返回多游戏模式",__METHOD__);
            return $allGameList;
        }

        if($player_type_force > 0){//若有强制限制
            return $player_type_force == ATCode::PLAYER_TYPE_FORCE_LOW ? $simpleGameList:$allGameList;
        }


        if ($player_type == 2) { //A03 高级玩家不受控制，返回多游戏模式
            return $allGameList;
        }

        if (!$sid or !$version or !$unid or !$region_id or !$city_id) {
            return $simpleGameList;
        }


        $app_id = FaControlAppList::instance()->getControlAppId($sid,$version);
        $controlStoreSetting = FaControlStoreSetting::instance()->getControlStoreSetting($app_id);
        $controlAreaSetting = FaControlAreaSetting::instance()->getControlAreaSetting($app_id);
        if(empty($app_id) || empty($controlStoreSetting) || empty($controlAreaSetting)){
            return $simpleGameList;
        }

        foreach($controlStoreSetting as $k => $v){
            if($v['control_store'] == $unid){
                $store_setting = $v;
                break;
            }
        }
        //isControlShow 高级玩家模式 0-开启(多游戏)，1-关闭(单游戏)
        if(empty($store_setting) ){
            return $simpleGameList;
        }


        if($region_id == 440000 && in_array($city_id,[440100,440300])){//广州、深圳独立于广东省配置（北上广深）
            $region_id=$city_id;
        }
        $area_setting = array();
        foreach($controlAreaSetting as $k => $v){
            if($v['control_area'] == $region_id){
                $area_setting = $v;
                break;
            }
        }
        //4.渠道，区域时间段策略同时开启时，高级玩家功能等开关按钮同时不生效。
        if($store_setting['time_control']==1 && $area_setting['time_control']==1){//时间策略开启
            $time_common_setting = $this->getControlTimeCommonSetting();
            $isInTimeSetting = false;
            $now_time = strtotime(date('Y-m-d H:i'));//当前时间
            foreach($time_common_setting as $time_setting){
                $time1 = strtotime(date('Y-m-d ').$time_setting[0]);
                $time2 = strtotime(date('Y-m-d ').$time_setting[1]);
                if($time1 <= $now_time && $now_time <= $time2){//满足时间段直接返回全游戏
                    $isInTimeSetting = true;
                    break;
                }
            }
            if($isInTimeSetting){
                return $allGameList;
            }else{//其他时间返回斗地主
                return $simpleGameList;
            }
        }
        if(empty($area_setting)  ){
            return $simpleGameList;//用户归属为未知地区的返回单游戏模式
        }
        if( ($store_setting['ddz']==0 && $store_setting['ysz']==0 && $store_setting['lhd']==0 && $store_setting['mj']==0 &&$store_setting['nn']==0 ) || $store_setting['is_control_show']==1 ){
            return $simpleGameList;
        }
        if( ($area_setting['ddz']==0 && $area_setting['ysz']==0 && $area_setting['lhd']==0 && $area_setting['mj']==0 &&$area_setting['nn']==0) || $area_setting['is_control_show']==1){
            return $simpleGameList;
        }

        $game_list_map = [
            'ddz'=>ATCode::GAME_ID_DDZ,
            'ysz'=>ATCode::GAME_ID_ZJH,
            'lhd'=>ATCode::GAME_ID_LHD,
            'mj'=> ATCode::GAME_ID_MJ,
            'nn'=> ATCode::GAME_ID_NIUNIU,
        ];
        //3.渠道、区域结合
        $gameListShow = [];
        $game_setting['ddz'] = $store_setting['ddz'] == 0 ? 0 : $area_setting['ddz'];//渠道关闭优先 斗地主开关 1-开启，0-关闭
        $game_setting['ysz'] = $store_setting['ysz'] == 0 ? 0 : $area_setting['ysz'];//渠道关闭优先
        $game_setting['lhd'] = $store_setting['lhd'] == 0 ? 0 : $area_setting['lhd'];//渠道关闭优先
        $game_setting['mj'] = $store_setting['mj'] == 0 ? 0 : $area_setting['mj'];//渠道关闭优先
        $game_setting['nn'] = $store_setting['nn'] == 0 ? 0 : $area_setting['nn'];//渠道关闭优先
        foreach($game_setting as $k=>$v){
            if($v==1){
                $gameListShow[] = $game_list_map[$k];
            }
        }

        if(empty($gameListShow)){//没有设置展示的子游戏，返回单款模式

            return $simpleGameList;
        }else{
            foreach($gameListShow as $k=>$v){
                if(!in_array($v,$allGameList)){
                    unset($gameListShow[$k]);
                }
            }
            if(empty($gameListShow)){
                return $simpleGameList;
            }
            return $gameListShow;
        }

    }

    /**
     * @param $activityGameStr string "1005-40,1005-41"
     * @param $allowGame    array
     * @return bool
     */
    public function gamesFilter($activityGameStr,$allowGame)
    {
        $activityGame = explode(',',$activityGameStr);
        $activityGame = array_map(function($val){
            return substr($val,0,strpos($val,'-'));
        },$activityGame);
        $activityGame = array_unique($activityGame);
        if(array_intersect($activityGame,$allowGame))
        {
            Logger::write("游戏显示过滤规则通过:activityGameStr:".$activityGameStr." 和 allowGame：".json_encode($allowGame),__METHOD__);
            return true;
        }
        Logger::write("游戏显示过滤规则未通过:activityGameStr:$activityGameStr 和 allowGame：".json_encode($allowGame),__METHOD__);
        return false;

    }

    public function getCrossGameStr($activity_id)
    {
        $giftConfig = CrossChallegeGift::instance()->getOne($activity_id);
        if(false !== strpos($giftConfig['challege_list'],','))
        {
            list($config_id) = explode(',',trim($giftConfig['challege_list'],','));
        }else{
            $config_id = $giftConfig['challege_list'];
        }
        $config = CrossChallegeConfig::instance()->getOne($config_id);

        return $config['game_list'];
    }

    /**
     * 获取时间策略设置
     * @return array|mixed
     */
    private function getControlTimeCommonSetting(){
        $cache = Redis::getIns()->get(Okey::rControlTimeCommonSetting()); //从缓存中取信息
        if ($cache !== false){
            return json_decode($cache,true);
        }
        $time_setting = [//支持多个时间段，默认8:00到20:00时间段不开
            ['00:00','08:00'],
            ['20:00','23:59']
        ];
        Redis::getIns()->set(Okey::rControlTimeCommonSetting(),json_encode($time_setting));
        return $time_setting;
    }

}
