<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/10/11
 * Time: 14:25
 */
namespace Home\Model;
use Think\Model;

class OrderModel extends Model
{
    public function order()
    {

    //1.获取购物车中的商品
        $cateModel = D('Cart');
        $data = $cateModel->getList();
        if(!$data){
            $this->error='购物车中没有商品';
            return false;
        }
    //2.根据每一个商品做一个库存检查
        foreach($data as $key=>$value){
            //具体这对每一个商品检查库存
            $status = $cateModel->checkGoodsNumber($value['goods_id'],$value['goods_count'],$value['goods_attr_ids']);
            if(!$status){
                $this->error='库存量不足';
            }
        }
    //3.向订单总表中写入具体的数据
        //计算购物车中的总价格
        $total = $cateModel->getTotal($data);
        $order = array(
            'user_id'=>session('user_id'),
            'addtime'=>time(),
            'total_price'=>$total['price'],
            'name'=>I('post.name'),
            'address'=>I('post.address'),
            'tel'=>I('post.tel'),
        );
        $order_id = $this->add($order);
    //4.象商品订单详情表中写入具体的数据
        foreach($data as $key=>$value){
            $goods_order[] = array(
                'order_id'=>$order_id,
                'goods_id'=>$value['goods_id'],
                'goods_attr_ids'=>$value['goods_attr_ids'],
                'price'=>$value['goods']['shop_price'],
                'goods_count'=>$value['goods_count']
            );
        }

        M('OrderGoods')->addAll($goods_order);
    //5.减少商品对应的库存量

        foreach($data as $key=>$value){
            //1.减少商品表中的总库存
            M('Goods')->where('id='.$value['goods_id'])->setDec('goods_number',$value['goods_count']);
            //增加商品表中的销量
            M('Goods')->where('id='.$value['goods_id'])->setInc('goods_number',$value['goods_count']);
            //2.根据商品的单选属性组合减少对应的库存
            if($value['goods_attr_ids']){
                $where = 'goods_id='.$value['goods_id'].' and goods_attr_ids='."'".$value['goods_attr_ids']."'";

                M('GoodsNumber')->where($where)->setDec('goods_number',$value['goods_count']);
            }
        }

    //6.清空购物车中的数据
        $user_id = session('user_id');
        $cateModel->where('user_id='.$user_id)->delete();
        $order['id'] = $order_id;
        return $order;
    }

}
