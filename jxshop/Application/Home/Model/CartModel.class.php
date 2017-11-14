<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/10/11
 * Time: 19:30
 */
namespace Home\Model;
use Think\Model;

class CartModel extends Model
{
    //定义字段
    protected $fields = array('id','user_id','goods_id','goods_count','goods_attr_ids');

    public function addCart($goods_id,$goods_count,$attr)
    {
        sort($attr);
        //属性信息转换为字符串格式
        $goods_attr_ids =$attr?implode(',',$attr):'';

        //实现库存检查
        $res = $this->checkGoodsNumber($goods_id,$goods_count,$goods_attr_ids);
        if(!$res){
            $this->error='库存不足';
            return false;
        }

        $user_id = session("user_id");
        if($user_id){
        //用户已登录
            $map = array(
                'user_id'=>$user_id,
                'goods_id'=>$goods_id,
                'goods_attr_ids'=>$goods_attr_ids
            );
            $info = $this->where($map)->find();
            if($info){
                //说明目前数据已经存在了。只需要更新即可
                $this->where($map)->setField('goods_count',$goods_count+$info['goods_count']);
            }else{
                //说明目前数据不存在
                $map['goods_count'] = $goods_count;
                $this->add($map);
            }
        }else{
            //没有登录时
            //反序列化获取购物车中的数据
            $cart = unserialize(cookie('cart'));
            //拼接下表，判断其是否在购物车已经存在
                $key = $goods_id."-".$goods_attr_ids;
                if(array_key_exists($key,$cart)){
                    //存在
                    $cart[$key] += $goods_count;
                }else{
                    //不存在
                    $cart[$key] =  $goods_count;
            }

            //将最新的数据写入到cookie中
            cookie('cart',serialize($cart));
        }
        return true;
    }


    //检查库存
    public function checkGoodsNumber($goods_id,$goods_count,$goods_attr_ids)
    {
        //1.检查总的库存量
        $goods = D('Admin/Goods')->where("id=$goods_id")->find();
        if($goods['goods_number']<$goods_count){
            //库存不够
            return false;
        }

        //根据单选属性检查
        if($goods_attr_ids){
            $where = "goods_id = $goods_id and goods_attr_ids = '$goods_attr_ids'";
            $number = M("GoodsNumber")->where($where)->find();
            if(!$number || $number['goods_number']<$goods_count){
                //表示库存不够
                return false;
            }
        }
        return true;
    }

    //具体实现购物车cookie的数据转移到数据库中
    public function cookie2db()
    {
        //获取cookie中购物车数据
        $cart = unserialize(cookie('cart'));
        //获取当前用户的ID标识
        $user_id = session('user_id');
        if(!$user_id){
            return false;
        }

        foreach($cart as $key=>$value){
            //拆分下标
            $tmp = explode('-',$key);
            //拼接条件查询当前当前商品信息是否存在
            $map = array(
                'user_id'=>$user_id,
                'goods_id'=>$tmp[0],
                'goods_attr_ids'=>$tmp[1]
            );

            $info = $this->where($map)->find();
            if($info){
                //存在
                $this->where($map)->setField('goods_count',$value+$info['goods_count']);
            }else{
                //不存在
                $map['goods_count'] = $value;
                $this->add($map);
            }
        }
        //写入到数据库
        cookie('cart',null);
    }

    //获取购物车中的商品具体信息
    public function  getList()
    {
        //1.获取当前购物车中的信息
        $user_id = session('user_id');
        if($user_id){
            //用户已登录
            $data = $this->where('user_id='.$user_id)->select();
        }else{
            //直接从cookie中获取对应的购物车中的数据
            $cart = unserialize(cookie('cart'));
            //将没有登录的购物车数据转换为数据库中格式
            foreach($cart as $key=>$value){
                $tmp = explode('-',$key);
                $data[] = array(
                    'goods_id'=>$tmp[0],
                    'goods_attr_ids'=>$tmp[1],
                    'goods_count'=>$value
                );
            }
        }

        //根据购物车中的商品的id获取商品信息
        $goodsModel = D('Admin/Goods');
        foreach($data as $key=>$value){
            //获取具体的商品信息
            $goods = $goodsModel->where('id='.$value['goods_id'])->find();
            //根据商品是否处于促销状态设置价格
            if($goods['cx_price']>0 && $goods['start']<time() && $goods['end']>time()){
            //处于促销状态，将商品价格设置为促销价
                $goods['shop_price'] =$goods['cx_price'];
            }
            $data[$key]['goods'] = $goods;
        //3,根据商品对应的属性值得组合获取对应的属性值
            if($value['goods_attr_ids']){
                //获取商品的属性信息
                $attr = M('GoodsAttr')->alias('a')->join('left join jx_attribute b on a.attr_id = b.id')->field('a.attr_values,b.attr_name')->where("a.id in ({$value['goods_attr_ids']})")->select();
            $data[$key]['attr'] = $attr;
            }
        }
        return $data;
    }

    //计算购物车总额
    public function getTotal($data)
    {
        //初始商品个数和总金额
        $count = $price = 0;
        foreach($data as $key=>$value){
            $count +=$value['goods_count'];
            $price +=$value['goods_count']*$value['goods']['shop_price'];
        }
        return array('count'=>$count,'price'=>$price);
    }

    //删除购物车的商品
    public function  dels($goods_id,$goods_attr_ids)
    {
        //判断商品属性id是否存在
        $goods_attr_ids = $goods_attr_ids?$goods_attr_ids : '';
        $user_id = session('user_id');
        //判断是否处于登录状态
        if($user_id){
            //登录
            $where = "user_id = $user_id and goods_id = $goods_id and goods_attr_ids='$goods_attr_ids'";
             $this->where($where)->delete();
        }else{
            //没有登录
            $cart = unserialize(cookie('cart'));
            //拼接当前商品对应的key信息
            $key = $goods_id.'-'.$goods_attr_ids;
            unset($cart[$key]);
            //将最新的数据写入到cookie中
            cookie('cart',serialize($cart));
        }

    }

    //更新购物车
      public function updateCount($goods_id,$goods_attr_ids,$goods_count)
      {
          if($goods_count<=0){
              return false;
          }
          $user_id = session('user_id');
          //判断是否处于登录状态
          if($user_id){
              //登录
              $where = "user_id = $user_id and goods_id = $goods_id and goods_attr_ids=$goods_attr_ids";
              $this->where($where)->setField('goods_count',$goods_count);
          }else{
              //没有登录
              $cart = unserialize(cookie('cart'));
              //拼接当前商品对应的key信息
              $key = $goods_id.'-'.$goods_attr_ids;
              $cart[$key] = $goods_count;
              //将最新的数据写入到cookie中
              cookie('cart',serialize($cart));
          }
      }
}