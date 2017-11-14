<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/10/8
 * Time: 18:21
 */
namespace Admin\Model;
class GoodsAttrModel extends CommonModel
{
        protected  $fields = array('id','goods_id','attr_id','attr_values');

    public function insertAttr($attr,$goods_id)
    {
        foreach($attr as $key=>$value){
            foreach($value as $v){
                $attr_list[] = array(
                    'goods_id'=>$goods_id,
                    'attr_id'=>$key,
                    'attr_values'=>$v
                );
            }
        }
        $this->addAll($attr_list);
    }

    //根据商品的ID获取对应的属性值及属性信息
    public function getSigleAttr($goods_id)
    {
      $data = $this->alias('a')->join('left join jx_attribute b on a.attr_id=b.id')->field('a.*,b.attr_name,attr_type,attr_input_type,attr_value')->where("a.goods_id=$goods_id and b.attr_type=2")->select();

        //将获取到的数据结果转换为三维数组，方便模板显示
        foreach($data as $key=>$value){
            $list[$value['attr_id']][] =$value;
        }
    return $list;
    }

}