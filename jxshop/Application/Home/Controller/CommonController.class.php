<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/10/9
 * Time: 20:53  公共控制器
 */
namespace Home\Controller;
use Think\Controller;

class CommonController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $cate = D('Admin/Category')->getCateTree();
        $this->assign('cate',$cate);
    }

    //检查用户是否登录
    public function checkLogin()
    {
        $user_id = session('user_id');
        if(!$user_id){
            //没有登录
            $this->error('请先登录',U('User/login'));
        }
    }
}