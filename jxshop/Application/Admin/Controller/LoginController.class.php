<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/9/28
 * Time: 14:38
 */
namespace Admin\Controller;

use Think\Controller;

class LoginController extends Controller
{
   public function login()
   {
       if(IS_GET){
           $this->display();
       }else{
           //对验证码进行对比
           $captcha = I('post.captcha');
           $verify = new \Think\Verify();
           $res = $verify->check($captcha);
           if(!$res){
                $this->error('验证码不正确');
           }

           //接收用户名和密码
           $username = I('post.username');
           $password = I('post.password');
           $model = D('Admin');
           $res = $model->login($username,$password);
           if(!$res){
                $this->error($model->getError());
           }
           $this->success('登录成功',U('Index/index'));
       }
   }

    //生成验证码
    public function  verify()
    {
        $config =array('length'=>3);
        $verify = new \Think\Verify($config);
        $verify->entry();
    }
}