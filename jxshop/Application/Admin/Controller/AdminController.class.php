<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/9/28
 * Time: 14:38
 */
namespace Admin\Controller;
class AdminController extends CommonController
{
    public function  add(){
        if(IS_GET){
            //获取所有的角色
            $role = D('Role')->select();
            $this->assign('role',$role);
            $this->display();
        }else{
            //接收数据实现数据入库
            $model = D('Admin');
            $data = $model->create();
            if(!$data){
                $this->error($model->getError());
            }
            $res = $model->add($data);
            if(!$res){
                $this->error("添加失败");
            }
            $this->success("添加成功");
        }
    }

    public function index(){
        $model = D('Admin');
        $data = $model->listdata();
//        dump($data);die;
        $this->assign('data',$data);
        $this->display();
    }

    public  function dels(){
        $admin_id = intval(I('get.admin_id'));
        if($admin_id <=1){
            $this->error('参数错误');
        }
        $res = D('Admin')->remove($admin_id);
        if($res === false){
            $this->error("删除失败");
        }
        $this->success("删除成功",U('index'));
    }

    public function edit(){
        $model= D('Admin');
        if(IS_GET){
            $admin_id = intval(I('get.admin_id'));
            //获取用户名及密码  对应的角色ID
            $info = $model->findOne($admin_id);
            //获取所有的角色
            $role = D("Role")->select();
            $this->assign('info',$info);
            $this->assign('role',$role);
            $this->display();
        }else{
            $data = $model->create();
            if(!$data){
                    $this->error($model->getError());
            }
            if($data['id']<=1){
                $this->error("参数错误");
            }
            $model->update($data);
            $this->success("修改成功",U('index'));
        }
    }
}