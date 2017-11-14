<?php
namespace Admin\Controller;

class GoodsController extends CommonController
{
    public function showAttr()
    {
        $type_id = intval(I('post.type_id'));
        if($type_id<=0){
            echo '没有数据';exit;
        }
        $data = D('Attribute')->where("type_id=$type_id")->select();

        foreach($data as $key=>$value){
            if($value['attr_input_type'] == 2){
                $data[$key]['attr_value'] = explode(',',$value['attr_value']);
            }
        }

        $this->assign('data',$data);
        $this->display();
    }

    public function add(){
        if(IS_GET){
            //获取所有的类型
            $type = D('Type')->select();
            $this->assign('type',$type);
            //获取所有分类信息
            $cate = D('Category')->getCateTree();
            $this->assign('cate',$cate);
            $this->display();
            exit();
        }
        $model = D('Goods');
        $data = $model->create();
        if(!$data){
            $this->error($model->getError());
        }
        $goods_id = $model->add($data);
        if(!$goods_id){
            $this->error($model->getError());
        }
        $this->success('添加成功');
    }

    public function index(){
        //获取分类信息
        $cate = D('Category')->getCateTree();
        $this->assign('cate',$cate);
        $model = D('Goods');
        $data =$model->listData();
        $this->assign('data',$data);
        $this->display();
    }

    //商品的伪删除
    public function  dels(){
        $goods_id = intval(I('get.goods_id'));
        if($goods_id<=0){
            $this->error('参数错误');
        }
        $model = D('Goods');
        $res = $model->dels($goods_id);
        if($res === false){
            $this->error('删除失败');
        }
        $this->success('删除成功');
    }

    //商品的编辑修改
    public function edit(){
        if(IS_GET){
            $goods_id = intval(I('get.goods_id'));
            $model = D('Goods');
            $info = $model->findOneById($goods_id);
            if(!$info){
                $this->error('参数错误');
            }

            //获取所有的分类信息
            $cate = D('Category')->getCateTree();
            $this->assign('cate',$cate);

            //扩展分类的获取
            $ext_cate_ids = M('GoodsCate')->where("goods_id=$goods_id")->select();
            //如果不存在扩展分类时,手动追加元素
            if(!$ext_cate_ids){
                $ext_cate_ids = array("msg"=>"no data");
            }

            $this->assign('ext_cate_ids',$ext_cate_ids);

            //对商品描述进行反转译
            $info['goods_body'] = htmlspecialchars_decode($info['goods_body']);
            $this->assign('info',$info);

            //获取所有的类型
            $type = D('Type')->select();
            $this->assign('type',$type);
            $goodsAttrModel = M('GoodsAttr');
            $attr = $goodsAttrModel->alias('a')->field('a.*,b.attr_name,b.attr_type,b.attr_input_type,b.attr_value')->join('left join jx_attribute b on a.attr_id=b.id')->where('a.goods_id='.$goods_id)->select();

            foreach($attr as $key=>$value){
                if($value['attr_input_type']==2){
                    $attr[$key]['attr_value']=explode(',',$value['attr_value']);
                }
            }
            foreach($attr as $key=>$value){
                $attr_list[$value['attr_id']][]=$value;
            }
            $this->assign('attr',$attr_list);

            //获取商品对应的图片信息
            $goods_img_list = M('GoodsImg')->where("goods_id=$goods_id")->select();
            $this->assign('goods_img_list',$goods_img_list);
            $this->display();
        }else{
            $model = D('Goods');
            $data = $model->create();
            if(!$data){
                $this->error($model->getError());
            }
            $res = $model->update($data);
            if($res === false){
                $this->error($model->getError());
            }
            $this->success("修改成功",U('index'));
        }
    }

    //回收站商品列表显示
    public function trash(){
        //获取分类信息
        $cate = D('Category')->getCateTree();
        $this->assign('cate',$cate);

        $model = D('goods');

        //调用模型方法获取数据
        $data =$model->listData(0);
        $this->assign('data',$data);
        $this->display();
    }

    //还原商品
    public function recover(){
        $goods_id = intval(I('get.goods_id'));
        $model = D('Goods');
        $res = $model->setStatus($goods_id,1);
        if($res === false){
            $this->error("还原失败");
        }
        $this->success("还原成功");
    }

    //彻底删除
    public function remove(){
        $goods_id = intval(I('get.goods_id'));
        if($goods_id<=0){
            $this->error('参数错误');
        }
        $model = D('Goods');
        $res =$model->remove($goods_id);
        if($res === false){
            $this->error('彻底删除');
        }
        $this->success('删除成功');
    }

    //删除相册中的图片
    public function delImg(){
        $img_id = intval(I('post.img_id'));
        if($img_id<=0){
            $this->ajaxReturn(array('status'=>0,'msg'=>'参数错误'));
        }
        //先将图片删除
        $model  = M('GoodsImg');
        $info = $model->where("id=$img_id")->find();
        if(!$info){
            $this->ajaxReturn(array('status'=>0,'msg'=>'参数错误'));
        }

        unlink($info['goods_img']);
        unlink($info['goods_thumb']);

        //将图片对应的数据库中的信息删除
        $model->where("id=$img_id")->delete();
        $this->ajaxReturn(array('status'=>1,'msg'=>'ok'));
    }

    //实现商品库存设置
    public function setNumber()
    {
        if(IS_GET){
            $goods_id = intval(I('get.goods_id'));
            //通过商品的标识获取对应的单选属性值及属性信息
            $GoodsAttrModel = D('GoodsAttr');
            $attr = $GoodsAttrModel->getSigleAttr($goods_id);
            if(!$attr){
                //针对没有单选属性的商品获取对应的库存信息
                $info = D('Goods')->where("id=$goods_id")->find();
                $this->assign('info',$info);
                //商品没有单选属性
                $this->display('nosigle');
                exit;
            }

            //针对有单选属性的库存信息的获取
            $info = M('GoodsNumber')->where("goods_id=$goods_id")->select();

            if(!$info){
                //没有获取到对应的库存信息，为了保证正常显示。至少要循环一次
                $info = array('goods_number'=>0);
            }

            $this->assign('info',$info);
            $this->assign('attr',$attr);
            $this->display();
        }else{
            $attr = I('post.attr');
            $goods_number = I('post.goods_number');
            $goods_id = I('post.goods_id');

            if(!$attr){
                //没有单选属性的数据提交
                D('Goods')->where('id='.$goods_id)->setField('goods_number',$goods_number);

                exit;
            }
            //通过循环处理 最后数据入库
            foreach($goods_number as $key=>$value){
                //拼接处具体的组合信息
                $tmp=array();
                foreach($attr as $k=>$v){
                    $tmp[] = $v[$key];
                }
                sort($tmp);
                $goods_attr_ids = implode(',',$tmp);
                //实现组合去重
                if(in_array($goods_attr_ids,$has)){
                    //重复 手动去掉$goods_number中对应的库存量
                    unset($goods_number[$key]);
                    continue;
                }
                $has[] =$goods_attr_ids;
                $list[] = array(
                    'goods_id'=>$goods_id,
                    'goods_number'=>$value,
                    'goods_attr_ids'=>$goods_attr_ids,
                );
            }
            //删除当前商品以及拥有的库存信息
            M('GoodsNumber')->where('goods_id='.$goods_id)->delete();
            //将库存信息入库
            M('GoodsNumber')->addAll($list);
            //计算当前库存总数
            $goods_count = array_sum($goods_number);
           $res =  D('Goods')->where('id='.$goods_id)->setField('goods_number',$goods_count);
            if(!$res){
                $this->error("保存失败");
            }
            $this->success("保存成功",U('index'));

        }
    }
}