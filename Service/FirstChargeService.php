<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/3/28
 * Time: 10:49
 */

namespace Workerman\Service;


use Workerman\Lib\Code;
use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Model\FirstchargeSetting;
use Workerman\Model\UserFirstchargeLog;

class FirstChargeService extends Model
{
    public function get($uid,$platform_id,$channel_id)
    {
        if(!$uid || !$platform_id || !$channel_id)
        {
            Logger::write("参数非法",__METHOD__,"ERROR");
            return ['code'=>Code::CODEERRPARAM,'msg'=>"参数非法 ".json_encode(func_get_args()),'data'=>[]];
        }
        if(UserFirstchargeLog::instance()->has($uid))
        {
            Logger::write('已购买过首充'.json_encode(func_get_args()),__METHOD__,"ERROR");
            return ['code'=>Code::NOT_ALLOWED_OPERATION,'msg'=>"已购买过首充",'data'=>[]];
        }

        $result = FirstchargeSetting::instance()->getOne($channel_id,$platform_id);
        $gift_list=[];
        foreach ($result['base_gift_content'] as $item)
        {
            $gift_list[$item['id']] = $item;
            $gift_list[$item['id']]['extra_flag'] = 0;
        }
        foreach ($result['extra_gift_content'] as $k=>$item)
        {

            if(isset($gift_list[$item['id']]))
            {
               $gift_list[$item['id']]['num'] +=$item['num'];
            }else{
               $gift_list[$item['id']] = $item;
            }
            $gift_list[$item['id']]['extra_flag'] = 1;

        }
        unset($result['id'],$result['channel_id'],$result['platform_id'],$result['extra_gift_content'],$result['base_gift_content']);
        $result['gift_list'] = array_values($gift_list);
        usort($result['gift_list'],function ($a,$b){
            if($a['extra_flag'] == $b['extra_flag'])
                return 0;
            return $a['extra_flag']<$b['extra_flag']?0:1;
        });
        return ['code'=>Code::SUCCESS,'msg'=>"获取首充配置",'data'=>$result];
    }





}