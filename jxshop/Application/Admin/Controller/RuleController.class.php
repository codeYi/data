<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/9/25
 * Time: 10:17
 */
namespace Admin\Controller;
class RuleController extends CommonController
{
    //实现分类的添加
    public function add(){
        if(IS_GET){
            //获取格式化之后的分类信息
            $model = D('Rule');
            $cate = $model->getCateTree();
            //将信息赋值给模板
            $this->assign('cate',$cate);

            $this->display();
        }else{
            //数据入库
            $model = D('Rule');
            //创建数据
            $data = $model->create();
            if(!$data){
                $this->error($model->getError());
            }
            $insertId = $model->add($data);
            if(!$insertId){
                $this->error('数据写入失败');
            }
            $this->success('写入成功');
        }
    }

    //分类列表显示
    public function index(){
        $model = D('Rule');
        $list = $model->getCateTree();
        $this->assign('list',$list);
        $this->display();
    }

    //实现商品分类的删除
    public function dels(){
        $id=intval(I('get.id'));
        if($id<=0){
            $this->error('参数不对');
        }
        $model = D('Rule');
        //调用模型中的删除方法实现删除操作
        $res = $model->dels($id);
        if($res === false){
            $this->error('删除失败');
        }
        $this->success('删除成功');
    }

    //编辑操作
    public function edit(){
        if(IS_GET) {
            //显示要要编辑的信息
            $id = intval(I('get.id'));
            //根据ID参数获取该分类的信息
            $model = D('Rule');
            $info = $model->findOneById($id);
            $this->assign('info', $info);

            //获取所有分类的信息
            $cate = $model->getCateTree($id);
            $this->assign('cate', $cate);
            $this->display();
        }else{
            //实现数据修改
            $model = D('Rule');
            $data = $model->create();
            $res = $model->save($data);
            if($res===false){
            $this->error("修改失败");
            }
            $this->success('修改成功',U('index'));
        }
    }


}
