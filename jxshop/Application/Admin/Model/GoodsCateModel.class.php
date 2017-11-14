<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/9/25
 * Time: 10:19
 */
namespace Admin\Model;
class GoodsCateModel extends CommonModel
{
    public function insertExtCate($ext_cate_id, $goods_id)
    {
        //对提交的数据去重
        $ext_cate_id = array_unique($ext_cate_id);
        foreach ($ext_cate_id as $key => $value) {
            if ($value != 0) {
                $list[] = array('goods_id' => $goods_id, 'cate_id' => $value);
            }
        }

        //批量写入数据
        M('GoodsCate')->addAll($list);
    }
}

