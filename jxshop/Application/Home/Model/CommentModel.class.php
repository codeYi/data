<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/10/11
 * Time: 14:25
 */
namespace Home\Model;
use Think\Model;

class CommentModel extends Model
{
    protected $field = array('id','user_id','goods_id','addtime','content','star','goods_number');

    public function _before_insert(&$data)
    {
        $data['addtime']=time();
        $data['user_id']=session('user_id');
    }

    public function _after_insert($data)
    {
        //选择的印象入库
        $old = I('post.old');
        foreach($old as $key=>$value){
            M('Impression')->where('id='.$value)->setInc('count');
        }

        $name= I('post.name');
        $name = explode(',',$name);
        $name = array_unique($name);

        foreach($name as $key=>$value){
            if(!$value){
                continue;
            }
            $where = array('goods_id'=>$data['goods_id'],'name'=>$value
        );

            $model=M('Impression');
            $res = $model->where($where)->find();
            if($res){
                $model->where($where)->setInc('count');
            }else{
                $where['count'] = 1;
                $model->add($where);
            }
        }

        //实现商品表中评论总数的增加
        D('Admin/goods')->where('id='.$data['goods_id'])->setInc('plcount');
    }

    //根据商品ID获取对应的评论信息
    public function getList($goods_id)
    {
        $p = I('get.p');
        $pagesize = 2;
        //计算总数
        $count = $this->where('goods_id='.$goods_id)->count();
        $page = new \Think\Page($count, $pagesize);
        //设置锚点
        $page->setConfig('is_anchor',true);
        $show = $page->show();
        //获取对应的评论信息
        $list = $this->alias('a')->field('a.*,b.username')->join('left join jx_user b on a.user_id = b.id')->where('goods_id='.$goods_id)->page($p,$pagesize)->select();

        return array('list'=>$list,'page'=>$show);
    }
}

