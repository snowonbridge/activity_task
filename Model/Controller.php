<?php
namespace Simple;

use Simple\Helper\Code;
use Simple\Helper\Okey;

class Controller
{


    /**
     * Response format
     * 1 is json, 2 is raw
     *
     * @var int
     */
    protected $format = 1;

    /**
     * @var \Simple\Request
     */
    protected $request;

    /**
     * @var \Simple\Response
     */
    protected $response;

    private $loginSecret = '69fcdbe4f29e410dec535560106e50eb';//md5('sg20170725&*#@sg');

    //用时统计
    private $timeUse = 0;


    /**
     * Controller constructor.
     *
     * @param Request  $request
     * @param Response $response
     */
    function __construct(Request $request=null, Response $response=null)
    {
        $this->request = $request;

        $this->response = $response;
        $gameid = isset($request->post['gameid']) ?  $request->post['gameid'] : (isset($request->get['gameid']) ? $request->get['gameid'] : 0);
        $this->gameid = $gameid;
        $this->beforeAction();
    }

    protected function beforeAction(){

        if(PHP_SAPI == 'cli'){
            $this->timeUse = microtime(true);
            $req = isset($this->request->post) && is_array($this->request->post) ? $this->request->post : [];//just record post params
            Logger::write(['client_id'=>$this->request->client_id,'url'=>$this->request->uri->getPath(),'params'=>json_encode($req,JSON_UNESCAPED_UNICODE)],'paramsIn','INFO');
        }
    }

    protected function afterAction(){

        if(PHP_SAPI == 'cli'){
            $this->timeUse = microtime(true) - $this->timeUse;
            Logger::write(['client_id'=>$this->request->client_id,'url'=>$this->request->uri->getPath(),'time' => $this->timeUse],'execTime','INFO');
            Logger::write(['client_id'=>$this->request->client_id,'url'=>$this->request->uri->getPath(),'data'=>$this->response->getBody()],'resultData','INFO');
        }
    }

    /**
     * Parse data from request
     *
     * @param string $field    Field name
     * @param null   $default  Default value for this field
     * @param string $type     Possibles values of type are: "boolean" or "bool", "integer" or "int", "float" or
     *                         "double", "string", "array", "object", "null"
     *
     * @return mixed
     */
    protected function parse($field = '', $default = null, $type = 'integer')
    {
        $request = array_merge($this->request->post, $this->request->get);

        if (!isset($request[$field])) {
            self::setType($default, $type);
            return $default;
        }

        self::setType($request[$field], $type);
        return $request[$field];
    }

    /**
     * Set the type of a variable
     *
     * @param mixed  $var  The variable being converted.
     * @param string $type Possibles values of type are: "boolean" or "bool", "integer" or "int", "float" or "double",
     *                     "string", "array", "object", "null"
     */
    static function setType(&$var, $type = 'string')
            {
        switch ($type) {
            case 'bool':
            case 'boolean':
            case 'int':
            case 'integer':
            case 'float':
            case 'double':
            case 'string':
            case 'array':
            case 'object':
            case 'null':
                settype($var, $type);
                break;
            default:
                break;
        }
    }

    /**
     * Json response
     *
     * @param $code
     * @param array $data
     * @param int $status
     * @param string $msg
     * @return Response
     */
    public function json($code = 0 ,$msg = '',$data=[], $status = 200,$ext=array() )
    {
        $this->response->setHeader('Content-Type', 'application/json; charset=UTF-8');
        if (!isset(Response::$STATUS_MAP[$status])) {
            $status = 200;
        }
        $message = [
            'ret' => $status,
            'code'=>$code,
            'msg' => $msg,
            'data' => $data,
            'time'=>time()
        ];
        if($ext)
            $message = array_merge($message,$ext);
        $this->response->setStatus($status);
        $this->response->setBody(json_encode($message));
        $this->afterAction();
        return $this->response;
    }

    /**
     * Raw response
     *
     * @param string $msg
     * @param int    $status
     *
     * @return Response
     */
    public function raw($msg = '', $status = 200)
    {
        $this->response->setHeader('Content-Type', 'text/html; charset=UTF-8');

        if (!isset(Response::$STATUS_MAP[$status])) {
            $status = 200;
        }

        if (empty($msg)) {
            $msg = Response::$STATUS_MAP[$status];
        }

        $this->response->setStatus($status);
        $this->response->setBody($msg);
        $this->afterAction();
        return $this->response;
    }




    //第三方登录校验 签名串
    protected  function _getSign($params)
    {
        return md5($params['openid'] . $params['usertype'] . $params['time'] . '#' . $this->loginSecret);
    }


    /**
     * 验证客户端传过来的数据,测试环境不验证
     * @param array $req 客户端传过来的原始数据
     * @param int $level 验证等级.0,验证sig与在线,time.1不可以房间内.2防重复
     * @return int 验证结果,1成功 0失败或不在线,-1在房间内,-2重复请求太多太快
     */
    public function _checkSign($req)
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
        if(!DEBUG && !isset($req['time'])){
            return false;
        }
        if(isset($req['time']) && $req['time'] < time() - 60){
            return false;
        }
        $mkey = isset($req['mkey']) ? $req['mkey'] : '';
        $sSig = $this->_joins($req, $mkey);
        $flag = (strcmp($sign, md5($sSig)) === 0 ? 1 : 0);
        if (!$flag) {
            Logger::log(json_encode($req).'|' . $sSig  . '|' . md5($sSig) . '|' . $sign . '|', 'autherror.txt');
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
    public function _joins($arg, $mkey)
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
                    $str .= ($key . '=' . $this->_joins($value, $mkey));
                }
            }
        }
        return $str;
    }
    /**
     * @param int $level 是否验证用户在线信息
     * @return bool|\Simple\Response
     */
    protected  function _init($level = 1)
    {
        $req = isset($this->request->post) && is_array($this->request->post) ? $this->request->post : [];
        if (DEBUG) {
            Logger::log($req, 'requestPost.log');

        }
        if (!isset($req['mkey']) or !$req['mkey']) {
            return $this->json(Code::CODEERRPARAM, '参数错误');
        }

        $flag = $this->_checkSign($req);

        if (!$flag) {
            return $this->json(Code::CODENOTSIGNFAILED, '签名验证验失败');
        }
        $this->mkey = isset($req['mkey']) ? $req['mkey'] : '';
        $member = service('Member');

        $ret = \Simple\model\SwitchSetting::instance()->isVersionOpen($req['unid'],$req['version']);
        if(!$ret)
        {
            $this->json(Code::CODE_VERSION_UNAVAIL,Code::getCodeMsg(Code::CODE_VERSION_UNAVAIL),[]);
        }
        if ($level == 1) {
            if (!isset($req['uid']) or !$req['uid']) {
                return $this->json(Code::CODEERRPARAM, '参数错误');
            }
            $userparam = $member->getUGame($req['uid']);
            if (isset($userparam['uid'])) {
                $this->uid = $req['uid'];
            } else {
                return $this->json(Code::CODEUSERNOEXISTS, '用户不存在');
            }

            $online = service('Online');
            $flag = $online->auth($this->uid, $this->mkey, 0);
            if (!$flag) {
                return $this->json(Code::CODEUSERAUTHFAILED, '校验失败');
            }
        }

        $this->usertype = isset($req['usertype']) ? (int)$req['usertype'] : 1;
        $this->unid = isset($req['unid']) ? (int)$req['unid'] : 0;
        $this->sid = isset($req['sid']) ? (int)$req['sid'] : 0;
        $this->openid = isset($req['openid']) ? (int)$req['openid'] : 0;
        $this->system = isset( $req['os'] ) ?  $req['os'] : $member->getSystemType($this->unid);
        $this->version = empty($req['version']) ? '1.0.0':$req['version'];
        $version_array = explode('.', $this->version);
        $this->intversion = (int)implode('', $version_array);
        $this->param = isset($req['param']) && is_array($req['param']) ? $req['param'] : '';
        $appCfg = cfg('app');
        if (!$appCfg) {
            return $this->json(Code::CODEPLATFORMCFGMISSING, '平台配置缺失');
        }
        if (!in_array($req['unid'], $appCfg['unids'])) {
            return $this->json(Code::CODEUNIDNOTEXITS, '平台错误');
        }
        return true;
    }



}