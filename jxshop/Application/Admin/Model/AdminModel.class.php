<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/9/28
 * Time: 14:50
 */
namespace Admin\Model;
class AdminModel extends CommonModel
{
   //定义字段
    protected $fields = array('id','username','password');
    //自动验证
    protected $_validate =array(
        array('username','require','用户名必须填写'),
        array('username','','用户名重复',1,'unique'),
        array('password','require','密码必须填写'),
    );

    //自动完成
    protected $_auto = array(
        array('password','md5',3,'function'),
    );

    //后置钩子函数 实现数据的中间表入库
    public function _after_insert($data)
    {
        $admin_role = array(
            'admin_id'=>$data['id'],
            'role_id'=>I('post.role_id'),
        );
        M('AdminRole')->add($admin_role);
    }


    //管理员首页展示
    public function listData(){
        //定义每页显示的数据条数
        $pagesize = 3;
        //2.获取数据总数
        $count = $this->count();
        //3.计算出分页导航
        $page = new \Think\Page($count,$pagesize);
        $show = $page->show();
        //4.获取当前页码
        $p = intval(I('get.p'));
        //5.获取具体的数据
        $data = $this->alias('a')->field("a.*,c.role_name")->join("left join jx_admin_role as b on a.id=b.admin_id")->join("left join jx_role as c on c.id = b.role_id")->page($p,$pagesize)->select();
        return array('pageStr'=>$show,'data'=>$data);
    }

    //删除用户
    public function remove($admin_id){
        //1.开启事物
        $this->startTrans();

        //删除对应的用户信息
        $userStatus =$this->where("id=$admin_id")->delete();
        if(!$userStatus){
            $this->rollback();
            return false;
        }
        //删除用户对应的角色信息
        $roleStatus =M('AdminRole')->where("admin_id=$admin_id")->delete();
        if(!$roleStatus){
            $this->rollback();
            return false;
        }
        $this->commit();
        return true;
    }

    //根据用户的ID获取用户的信息以及对应的角色ID
    public function  findOne($admin_id){
        return $this->alias('a')->field('a.*,b.role_id')->join("left join jx_admin_role as b on a.id=b.admin_id")->where("a.id=$admin_id")->find();
    }

    //修改信息的修改
    public function  update($data){
        $role_id =intval(I("post.role_id"));
        //修改用户的基本信息
        $this->save($data);
        //修改用户对应的角色
        M('AdminRole')->where("admin_id=".$data['id'])->save(array('role_id'=>$role_id) );
    }

    //用户登录
    public function login($username,$password){
        //根据用户名查询用户信息
        $usernameinfo = $this->where("username='$username'")->find();
        if(!$usernameinfo){
            $this->error("用户名不存在");
        }
        //根据密码进行比对
        if($usernameinfo['password'] != md5($password)){
            $this->error("密码错误");
            return false;
        }

        cookie('admin',$usernameinfo);
        return true;
    }
}
