<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/10/15
 * Time: 23:27
 */
namespace Admin\Controller;
class OrderController extends  CommonController
{
    //显示订单列表
    public function index()
    {
        //获取订单信息
        $data = M('Order')->select();
        $this->assign('data',$data);
        $this->display();
    }

    //实现订单发货的功能
    public function send()
    {
        if(IS_GET){
            $order_id = I('get.order_id');
            //根据订单号获取具体的订单数据
            $info = M('Order')->alias('a')->field('a.*,b.username')->join('left join jx_user b on b.id = a.user_id')->where('a.id='.$order_id)->find();
            $this->assign('info',$info);
            $this->display();
        }else{
            //实现发货操作
            $order_id = I('post.id');
            $info = M('Order')->where('id='.$order_id)->find();
            if(!$info || $info['pay_status'] !=1){
                $this->error('参数错误');
            }

            $data = array(
                'com'=>I('post.com'),
                'no'=>I('post.no'),
                'order_status'=>2,//设置订单已经处于发货状态
            );

            M('order')->where('id='.$order_id)->save($data);
            $this->success('发货成功');
        }
    }
}