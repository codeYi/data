<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/9/25
 * Time: 10:16
 */
namespace Admin\Controller;

use Think\Controller;

class CommonController extends Controller
{

    //增加属性存储信息
    public $is_check_rule = true;

    //保存用户的信息.基本信息/角色ID/权限信息
    public $user = array();


    public function __construct()
    {
        parent::__construct();
        //判断当前用户是否登录
        $admin = cookie('admin');
        if (!$admin) {
            $this->error('没有登录', U('login/login'));
        }

//            S(array(
//                'type'=>'memcache',
//                'host'=>'localhost',
//                'port'=>'11211'
//            ));

        $this->user = S('user_'.$admin['id']);
        if(!$this->user){
            echo 'mysql';
        }
        //将当前用户的信息保存到属性中
        $this->user = $admin;
        //根据用户的id获取对应的角色ID
        $role_info = M('AdminRole')->where('admin_id='.$admin['id'])->find();
        //将角色ID存储在user属性中
        $this->user['role_id'] = $role_info['role_id'];
        $ruleModel = D('Rule');
        if ($role_info['role_id'] == 1) {
            //超级管理员不验证权限
            $this->is_check_rule = false;
            $rule_list = $ruleModel->select();
        } else {
            //普通管理员
            //根据角色ID获取对应的权限ID
            //根据权限ID获取对应的权限信息
            $rules = D('RoleRule')->getRules($role_info['role_id']);
            //将查询到的权限ID的二维数组转换为一维数组
            foreach ($rules as $key => $value) {
                $rules_ids[] = $value['rule_id'];
            }

            //将一维数组转换为字符串格式
            $rules_ids = implode(',', $rules_ids);
            //根据权限ID获取对应的权限信息
            $rule_list = $ruleModel->where("id in ($rules_ids)")->select();
        }
        foreach ($rule_list as $key => $value) {
            $this->user['rules'][] = strtolower($value['module_name'] . '/' . $value['controller_name'] . '/' . $value['action_name']);

            //导航栏菜单显示
            if ($value['is_show'] == 1) {
                $this->user['menus'][] = $value;
            }
            //读取数据库完成后需要将信息写入到文件中
            S('user_'.$admin['id'],$this->user);
        }

        //超级管理员不用进行权限验证
        if($this->user['role_id'] == 1){
            $this->is_check_rule = false;
        }

        if($this->is_check_rule){
            //增加默认具备的权限访问
            $this->user['rules'][]='admin/index/index';
            $this->user['rules'][]='admin/index/top';
            $this->user['rules'][]='admin/index/menu';
            $this->user['rules'][]='admin/index/main';

            //普通管理员
            $action = strtolower(MODULE_NAME."/".CONTROLLER_NAME."/".ACTION_NAME);
            if(!in_array($action,$this->user['rules'])){
                if(IS_AJAX){
                    $this->ajaxReturn(array('status'=>0,'msg'=>'没有权限'));
                }else{
                    echo "没有权限";
                    exit();
                }
            }
        }
    }
}