<?php
/**
角色模型
 */
namespace Admin\Model;
class TypeModel extends CommonModel
{
    //自定义字段
    protected  $fields = array('id','type_name');

    //自动完成
    protected  $_validate = array(
        array('type_name','require','角色名称必须填写'),
        array('type_name','','角色名重复',1,'unique')
    );

    //获取管理员数据
    public function listData(){
        //定义页面尺寸
        $pagesize = 2;
        //计算数据总数
        $count = $this->count();
        //生成分页导航信息
        $page = new \Think\Page($count,$pagesize);
        $show = $page->show();
        //接收当前所在的页码
        $p = intval(I('get.p'));
        $list=$this->page($p,$pagesize)->select();
        return array('pageStr'=>$show,'list'=>$list);
    }


    //删除角色
    public function remove($role_id){
        return $this->where("id=$role_id")->delete();
    }
}