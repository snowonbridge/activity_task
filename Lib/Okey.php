<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-9
 * Time: 下午2:56
 */

namespace Workerman\Lib;


class Okey {
    const EX_ONE_DAY=86400;//3600*24
    const EX_ONE_HOUR=3600;//3600
    const EX_ONE_MINUTE=60;//
    const EX_HALF_MINUTE=30;//
    const EX_ONE_MONTH=2678400;
    static function rIpLimit(){ return "RIPLIMIT";}
    static  function rU($uid){ return "RU|{$uid}";}
    static function rUid( $openid, $pf ) { return "UID|{$openid}|{$pf}"; } //用户信息key
    static function rMsg( $uid) { return "RMSG|{$uid}"; } //用户信息key
    static function rQuickLogin( $uid) { return "RQUICKLOGIN|{$uid}"; } //快捷登录
    static function rGuest( $uid) { return "GUEST|{$uid}"; } //快捷登录
    static function rSms( $str) { return "RSMS|{$str}"; } //快捷登录
    public static function rMailIdKey() { return 'RMAILIDKEY'; }
    public static function rHbSms($tuid) { return "RHBSMS|{$tuid}"; }
    public static function rHbcon($smsid) { return "RHBCON|{$smsid}"; }
    public static function rNoticeSms($tuid) { return "RNOTICESMS|{$tuid}"; }
    public static function rNoticeSmsCon($smsid) { return "RRNOTICESMSCON|{$smsid}";}
    public static function rNoticeReadTime($uid) { return "RRNOTICESMSCON|{$uid}";}
    public static function rBag($opt = []) {    if( is_array( $opt ) ) return "RBAG|" . implode( "|", $opt ); $args = func_get_args(); return 'RBAG|' . implode( "|", $args ); }//礼包
    public static function rPlayTime($uid,$date) { return "RPLAYTIME|{$uid}|{$date}"; }//在线玩牌时长
    public static function rFriend($fuid, $int) { return "RFRIEND|{$fuid}|{$int}"; }
    public static function rNoticeMsg($string) { return "RNOTICEMSG|{$string}"; }
    public static function rMsgIdKey() { return 'RMSGIDKEY'; }
    public static function rMkTop($type, $unid) { return "RTOP|{$type}|{$unid}"; }//排行榜
    public static function rRank() { return "RRANK"; }
    public static function rPrestigeTop($string, $uid) { return "RPRESTIGETOP|{$string}|{$uid}"; }
    public static function rUserInterPropList($uid) { return "RUSERINTERPROPLIST|{$uid}"; }
    public static function rChatRobot($uid) { return "RCHATROBOT|{$uid}";}
    public static function rChatRobotProp($uid, $type) { return "RCHATROBOTPROP|{$uid}|{$type}";}
    public static function rGood($type='', $key) { return "RGOOD|{$type}|$key"; }

    public static function rVipLevelSetting() { return "VIPLEVELSETTING"; }//VIP等级设置
    public static function rLevelPrivilegeSetting() { return "LEVELPRIVILEGESETTING"; }//等级特权设置
    public static function rCumulativeRechargeSetting() { return "CUMULATIVERECHARGESETTING"; }//累计充值福利设置表

    public static function rLt($uid, $ltype) { return "RLT|{$uid}|{$ltype}"; }//限量使用key
    public static function rLimit( $uid, $date ){ return 'RLIMIT|' . $uid . '|' . $date; }


    public static function mFirstLogin($uid){return "MFIRSTLOGIN|$uid";}
    public static function mOnline($uid){return "MONLINE|$uid";}
    public static function mGameData($id, $uid) { return "MGAMEDATA|{$id}|{$uid}"; }
    public static function mSendMailLimit($uid) { return "mSendMailLimit|{$uid}"; }

    public static function test($type){return "KEYFLAG|$type";}

    /**
     * -----------------------------------首登功能相关缓存配置 start -------------------------------
     */
    //存放每日奖励列表  return array
    public static function rDailyGiftList($uid,$day='')
    {
        if(!$day)
        {
            $day =date("Ymd",time());
        }
        return "SDAILYGIFTLIST|{$uid}|{$day}";
    }
    //存放月积累奖励列表  return array
    public static function rMonthAccumGift($uid,$month='')
    {
        if(!$month)
        {
            $month =date("Ym",time());
        }
        return "SMONTHACCUMGIFT|{$uid}|{$month}";
    }
    //return array
    public static function giftMonthAccumSetting()
    {
        return "SGIFTMONTHACCUMSETTING";
    }
    //return array
    public static function rActiveValueSetting()
    {
        return 'SACTIVEVALUESETTING';
    }
    //return array
    public static function rGiftContentSetting()
    {
        return 'SGIFTCONTENTSETTING';
    }
    //return array
    public static function rGiftCritRateSetting()
    {
        return 'SGIFTCRITRATESETTING';
    }
    //return array
    public static function rGiftDailySetting()
    {
        return 'RGIFTDAILYSETTING';
    }
    //return array
    public static function rGiftMonthSetting()
    {
        return 'RGIFTMONTHSETTING';
    }
    //用户当月活跃值缓存 int
    public static  function rUserActiveValues($uid,$month_accum_id,$month)
    {
        return "RUSERACTIVEVALUES|$uid|$month_accum_id|$month";
    }
    //用户当月签到天数 int
    public static  function rUserActiveDays($uid,$month_accum_id,$month)
    {
        return "RUSERACTIVEDAYS|$uid|$month_accum_id|$month";
    }
    //用户当月的user_active_value一行记录，return array
    public static function rActiveValuesAndDays($uid,$month_accum_id,$month)
    {
        return "R_USER_ACTIVE_VALUES_AND_DAYS|$uid|$month_accum_id|$month";
    }
    //return int
    public static function rReceiveGiftStatus($uid,$month_accum_id,$month)
    {
        return "R_USER_RECEIVE_GIFT_STATUS|$uid|$month_accum_id|$month";
    }
    //return int
    public static function rUserActiveDaysById($id)
    {
        return "RUSERACTIVEDAYSBYID|$id";
    }
    //return int
    public static function rUserActiveValuesById($id)
    {
        return "RUSERACTIVEVALUESBYID|$id";
    }
    //return int
    public static function rUserCheckInDay($uid,$day)
    {
        return "RUSERCHECKINDAY|$uid|$day";
    }
    //return int
    public static function rUserMonthGiftStatus($uid,$gift_month_accum_id,$user_active_value_id)
    {
        return "RUSER_MONTH_GIFT_STATUS|$uid|$gift_month_accum_id|$user_active_value_id";
    }
    //return int
    public static function rExistMonthGift($uid,$user_active_value_id)
    {
        return "REXIST_MONTH_GIFT|$uid|$user_active_value_id";
    }
    //return int
    public static function rExistDailyGift($uid,$gift_daily_id,$day_nd)
    {
        return "REXIST_DAILY_GIFT|$uid|$gift_daily_id|$day_nd";
    }
//* above all-----------------------------------首登缓存配置 end  -------------------------------

    /*************************** 用户特权福利--begin *********************************************/

    //用户当天生效中的特权扩展信息
    public static function rUserPrivilegeExt($uid,$day = '')
    {
        if(!$day){//day must be 20170824
            $day = date("Ymd",time());
        }
        return "USERPRIVILEGEEXT|{$uid}|{$day}";
    }

    //用户特权扩展公共信息
    public static function rUserPrivilegeCommonExt($uid)
    {
        return "USERPRIVILEGECOMMONEXT|{$uid}";
    }

    //累充福利金币补满
    public static function rPrivilegeChipFull($uid)
    {
        $day = date("Ymd",time());
        return "PRIVILEGECHIPFULL|{$uid}|{$day}";
    }

    //累充福利金币赠送
    public static function rPrivilegeGetChip($uid,$mType)
    {
        $day = date("Ymd",time());
        return "PRIVILEGEGETCHIP|{$uid}|{$day}|{$mType}";
    }

    //用户充值回调锁-暂时
    public static function rAsDoneCharge($uid,$orderId)
    {
        return "ASDONECHARGE|{$uid}|{$orderId}";
    }
    //任务队列名称
    public static function rTaskList()
    {
        return 'LIST_ACTIVITY_TASK';
    }
    public static function ActivityList()
    {
        return 'AT_0';
    }
    public static function getNoticeList($sid,$version)
    {
        return "AT_1|$sid|$version";
    }
    public static function getActivityTabSetting()
    {
        return 'AT_2';
    }
    public static function ConsumptionConfig($id)
    {
        return "AT_3|{$id}";
    }

    public static function CrossChallegeConfig($id)
    {
        return "AT_4|{$id}";
    }
    public static function FunctionConfig($id)
    {
        return "AT_5|{$id}";
    }
    public static function CrossChallegeGift()
    {
        return "AT_6";
    }
    public static function ConsumptionGift()
    {
        return "AT_7";
    }
    public static function FunctionGift($activity_id)
    {
        return "AT_8|{$activity_id}";
    }

    public static function SocialConfig()
    {
        return "AT_9";
    }

    public static function SocialGift($activity_id)
    {
        return "AT_10|{$activity_id}";
    }
    public static function UserConsumptionLog($uid,$activity_id,$add_time='')
    {
        return "AT_11|{$uid}|{$activity_id}|{$add_time}";
    }

    public static function UserExchangeNum($uid,$activity_id,$month_time)
    {
        return "AT_12|{$uid}|{$activity_id}|{$month_time}";
    }
    public static function UserSocialLog($uid,$activity_id,$add_time='')
    {
        return "AT_13|{$uid}|{$activity_id}|{$add_time}";
    }
    //
    public static function UserFunctionLog($uid,$activity_id,$add_time='')
    {
        return "AT_14|{$uid}|{$activity_id}|{$add_time}";
    }
    public static function rWinGiftStatus($uid,$room_token,$num)
    {
        return "AT_15|{$uid}|{$room_token}|{$num}";
    }
    public static function rWinStreakList()
    {
        return "AT_16";
    }
    public static function rUserFirstTurn($uid,$date)
    {
        return "AT_17|{$uid}|{$date}";
    }
    public static function rLuckyValuesList()
    {
        return "AT_18";
    }
    public static function rTurnUserLogList()
    {
        return "AT_19";
    }
    public static function rTurnGiftsUserList()
    {
        return "AT_T2000";
    }
    public static function rUserLuckyValue($uid,$gift_setting_id)
    {
        return "AT_21|$uid|$gift_setting_id";
    }
    public static function rTurnGiftSetting()
    {
        return "AT_22";
    }
    public static function rTurnLotterySetting()
    {
        return "AT_23";
    }
    public static function isFirstTurn($uid,$day)
    {
        return "AT_24|$uid|$day";
    }
    //道具使用
    public static function rUserTool($uid,$string) { return "RUSERTOOL|{$string}|$uid"; }

    public static function getChannelList()
    {
        return "AT_25";
    }
    public static function getControlList()
    {
        return "AT_26";
    }
    //控制--游戏app版本列表
    public static function rControlAppList($sid) { return 'CONTROL_APP_LIST|'.$sid; }//string
    //控制--渠道设置
    public static function rControlStoreSetting($app_id) { return 'CONTROL_STORE_SETTING|'.$app_id; }//string
    //控制--游戏app版本列表
    public static function rControlAreaSetting($app_id) { return 'CONTROL_AREA_SETTING|'.$app_id; }//string

    public static function ActivityCatById($id)
    {
        return "AT_27|$id";
    }
    public static function ActivityCatByActivityId($activity_id)
    {
        return "AT_28|$activity_id";
    }
    public static function ConsumptionGiftByActivityId($activity_id)
    {
        return "AT_29|$activity_id";
    }
    public static function CrossChallegeGiftByActivityId($activity_id)
    {
        return "AT_30|$activity_id";
    }
    public static function ConsumptionGiftByTabId($tab_id)
    {
        return "AT_31|$tab_id";
    }
    public static function CrossChallegeGiftByTabId($tab_id)
    {
        return "AT_32|$tab_id";
    }
    public static function UserCrossChallegeLog($uid,$activity_id,$add_time='')
    {
        return "AT_33|{$uid}|{$activity_id}|{$add_time}";
    }
    public static function UserDiamondNum($uid,$activity_id,$month_time)
    {
        return "AT_34|{$uid}|{$activity_id}|{$month_time}";
    }
    public static function UserRemindcardNum($uid,$activity_id,$month_time)
    {
        return "AT_35|{$uid}|{$activity_id}|{$month_time}";
    }
    public static function UserRoomcardNum($uid,$activity_id,$month_time)
    {
        return "AT_36|{$uid}|{$activity_id}|{$month_time}";
    }
    public static function UserJbcarNum($uid,$activity_id,$month_time)
    {
        return "AT_37|{$uid}|{$activity_id}|{$month_time}";
    }
    public static function CrossBaseIds()
    {
        return "AT_38";
    }
    public static function ConsumptionBaseIds()
    {
        return "AT_39";
    }

    //控制-时间策略
    public static function rControlTimeCommonSetting() { return 'CONTROL_TIME_COMMON_SETTING'; }//string
} 