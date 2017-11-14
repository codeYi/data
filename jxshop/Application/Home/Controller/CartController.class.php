<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/10/11
 * Time: 16:52
 */
namespace Home\Controller;
class CartController extends CommonController
{
    //实现商品添加到购物车
    public function  addCart()
    {
        $goods_id = intval(I('post.goods_id'));
        $goods_count = intval(I('post.goods_count'));
        $attr = I('post.attr');
        //实例化模型对象调用方法写入数据
        $model = D('Cart');
        $res = $model->addCart($goods_id,$goods_count,$attr);
        if(!$res){
            $this->error($model->getError());
        }
        $this->success('写入成功');


    }

    //实现购物车显示功能
    public function index()
    {
        $model = D('Cart');
        //获取购物车中的具体商品信息
        $data =$model->getList();

        //计算当前购物车总额
        $total = $model->getTotal($data);
        $this->assign('total',$total);
        $this->assign('data',$data);
        $this->display();

    }

    //删除购物车的数据
    public function  dels()
    {
        $goods_id = intval(I('get.goods_id'));
        $goods_attr_ids = I('get.goods_attr_ids');
        D('Cart')->dels($goods_id,$goods_attr_ids);

        $this->success('删除成功');
    }

    //数据库点击增加、减少
    public function updateCount()
    {
        $goods_id = intval(I('post.goods_id'));
        $goods_count = intval(I('post.goods_count'));
        $goods_attr_ids = I('post.goods_attr_ids');
        D('Cart')->updateCount($goods_id,$goods_attr_ids,$goods_count);

    }
    public function test()
    {
        dump($_COOKIE);
    }

}