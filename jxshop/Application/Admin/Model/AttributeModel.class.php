<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/9/28
 * Time: 14:50
 */
namespace Admin\Model;
class AttributeModel extends CommonModel
{
   //定义字段
    protected $fields = array('id','attr_name','type_id','attr_type','attr_input_type','attr_value');
    //自动验证
    protected $_validate =array(
        array('attr_name','require','属性名称必须填写'),
        array('type_id','require','分类ID必须填写'),
        array('attr_type','1,2','属性类型只能为单选或唯一',1,'in'),
        array('attr_input_type','1,2','属性录入方法只能为手工或列表',1,'in')
    );

    public function  listData()
    {
        //定义页尺寸
        $pagesize = 3;
        //计算数据总数
        $count = $this->count();
        //生成分页导航信息
        $page = new \Think\Page($count,$pagesize);
        $show = $page->show();
        //接收当前页码
        $p = intval(I('get.p'));
        $list = $this->page($p,$pagesize)->select();

        //使用替换的方式实现获取到具体的类型名称
        //1.获取所有的类型信息
        $type = D('Type')->select();
        //2.使用主键ID作为索引
        foreach($type as $key=>$value){
            $typeinfo[$value['id']] = $value;
        }
        //3.将当前数据中对应的type_id替换为具体对应的类型名称
        foreach($list as $key=>$value){
            $list[$key]['type_id'] = $typeinfo[$value['type_id']]['type_name'];
        }

        return array('pageStr'=>$show,'list'=>$list);
    }

    public function remove($attr_id)
    {
        $this->where("id=$attr_id")->delete();
    }
}
