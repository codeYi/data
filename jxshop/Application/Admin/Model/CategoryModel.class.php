<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/9/25
 * Time: 10:19
 */
namespace Admin\Model;
class CategoryModel extends CommonModel
{
    //自定义字段
    protected $fields = array('id', 'cname', 'parent_id', 'isrec');

    //自动验证
    protected $_validate = array(
        array('cname', 'require', '分类名称必须填写'),
    );

    //获取某个分类的子分类
    public function getChildren($id)
    {
        //先获取所有的分类信息
        $data = $this->select();
        //在对获取的信息进行格式化
        $list = $this->getTree($data, 0, $id, 1, false);
        //dump($list);die;
        foreach ($list as $key => $value) {
            $tree[] = $value['id'];
        }
        return $tree;
    }

    //获取格式化后的数据
    public function getCateTree($stop_id)
    {
        //先获取所有的分类信息
        $data = $this->select();
        //在对获取的信息进行格式化
        $list = $this->getTree($data, $stop_id);
        return $list;
    }

    //格式化分类信息
    public function getTree($data, $stop_id = 0, $id = 0, $lev = 1, $iscache = true)
    {
        static $list = array();
        //dump($list);
        if (!$iscache) {
            $list = array();
        }
        foreach ($data as $value) {
            if ($value['parent_id'] == $id) {
                if ($stop_id == $value['id']) continue;//自己和子分类跳过当前循环
                $value['lev'] = $lev;
                $list[] = $value;

                //使用递归的方式获取分类的子分类
                $this->getTree($data, $stop_id, $value['id'], $lev + 1);
            }
        }
//        dump($list);die;
        return $list;
    }

    //删除分类
    public function dels($id)
    {
        //删除的分类下有子分类不允许删除
        $result = $this->where("parent_id=" . $id)->find();
        if ($result) {
            return false;
        }
        return $this->where('id=' . $id)->delete();
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

    //获取楼层信息
    public function getFloor()
    {
        //获取所有的顶级分类
        $data = $this->where('parent_id=0')->select();

        //根据顶级分类的标识获取对应的二级分类及推荐的二级分类
        foreach ($data as $key => $value) {
            //获取二级分类
            $data[$key]['son'] = $this->where("parent_id=".$value['id'])->select();
            //获取推荐的二级分类
            $data[$key]['recson'] = $this->where("isrec = 1 and parent_id=".$value['id'])->select();

            //根据每个楼层推荐的二级分类信息获取对应的商品数据
            foreach ($data[$key]['recson'] as $k => $v){
                //$v代表的是每一个推荐分类信息
                $data[$key]['recson'][$k]['goods'] = $this->getGoodsByCateId($v['id']);
            }
        }
        return $data;
    }

    //通过分类ID或区对应的标识信息
    public function getGoodsByCateId($cate_id,$limit=8)
    {
        //1.获取当前分类下面子分类信息
        $children=$this->getChildren($cate_id);
        //2.将当前分类的标识追加到对应的子分类中
        $children[] =$cate_id;
        //3.将$children 格式化为字符串格式  目的是为了使用in语法
        $children = implode(',',$children);
        //4.通过目前的分类信息获取商品数据
        $goods = D('Goods')->where("is_sale=1 and cate_id in ($children)")->limit($limit)->select();
        return $goods;
    }

}
        //优化代码
//        $data =$this->select();
//        //循环处理数组,最后生成两个数组 父类数组和子类数组 两个数组key值相同
//        foreach($data as $key =>$value){
//            //判断是否是顶级分类
//            if ($value['parent_id'] == 0){
//                //将顶级分类作为数组key值
//                $dataInfo[$value['id']] = $value;
//                //储存父id临时变量
//                $parentIds[] = $value['id'];
//            }else{
//                //判断出次级分类
//                //将次级分类父id当作key
//                $info[$value['parent_id']]['son'][]=$value;
////                dump($info);die;
//                if ($value['isrec'] ==1){
//                    //判断是否是推荐
//                    $info[$value['parent_id']]['recson'][]=$value;
//                }
//
//            }
//        }
//
//        //循环子类数组 将子类数组追加到父类数组中
//        foreach($info as $key =>$value){
//            if (in_array($key,$parentIds)){
//                $dataInfo[$key]['son']=$value['son'];
//                $dataInfo[$key]['recson']=$value['recson'];
//                //将商品追加到recson中recson
//                foreach ($dataInfo[$key]['recson'] as $k =>$v){
//                    $dataInfo[$key]['recson'][$k]['goods']=$this->getGoodsByCateId($v['id']);
//                }
//            }
//
//        }
//        dump($dataInfo);
////        return $dataInfo;

//    }
//
//    //根据分类ID获取商品信息
//    public function  getGoodsByCateId($cate_id,$limit=8){
//        $cateIds =$this->getChildren($cate_id);
//        $cateIds[] = $cate_id;
//        $cateIds=implode(',',$cateIds);
//        return  D('goods')->where("is_sale=1 and cate_id in ($cateIds)")->limit($limit)->select();
