<?php
/**
 * Created by PhpStorm.
 * User: nihao
 * Date: 17-8-25
 * Time: 下午2:34
 */

namespace Workerman\Model;


use Workerman\Lib\Okey;
use Workerman\Lib\Logger;
use Workerman\Lib\Model;
use Workerman\Lib\Redis;
use Workerman\Service\ATCode;

class GiftContentSetting extends Model{
    public $db = 'activity';
    public $table = 'gift_content_setting';
    const STATUS_ON=1;
    const STATUS_OFF=2;
    const CAN_CAL_DR_YES=1;//可计算倍率
    const CAN_CAL_DR_NO=2;//不可计算倍率

    const GIFT_TYPE_MONEY=1;//金币
    const GIFT_TYPE_SUB_CARD=113;//分牌卡I
    const GIFT_TYPE_SUB_CARD_II=114;//分牌卡II
    const GIFT_TYPE_SUB_CARD_III=115;//分牌卡III

    const GIFT_TYPE_REMENBCARD=110;//计牌器I
    const GIFT_TYPE_REMENBCARD_II=111;//计牌器II
    const GIFT_TYPE_REMENBCARD_III=112;//计牌器III
    const GIFT_TYPE_EXPRESSION_PACK=4;//表情包
    const GIFT_TYPE_SCENES=5;//场景
    const GIFT_TYPE_DIAMAND=2;//钻石
    const GIFT_TYPE_LOTTERY=7;//奖券
    const GIFT_TYPE_ROOMCARD=3;//房卡
    const GIFT_TYPE_JBCARD=117;//禁比卡
    public function __construct($db='')
    {
        parent::__construct($db);

    }
    public function rules()
    {
        return [
            ['name','required'],
            ['id','required'],
        ];
    }

    public function add($data)
    {
        if(!$this->validate($data,$this->rules()))
        {
            Logger::write('参数验证失败','GiftContentSetting add ','error');
            return false;
        }
        $this->rmCache();
        return $this->insert($this->table,$data);
    }
    public function setStatusOn($id)
    {
        if($id<=0)
            return false;
        $this->rmCache();
        return $this->update($this->table,['status'=>self::STATUS_ON],"id=:id",[":id"=>$id]);
    }
    public function setStatusOff($id)
    {
        if($id<=0)
            return false;
        $this->rmCache();
        return $this->update($this->table,['status'=>self::STATUS_OFF],"id=:id",[":id"=>$id]);
    }

    /**
     * @desc 删除项
     * @param string $id
     * @return bool
     */
    public function deleteById($id)
    {
        $this->rmCache();
        return $this->delete($this->table,"id=:id",[":id"=>$id]);
    }

    /**
     * @desc 删除相关缓存
     */
    public function rmCache()
    {
        ATCode::rmCache(Okey::rGiftContentSetting());
    }
    public function getList($ids=array())
    {
        $all = $this->getAll();
        foreach($all as $item)
        {
            if(in_array($item['id'],$ids))
            {
                $result[]=$item;
            }
        }
        return $result;
    }
    public function getOne($id=0)
    {
        $all = $this->getAll();
        $result=[];
        foreach($all as $item)
        {
            if($id == $item['id'])
            {
                $result=$item;break;
            }
        }

        return $result;
    }
    public function getAll()
    {
        $result = ATCode::getCache(Okey::rGiftContentSetting(),function(){
            $result =  $this->getRows($this->table);
            return $result?$result:false;
        });
        return $result;
    }

    /**
     * 判断奖品项是否可计算倍率
     */
    public function canCalDR($id)
    {
        $r = $this->getOne($id);
        if($r && $r['can_cal_rate'] == self::CAN_CAL_DR_YES)
            return true;
        return false;
    }
} 