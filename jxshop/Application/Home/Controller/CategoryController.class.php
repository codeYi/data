<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/10/9
 * Time: 21:19   分类控制器
 */
namespace Home\Controller;
class CategoryController extends CommonController
{
    public  function index()
    {
        $data = D('Admin/goods')->getList();
        $this->assign('data',$data);
        $this->display();
    }
}