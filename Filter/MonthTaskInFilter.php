<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 2018/1/12
 * Time: 14:44
 */
namespace Workerman\Filter;
use Workerman\Lib\Model;
use Workerman\Service\ATCode;
use Workerman\Model\FirstChargeActivity;
use Workerman\Lib\Logger;


class MonthTaskInFilter extends Model{

    /**
     * @desc 执行过滤参数处理
     * @param $params
     * @return bool|object
     */
    public function filter($params)
    {
        $params =  $this->firstChargeTask($params);
        return $params;
    }

    /**
     * @desc 判断是否首充或转运大礼包的场景

     */
    public function isFirstChargeScene($scene_id)
    {
        return $scene_id == ATCode::SCENE_ID_FIRSTCHARGE?true:false;
    }
    /**
     * @desc 处理首充礼包的对应的活动
     * @param $params object
     * @param $params:scene_id
     * @param $params:id 首充或转运唯一ID对应
     */
    public function firstChargeTask($params)
    {
        $id = $params['id'];
        $scene_id = $params['scene_id'];
        if($this->isFirstChargeScene($scene_id))
        {
            $r = FirstChargeActivity::instance()->getOne($id);
            if(!$r){
                Logger::write(json_encode($params),__METHOD__.'首充转运参数异常',"ERROR");
                return false;
            }
            $money =$r['money'];
            $diamonds = ATCode::money2Diamond($money);

            $newParams['uid'] = $params['uid'];
            $newParams['from_gift_id'] = ATCode::GIFT_DIAMOND;
            $newParams['action_id'] = ATCode::ACTION_PURCHASE;
            $newParams['target_gift_id'] = ATCode::GIFT_GOLDCOIN;
            $newParams['magic'] = 2;//忽略
            $newParams['from_num'] = $diamonds;
            $newParams['login_check'] = 2;//忽略
            Logger::write(json_encode($newParams),__METHOD__.'首充转运转换参数',"INFO");
            return $newParams;
        }
        return $params;
    }

}