<?php
namespace Home\Controller;
class IndexController extends CommonController
{
    public function index()
    {
        //控制是否展开分类
      $this->assign("is_show",1);

        //获取热卖商品信息
        $goodsModel = D('Admin/Goods');
        $hot =$goodsModel->getRecGoods('is_hot');

        //获取推荐商品信息
        $goodsModel = D('Admin/Goods');
        $rec =$goodsModel->getRecGoods('is_rec');

        //获取新品商品信息
        $goodsModel = D('Admin/Goods');
        $new =$goodsModel->getRecGoods('is_new');
        $this->assign('new',$new);
        $this->assign('rec',$rec);
        $this->assign('hot',$hot);

        //获取当前正在疯狂抢购的商品
        $crazy = $goodsModel->getCrazyGoods();
        $this->assign('crazy',$crazy);

        //获取楼层数据
        $floor = D('Admin/Category')->getFloor();
        $this->assign('floor',$floor);
        $this->display();
    }
}