<?php
/**
角色模型
 */
namespace Admin\Model;
class RoleRuleModel extends CommonModel
{
    //自定义字段
    protected  $fields = array('id','role_id','rule_id');

    //角色权限数据入库
     function  disfetch($role_id,$rules){
        $this->where('role_id='.$role_id)->delete();
        foreach($rules as $key=>$value){
            $list[] = array(
                'role_id'=>$role_id,
                'rule_id'=>$value,
            );
        }
        $this->addAll($list);
    }

    public function getRules($role_id){
        return $this->where('role_id='.$role_id)->select();
    }

}