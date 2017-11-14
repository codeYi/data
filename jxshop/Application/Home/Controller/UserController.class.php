<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/10/11
 * Time: 10:21
 */
namespace Home\Controller;
class UserController extends CommonController
{

    //根据指定的手机号码进行短信验证码发送
    public function sendcode()
    {
        //获取具体要发送的手机号码
        $tel = I('post.tel');
        if(!$tel){
            $this->ajaxReturn(array('status'=>0,'msg'=>'手机号码错误'));
        }

        //根据手机号发送短信验证码
        $code = rand(1000,9999);
        $res = sendTemplateSMS($tel,array($code,'60'),'1');
        if(!$res){
            $this->ajaxReturn(array('status'=>0,'msg'=>'发送验证码失败'));
        }

        session('code',$code);
        return true;
    }

    //注册页面展示
    public function regist()
    {
        if(IS_GET){
            $this->display();
        }else{
            $username =I('post.username');
            $password =I('post.password');
            $checkcode = I('post.checkcode');

            if($username == '' || $password == ''){
                $this->ajaxReturn(array('status'=>0,'msg'=>'用户名和密码不能为空'));
            }
            //检查验证码是否正确
            $obj = new \Think\Verify();
            if(!$obj->check($checkcode)){
                $this->ajaxReturn(array('status'=>0,'msg'=>'验证码错误'));
            }
            //实例化模型对象，调用方法入库
            $model = D('User');
            $res =$model->regist($username,$password);
            if(!$res){
                $this->ajaxReturn(array('status'=>0,'msg'=>$model->getError()));
            }
            $this->ajaxReturn(array('status'=>1,'msg'=>'注册成功'));
        }

    }

    //实现用户邮箱注册
    public function registbyemail()
    {
        if(IS_GET){
            $this->display();
        }else{
            $username =I('post.username');
            $password =I('post.password');
            $email = I('post.email');

            //实例化模型对象，调用方法入库
            $model = D('User');
            $res =$model->registbyemail($username,$password,$email);
            if(!$res){
                $this->ajaxReturn(array('status'=>0,'msg'=>$model->getError()));
            }
            $link = '<a>http://jxshop.com'.U('active').'?user_id='.$res['user_id'].'&active_code='.$res['active_code'].'</a>';
            //发送邮件
            sendEmail($email,'商城用户激活邮件',$link);
            $this->ajaxReturn(array('status'=>1,'msg'=>'注册成功'));
        }

    }

    //激活操作
    public function active(){
        $user_id = I('get.user_id');
        $active_code = I('get.active_id');

        $model =D('User');

        $user_info  = $model->where('id='.$user_id)->find();
        if(!$user_info){
            $this->error('参数错误');
        }
        if($user_info['status']==1){
            $this->error('已经激活');
        }

        if($user_info['active_code'] == $active_code){
            $this->error('激活码错误');
        }
        $model->where('id='.$user_id)->setField('status',1);
        $this->success('激活成功',U('login'));
    }


    //生成验证码
    public function code()
    {
        $config=array("length"=>3);
        $code = new \Think\Verify($config);
        $code->entry();
    }

//    //登录前校验验证码
//    public function checkcode()
//    {
//        $code = trim(I('post.code'));
//        $obj = new \Think\Verify();
//        if(!$obj->check($code)){
//            $this->ajaxReturn(array('status'=>0,'msg'=>"验证码错误"));
//        }
//        $this->ajaxReturn(array('status'=>1));
//    }

    //用户登录
    public function  login()
    {
        if(IS_GET){
            $this->display();
        }else{
            $username=I('post.username');
            $password=I('post.password');
            $checkcode = I('post.checkcode');
            //检查验证码是否正确
            $obj = new \Think\Verify();
            if(!$obj->check($checkcode)){
                $this->ajaxReturn(array('status'=>0,'msg'=>"验证码错误"));
            }
            //实例化模型对象
            $model = D('User');
            $res = $model->login($username,$password);
            if(!$res){
                $this->ajaxReturn(array('status'=>0,'msg'=>$model->getError()));
            }
            $this->ajaxReturn(array('status'=>1,'msg'=>"ok"));
        }
    }

    //退出用户登录
    public function logout()
    {
        session('user',null);
        session('user_id',null);
        $this->redirect('/');
    }

    //qq登录
    public function oauth()
    {
        Vendor('qq.API.qqConnectAPI');
        $qc = new \QC();
        $qc->qq_login();
    }

    //qq登录回调
    public function callback()
    {
        Vendor('qq.API.qqConnectAPI');
        $qc = new \QC();
        //输出access_token
        echo $qc->qq_callback();
        //输出open_id
        echo $qc->get_openid();

        //获取具体用户信息
        $user = $qc->get_user_info();
        dump($user);
    }

    public function  test(){
//        $res = http_curl('http://api.com/index.php?m=home&c=user&a=login',array('username'=>'admin','password'=>'admin'));
        $res =authcode('yihui','ENCODE');
        dump($res);
        dump(authcode($res));
    }

}