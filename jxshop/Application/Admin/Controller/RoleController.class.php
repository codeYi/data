<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/9/25
 * Time: 10:17
 */
namespace Admin\Controller;
class RoleController extends CommonController
{
    public function add(){
        if(IS_GET){
            $this->display();
        }else{
            $model = D('Role');
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
        $model = D('Role');
        $data = $model->listData();
        $this->assign('data',$data);
        $this->display();
    }

    public function dels(){
        $role_id =intval(I('get.role_id'));
        if($role_id<=1){
            $this->error('参数错误');
        }

        $res = D('Role')->remove($role_id);
        if($res === false){
            $this->error('删除失败');
        }
        $this->success('删除成功');
    }

    public function  edit(){
        $model = D('Role');
        if(IS_GET){
            $role_id = intval(I('get.role_id'));
            $info = $model->findOneById($role_id);
            $this->assign('info',$info);
            $this->display();
        }else{
            $data = $model->create();
            if(!$data){
                $this->error($model->getError());
            }
            if($data['id']<=1){
                $this->error('参数错误');
            }
            $model->save($data);
            $this->success("修改成功",U('index'));
        }
    }

    //赋予权限
    public function  disfetch(){
        if(IS_GET){
            //获取当前角色ID拥有的权限信息
            $role_id = intval(I('get.role_id'));
            if($role_id<=1){
                $this->error("参数错误");
            }
            $hasRules = D('RoleRule')->getRules($role_id);
//            dump($hasRules);die;
            foreach($hasRules as $Key=>$value){
                $hasRulesIds[]=$value['rule_id'];
            }
            $this->assign('hasRules',$hasRulesIds);


            //获取所有的权限信息
            $RuleModel = D('Rule');
            $rule = $RuleModel->getCateTree();
            $this->assign('rule',$rule);
            $this->display();
        }else{
            $role_id = intval(I('post.role_id'));
            if($role_id<=1){
                $this->error("参数错误");
            }
            $rules = I('post.rule');
            D('RoleRule')->disfetch($role_id,$rules);

            //获取当前修改的角色下的所有用户信息
            $user_info = M('AdminRole')->where('role_id='.$role_id)->select();
            foreach($user_info as $key=>$value){
                //删除某个用户的对应的文件信息
                S('user_'.$value['admin_id'],null);
            }
            $this->success('操作成功',U('index'));
        }
    }

    public function flushAdmin()
    {
        //获取所有的超级管理员
        $user = M('AdminRole')->where('role_id=1')->select();
        //将所有的超级管理员用户对应缓存文件进行删除
        foreach($user as $key=>$value){
            S('user_'.$value['admin_id'],null);
        }
        echo 'ok';
    }
}
