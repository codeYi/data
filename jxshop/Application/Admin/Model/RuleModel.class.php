<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/9/25
 * Time: 10:19
 */
namespace Admin\Model;
class RuleModel extends CommonModel{
    //自定义字段
    protected  $fields = array('id','rule_name','module_name','controller_name','action_name','parent_id','is_show');

    //自动验证
    protected $_validate = array(
        array('rule_name','require','权限名称必须填写'),
        array('module_name','require','模型名称必须填写'),
        array('controller_name','require','控制器名称必须填写'),
        array('action_name','require','方法名称必须填写')
    );

    //获取某个分类的子分类
    public function getChildren($id){
        //先获取所有的分类信息
        $data = $this->select();
        //在对获取的信息进行格式化
        $list = $this->getTree($data,0,$id,1,false);
        //dump($list);die;
        foreach($list as $key=>$value){
            $tree[] = $value['id'];
        }
        return $tree;
    }

    //获取格式化后的数据
    public function getCateTree($stop_id=0){
        //先获取所有的分类信息
        $data = $this->select();
        //在对获取的信息进行格式化
        $list = $this->getTree($data,$stop_id);
        return $list;
    }

    //格式化分类信息
    public function getTree($data,$stop_id=0,$id=0,$lev=1,$iscache=true){
        static $list = array();
        //dump($list);
        if(!$iscache) {
            $list = array();
        }
        foreach($data as $value){
            if($value['parent_id']==$id){
                if($stop_id==$value['id'])continue;//自己和子分类跳过当前循环
                $value['lev'] = $lev;
                $list[]=$value;

                //使用递归的方式获取分类的子分类
                $this->getTree($data,$stop_id,$value['id'],$lev+1);
            }
        }
//        dump($list);die;
        return $list;
    }

    //删除分类
    public function dels($id){
        //删除的分类下有子分类不允许删除
        $result = $this->where("parent_id=".$id)->find();
        if($result){
            return false;
        }
        return $this->where('id='.$id)->delete();
    }

    //编辑更新
//    public function update($data){
//        //根据要修改的分类的ID将自己的所有子分类查询出来
//        $tree = $this->getCateTree($data['id']);
//        //将自己添加到不能修改的数组中
//        $tree[] = array('id'=>$data['id']);
//
//        foreach($tree as $key=>$value){
//            if($data['parent_id']==$value['id']){
//                $this->error="不能设置子分类为父分类";
//                return false;
//            }
//        }
//        return $this->save($data);
//    }

}