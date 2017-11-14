<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/10/9
 * Time: 23:50
 * 商品详情控制器
 */
namespace Home\Controller;
class MemberController extends CommonController
{
    public function __construct()
    {
        parent::__construct();
        //登录验证
        $this->checkLogin();
    }

    //显示订单
    public function order()
    {
        $user_id = session('user_id');
        $data = D('Order')->where('user_id='.$user_id)->select();
        $this->assign('data',$data);
        $this->display();
    }

    //获取快递信息
    public function express()
    {
        $order_id = I('get.order_id');
        $info = M('Order')->where('id='.$order_id)->find();
        if(!$info || $info['order_status'] !=2) {
            $this->error('参数错误');
        }
        $url ='http://api.kuaidi100.com/api?id=e3673a585812e1a081a3a4a5a7066fe7&com='.$info['com'].'&nu=886619774389391490&show=2&muti=1&order=asc';
        $res = file_get_contents($url);
        $res = json_decode($res,true);
        $this->assign('data',$res);
        $this->display();
    }
}