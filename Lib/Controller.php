<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-9-11
 * Time: 上午11:19
 */

namespace Workerman\Lib;


use Workerman\Model\User;
use Workerman\Protocols\Http;
use Workerman\Service\ATCode;

class Controller {

    public $post=array();
    public $get =array();
    public $uid = 0;
    public $sid = 0;
    public $mid = 0;
    public $openid = 0; //平台id
    public $unid = 0; //渠道
    public $usertype = 0; //登录方式
    public $system = 2; //系统 1ios 2 android
    public $version = '1.0';
    public $intversion = 10;
    public $mkey = ''; //在线key
    public $param = [];
    public $platform = [
        'ios' =>
            [
                2 => '微信公众号',
            ],
        'android' =>
            [
                1 => '应用宝',
                3 => '百度',
            ],
        'pc' =>
            [
                4 => '百度',
            ]
    ];
    public  $unids =  [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36,];

    const CROSS_CHALLEGE_TYPE = 1;
    const CONSUMPTION_TYPE = 2;
    const FUNCTION_TYPE = 3;
    const SOCIAL_TYPE = 6;
    public function __construct()
    {

        foreach($_POST as $k=>$v)
        {
            $this->post[$k] = ($v);
        }
        foreach($_GET as $k=>$v)
        {
            $this->get[$k] = ($v);
        }
        //json 格式穿过来才验证数据 方便本地测试
        if(defined('IS_WEB_SERVER'))
            $this->init();

    }
    /**
     * @desc 关卡类活动
     * @param $params
     * @param int $params:uid
     * @param params:activity_id 活动编号
     * @param params:game_no  游戏编号  游戏名称-玩法-经典场
     * @param params:win_result 是否赢
     * @param params:friends_num 好友数量
     * @param params:own_open_room 自己开房@1y,0n,2不验证
     */
    public function  isCrossScenes($params)
    {
        if(isset($params['game_no'])
            && isset($params['uid'])
            && isset($params['win_result'])
            && isset($params['friends_num'])
            && isset($params['own_open_room']))
        {
            return true;
        }

        return false;
    }
    /**
     * @desc 消费类活动
     * @param int $params:uid
     * @param int $params:from_gift_id 消费的道具,货币id
     * @param int $params:action_id  消费活动id，参考excel的消费活动sheet
     * @param int $params:target_gift_id 兑换的目标id(如金币id，房卡 id)
     * @param int $params:from_num  action_id的数量
     * @param int $params:user_level  用户等级  1：A1,2:B1会员与A2会员  3:B2会员  ，0无限制
     * @param int $params:magic  是否使用了魔法表情。@1y,0 n,2 忽略
     * @param int $params:login_check  登录检测，即登录调用。@1 y,0 n,2 忽略
     */
    public function  isConsumptionScenes($params)
    {
        if(isset($params['uid'])
            && isset($params['from_gift_id'])
            && isset($params['action_id'])
            && isset($params['target_gift_id'])
            && isset($params['from_num'])
            && isset($params['magic'])
            && isset($params['login_check'])
            )
        {
            return true;
        }
        return false;
    }
    /**
     * @desc 功能类活动
     * @param int $params:uid
     * @param int $params:counts  次数（每日分享，五星好评）
     */
    public function  isFunctionScenes($params)
    {
        if(isset($params['uid']))
        {
            return true;
        }
        return false;
    }
    /**
     *  @desc 社交类活动
     * @param array $params
     * @param int $params:uid
     * @param int $params:counts 次数
     * @param int $params:friend_relation  是否需要是好友@1y,0n,2忽略

     * @return bool
     */
    public function  isSocialScenes($params)
    {
        if(isset($params['uid'])
            && isset($params['friend_relation'])
        )
        {
            return true;
        }
        return false;
    }
    /**
     * @param array $params
     * @param int $params:uid
     * @param int $params:online_time 在线时间(分钟)
     * @param int $params:user_level 用户等级  1：A1,2:B1会员与A2会员  3:B2会员  ，0无限制
     * @return bool
     */
    public function isOnlineScenes()
    {
        return true;
    }

    protected  function init($level = 1)
    {
        $req = $this->post?$this->post:$this->get;

        if(isset($this->get['uid']))
        {
            $this->uid = $this->get['uid'];
            $this->sid =  $this->get['sid']?:ATCode::PLATFORM_ONLINE;
            //sid最大为127
            $this->mid = $this->get['mid'];
            return true;
        }
        if(!DEBUG)
        {
            if (!isset($req['mkey']) or !$req['mkey']) {
                Http::end(json_encode(['code'=>Code::CODEERRPARAM, '参数错误1'])) ;
            }
        }


        if ($level == 1) {
            if (!DEBUG && (!isset($req['uid']) or !$req['uid'])) {
                Http::end(json_encode(['code'=>Code::CODEERRPARAM, '参数错误2'])) ;
            }

            $userparam = User::instance()->getUBase($req['uid']);
            if (isset($userparam['uid'])) {
                $this->uid = $userparam['uid'];
            }
            if(!$this->uid) {
                Http::end(json_encode(['code'=>Code::CODEUSERNOEXISTS, '用户不存在'])) ;
            }

        }

        $this->usertype = isset($req['usertype']) ? (int)$req['usertype'] : 1;
        $this->unid = isset($req['unid']) ? (int)$req['unid'] : 0;
        $this->sid = isset($req['sid']) ? (int)$req['sid'] : ATCode::PLATFORM_ONLINE;
        //sid最大为127
        $this->mid = $req['mid'];

        $this->openid = isset($req['openid']) ? (int)$req['openid'] : 0;
        $this->system = $this->getSystemType($this->unid);
        $this->version = isset($req['version']) ? $req['version'] : '';
        $version_array = explode('.', $this->version);
        $this->intversion = (int)implode('', $version_array);
        $this->mkey = isset($req['mkey']) ? $req['mkey'] : '';
        $this->param = isset($req['param']) && is_array($req['param']) ? $req['param'] : '';


        if (!in_array($req['unid'], $this->unids)) {
            Http::end(json_encode(['code'=>Code::CODEUNIDNOTEXITS, '平台错误'])) ;
        }
        $flag = $this->checkSign($req);

        if (!$flag) {
            Http::end(json_encode(['code'=>Code::CODENOTSIGNFAILED, '签名验证验失败'])) ;
        }
        return true;
    }
    //根据pf系统1ios2android3pc
    public function getSystemType($unid)
    {

        $os = '';
        foreach ($this->platform as $k => $v) {
            if (isset($v[$unid])) {
                $os = $k;
                break;
            }
        }
        switch ($os) {
            case 'ios':
                $system = 1;
                break;
            case 'android':
                $system = 2;
                break;
            case 'pc':
                $system = 3;
                break;
            default:
                $system = 2;
                break;
        }
        return $system;
    }
    private  function checkSign($req)
    {
        $req = (array)$req;
        if (DEBUG === true) {//TODO
            return 1;
        }
        if (!isset($req['sign'])) {
            return false;
        }
        $sign = $req['sign'];
        unset($req['sign']);
        $sSig = $this->joins($req, (string)$req['mkey']);
        $flag = (strcmp($sign, md5($sSig)) === 0 ? 1 : 0);
        if (!$flag) {
            Logger::write(var_export($req, true) . '|' . md5($sSig) . '|' . $sign . '|', __METHOD__,"ERROR");
        }
        return $flag;
    }
    /**
     * 私有方法来组合数组对象
     *
     * @param array $arg
     * @param string $mkey
     * @return string
     */
    private  function joins($arg, $mkey)
    {
        $str = '';
        if (!is_object($arg)) {
            if (is_null($arg) || is_bool($arg)) {
                $str .= '';
            } else if (is_string($arg) || is_numeric($arg)) {
                $str .= 'X' . $mkey . $arg;
            } else {
                ksort($arg, SORT_STRING);
                foreach ($arg as $key => $value) {
                    $str .= ($key . '=' . $this->joins($value, $mkey));
                }
            }
        }
        return $str;
    }
    /**
     * Get simple instance
     *@return static
     */
    public static function instance()
    {
        if (!isset(self::$instance[get_called_class()])) {
            self::$instance[get_called_class()] = new static();
        }

        return self::$instance[get_called_class()];
    }
    public static $instance;
} 