<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/10/9
 * Time: 23:50
 * 商品详情控制器
 */
namespace Home\Controller;
class GoodsController extends CommonController
{
    public function index(){

        $goods_id = intval(I('get.goods_id'));
        if($goods_id<=0){
            //参数有误
            $this->redirect('Index/index');
            }
            $goodsModel =  D('Admin/Goods');
            $goods = $goodsModel->where('is_sale=1 and id='.$goods_id)->find();
            if(!$goods) {
                $this->redirect('Index/index');
            }
        //如果商品处于促销阶段 价格显示为促销价
        if($goods['cx_price']>0 && $goods['start']<time() && $goods['end']>time()){

            $goods['shop_price'] = $goods['cx_price'];
        }
            //将商品信息格式化
            $goods['goods_body'] =htmlspecialchars_decode($goods['goods_body']);

            $this->assign('goods',$goods);

            //获取商品对应的相册信息
            $pic = M('GoodsImg')->where('goods_id='.$goods_id)->select();

            //获取商品对应的属性信息
            $attr = M('GoodsAttr')->alias('a')->field('a.*,b.attr_name,b.attr_type')->join('left join jx_attribute b on a.attr_id=b.id')->where("a.goods_id=$goods_id")->select();

        //对数据进行格式化处理
        foreach($attr as $key=>$value){
            if($value['attr_type']==1){
                //唯一属性
                $unique[]=$value;
            }else{
                //单选属性。格式化为三维数组，第一维使用属性ID作为下标
                $sigle[$value['attr_id']][]=$value;
            }
        }
            $this->assign('unique',$unique);
            $this->assign('sigle',$sigle);
            $this->assign('pic',$pic);

        //获取当前商品对应的评论信息
        $commentModel = D('Comment');
        $comment = $commentModel->getList($goods_id);
        $this->assign('comment',$comment);

        //获取当前商品的印象属相
        $data = M('Impression')->where('goods_id='.$goods_id)->order('count desc')->limit(8)->select();
        $this->assign('data',$data);
        $this->display();
    }

    //实现评论数据入库
    public function comment()
    {
        //判断用户是否登录
        $this->checkLogin();
        $model = D('Comment');
        $data = $model->create();
        if(!$data){
            $this->error('参数不对');
        }
        $model->add($data);
        $this->success('写入成功');
    }

    //增加评论的有用值
    public function good()
    {
        $comment_id = I('post.comment_id');
        $model = D('Comment');
        $info = $model->where('id='.$comment_id)->find();
        if(!$info){
            $this->ajaxReturn(array('status'=>0,'msg'=>'error'));
        }
        $model->where('id='.$comment_id)->setField('good_number',$info['good_number']+1);
        $this->ajaxReturn(array('status'=>1,'msg'=>'ok','good_number'=>$info['good_number']+1));

    }
}