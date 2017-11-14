<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/10/11
 * Time: 14:25
 */
namespace Home\Model;
use Think\Model;

class UserModel extends Model
{
    protected $fields = array('id', 'username', 'password', 'salt','email','active_code','status');

    //实现用户的信息入库
    public function regist($username,$password)
    {
        //检查用户名是否可用
        $info = $this->where("username = '$username'")->find();
        if($info){
            $this->error='用户名重复';
            return false;
        }
        //生成盐
        $salt=rand(100000,999999);
        //生成双重MD5之后的密码
        $db_password= md5(md5($password).$salt);
        $data=array(
            'username' => $username,
            'password' => $db_password,
            'salt' => $salt,
        );
        return $this->add($data);
    }

    //实现用户使用邮箱进行注册
    public function registbyemail($username,$password,$email)
    {
        //检查用户名是否重复
        $info = $this->where("username = '$username'")->find();
        if($info){
            $this->error='用户名重复';
            return false;
        }
        //检查邮箱是否重复
        $info = $this->where("email = '$email'")->find();
        if($info){
            $this->error='邮箱重复';
            return false;
        }
        //生成盐
        $salt=rand(100000,999999);
        //生成双重MD5之后的密码
        $db_password= md5(md5($password).$salt);
        $data=array(
            'username' => $username,
            'password' => $db_password,
            'salt' => $salt,
            'email'=>$email,
            'status'=>0,
            'active_code'=>uniqid(),//生成激活码
        );

        $user_id = $this->add($data);
        $data['user_id'] = $user_id;
        return $data;
    }

    //登录验证
    public function login($username,$password)
    {
     //调用接口实现登录
        $parms =array(
            'username'=>$username, //指定的用户名
            'password'=>$password, //指定的密码
            'c'=>'user', //接口中的具体控制器
            'a'=>'login',//接口中具体的方法
        );
        $res = get_data($parms);
        if($res['status']==0){
            $this->error=$res['msg'];
            return false;
        }
        $info = $res['data'];
        //保存用户的登录状态
        session('user',$info);
        session('user_id',$info['id']);

        //实现购物车cookie中的数据转移
        D('Cart')->cookie2db();
        return ture;
    }
}
