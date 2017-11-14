<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/9/25
 * Time: 10:19
 */
namespace Admin\Model;
class GoodsModel extends CommonModel
{
    //自定义字段
    protected $fields = array('id', 'goods_name', 'goods_sn', 'cate_id', 'market_price', 'shop_price', 'goods_img', 'goods_thumb', 'goods_body', 'is_hot', 'is_rec', 'is_new', 'addtime', 'isdel', 'is_sale','type_id','goods_number','cx_price','start','end','plcount','sale_number');

    //自定义自动验证
    protected $_validate = array(
        array('goods_name','require','商品名称必须填写',1),
        array('cate_id','checkCategory','分类必须填写',1,'callback'),
        array('market_price','currency','市场价格格式不对'),
        array('shop_price','currency','本店价格格式不对')
    );

    //对分类进行验证
    public function checkCategory($cate_id){
        $cate_id = intval($cate_id);
        if($cate_id>0){
            return true;
        }
        return false;
    }

    //使用钩子函数添加时间及货号
    public function _before_insert(&$data){

        //实现关于促销商品时间的格式化操作
        if($data['cx_price']>0){
            //设置商品为促销商品
            $data['start'] = strtotime($data['start']);
            $data['end'] = strtotime($data['end']);
        }else{
            $data['cx_price'] = 0.00;
            $data['start'] = 0;
            $data['end'] = 0;
        }

        //添加时间
        $data['addtime'] = time();
        //处理货号  输入货号时要判断货号是否已存在  没有输入则自动生成一个
        if(!$data['goods_sn']){
            //没有提交货号 自动生成
            $data['goods_sn'] = 'JX'.uniqid();
        }else{
            //有提交货号
            $info = $this->where('goods_sn='.$data['goods_sn'])->find();
            if($info){
                $this->error ="货号重复";
                return false;
            }
        }

        $res = $this->uploadImg();
        if($res){
            $data['goods_img'] =$res['goods_img'];
            $data['goods_thumb'] = $res['goods_thumb'];
        }

    }

    //在add完成之后自动执行
    public function _after_insert($data){
        $goods_id = $data['id'];
        //接收提交数据的扩展分类
        $ext_cate_id = I('post.ext_cate_id');
        D('GoodsCate')->insertExtCate($ext_cate_id,$goods_id);

        //属性入库
        $attr = I('post.attr');
        D('GoodsAttr')->insertAttr($attr,$goods_id);

        //实现商品相册图片上传
        //1.商品图片释放
        unset($_FILES['goods_img']);
        $upload =new \Think\Upload();
        $info = $upload->upload();
        foreach($info as $key=>$value){
            //上传图片后的地址
            $goods_img = 'Uploads/'.$value['savepath'].$value['savename'];
            //制作缩略图
            $img=new \Think\Image();
            //打开图片
            $img ->open($goods_img);

            //制作缩略图
            $goods_thumb ='Uploads/'.$value['savepath'].'thumb_'.$value['savename'];

            $img->thumb(100,100)->save($goods_thumb);

            $list[] = array(
                'goods_id'=>$goods_id,
                'goods_img'=>$goods_img,
                'goods_thumb'=>$goods_thumb,
            );
        }
        if($list){
            M('GoodsImg')->addAll($list);
        }
    }

    //获取商品信息
    public function listData($isdel=1){
        //定义每页显示的数据条数
        $pagesize = 3;
        //2.获取数据总数
        $where = "isdel=$isdel";

        //接收提交分类的id
        $cate_id = intval(I('get.cate_id'));
        if($cate_id){
            //1.根据当前的分类ID获取子分类
            $cateModel = D('Category');
            $tree=$cateModel->getChildren("$cate_id");
            //将提交的当前分类ID也追加到数组中
            $tree[] = $cate_id;
            $children = implode(',',$tree);
            //获取扩展分类的id
            $ext_goods_ids = M('GoodsCate')->group('goods_id')->where("cate_id in($children)")->select();
            if($ext_goods_ids){
                foreach($ext_goods_ids as $key=>$value){
                $goods_ids[] = $value['goods_id'];
                }
            //将数组转换为字符串
                $goods_ids = implode(',',$goods_ids);
            }
            //组合where子句
            if(!$goods_ids){
                //没有商品的扩展分类满足条件
                $where .= " AND cate_id in ($children)";
            }else{
                $where .=" AND (cate_id in ($children) OR id in ($goods_ids))";
            }
        }

        //接收提交的状态码
        $intro_type = I('get.intro_type');
        if($intro_type == "is_new" || $intro_type =="is_rec" || $intro_type == "is_hot"){
            $where .= " AND $intro_type = 1";
        }

        //接收上下架
        $is_sale = intval(I('get.is_sale'));
        if($is_sale == 1){
            //表示表单提交的是上架状态
            $where .= " AND is_sale = 1";
        }elseif($is_sale == 2){
            //表示表单提交的是下架状态
            $where .= " AND is_sale = 0";
        }

        //接收关键字
        $keyword = I('get.keyword');
        if($keyword){
            $where .=" AND goods_name like '%$keyword%'";
        }


        $count = $this->where($where)->count();
        //3.计算出分页导航
        $page = new \Think\Page($count,$pagesize);
        $show = $page->show();
        //4.获取当前页码
        $p = intval(I('get.p'));
        //5.获取具体的数据
        $data = $this->where($where)->page($p,$pagesize)->select();
        return array('pagestr'=>$show,'data'=>$data);
    }

    //删除商品
    public function  dels($goods_id){
        return $this->where("id=$goods_id")->setField('isdel',0);
    }

    public function uploadImg(){
//        dump($_FILES['goods_img']);die;
        //判断是否有图片上传
        if(isset($_FILES['goods_img']) && $_FILES['goods_img']['error']!=0){
            return false;
        }
        //实现图片上传
        $upload = new \Think\Upload();
        $info = $upload->uploadOne($_FILES['goods_img']);
        if(!$info){
            $this->error=$upload->geterror();
        }

        //上传图片后的地址
        $goods_img = 'Uploads/'.$info['savepath'].$info['savename'];
        //制作缩略图
        $img=new \Think\Image();
        //打开图片
        $img ->open($goods_img);

        //制作缩略图
        $goods_thumb ='Uploads/'.$info['savepath'].'thumb_'.$info['savename'];

        $img->thumb(450,450)->save($goods_thumb);
        $data['goods_img'] =$goods_img;
        $data['goods_thumb'] = $goods_thumb;
        return array('goods_img'=>$goods_img,'goods_thumb'=>$goods_thumb);
    }

    //更新数据
    public function update($data){

        //实现关于促销商品时间的格式化操作
        if($data['cx_price']>0){
            //设置商品为促销商品
            $data['start'] = strtotime($data['start']);
            $data['end'] = strtotime($data['end']);
        }else{
            $data['cx_price'] = 0.00;
            $data['start'] = 0;
            $data['end'] = 0;
        }

        $goods_id = $data['id'];
        //解决商品的缺货问题
        $goods_sn = $data['goods_sn'];
        if(!$goods_sn){
            //没有提交货号
            $data['goods_sn'] = 'JX'.uniqid();
        }else{
            //用户提交了货号，检查货号是否重复，并且将自己一起的货号排除在外
            $res = $this->where("goods_sn = '$goods_sn' AND id !=$goods_id")->find();
            if($res){
                $this->error="货号错误";
                return false;

            }
        }

        //解决扩展分类的问题
        //删除之前的扩展分类
        $extCateModel = D('GoodsCate');
        $extCateModel->where("goods_id=$goods_id")->delete();
        //将最新的扩展分类写入数据
        $ext_cate_id =I('post.ext_cate_id');
        $extCateModel->insertExtCate($ext_cate_id,$goods_id);

        //解决图片问题
        $res = $this->uploadImg();
        if($res){
            $data['goods_img'] =$res['goods_img'];
            $data['goods_thumb'] = $res['goods_thumb'];
        }

        //属性修改
        //1.删除当前已有的属性信息
        $goodsAttrModel = D('GoodsAttr');
        $goodsAttrModel->where('goods_id='.$goods_id)->delete();
        $attr = I('post.attr');
        $goodsAttrModel->insertAttr($attr,$goods_id);

        //实现商品相册图片上传
        //1.商品图片释放
        unset($_FILES['goods_img']);
        $upload =new \Think\Upload();
        $info = $upload->upload();
        foreach($info as $key=>$value){
            //上传图片后的地址
            $goods_img = 'Uploads/'.$value['savepath'].$value['savename'];
            //制作缩略图
            $img=new \Think\Image();
            //打开图片
            $img ->open($goods_img);

            //制作缩略图
            $goods_thumb ='Uploads/'.$value['savepath'].'thumb_'.$value['savename'];

            $img->thumb(100,100)->save($goods_thumb);

            $list[] = array(
                'goods_id'=>$goods_id,
                'goods_img'=>$goods_img,
                'goods_thumb'=>$goods_thumb,
            );
        }
        if($list){
            M('GoodsImg')->addAll($list);
        }

        return $this->save($data);
    }

    //改变逻辑删除的状态
    public function setStatus($goods_id,$isdel=0){
        return $this->where("id=$goods_id")->setField('isdel',$isdel);
    }

    //彻底删除
    public function  remove($goods_id){
        //1.删除商品的图片
        $goods_info = $this->findOneById($goods_id);
        if(!$goods_info){
            return false;
        }

        unlink($goods_info['goods_img']);
        unlink($goods_info['goods_thumb']);

        //2.删除商品的扩展分类
        D('GoodsCate')->where("goods_id=$goods_id")->delete();

        //3.删除商品的基本信息
        $this->where("id=$goods_id")->delete();
        return true;
    }

    //根据传递的参数返回热卖、推荐、新品的商品信息
    public function  getRecGoods($type)
    {
        return $this->where("is_sale=1 and $type=1")->limit(5)->select();
    }

    //获取当前正在促销的商品
    public function getCrazyGoods()
    {
        $where = "is_sale=1 and cx_price>0 and start<".time()." and end>".time();

        return $this->where($where)->limit(5)->select();
    }

    //获取当前分类下的商品
    public function getList(){
        $cate_id = intval(I('get.id'));

        //获取当前商品分类下的子分类
        $children = D('Admin/Category')->getChildren($cate_id);
        $children[] = $cate_id;

        $children = implode(',',$children);

        $where = "is_sale =1 and cate_id in ($children)";

        //计算当前分类下所有商品对应的最大价格以及最下价格
        $goods_info = $this->field('max(shop_price) max_price,min(shop_price) min_price,count(id) goods_count,group_concat(id) goods_ids')->where($where)->find();


        //获取商品的属性信息
        if($goods_info['goods_ids']){
            $attr = M('GoodsAttr')->alias('a')->field('distinct a.attr_id,a.attr_values,b.attr_name')->join('left join jx_attribute b on a.attr_id = b.id')->where('a.goods_id in ('.$goods_info['goods_ids'].')')->select();
            foreach($attr as $key=>$value){

                $attrwhere[$value['attr_id']][] = $value;
            }
        }

        //根据属性值条件查询获取商品信息
        if(I('get.attr')) {
            $attrParms = explode(',', I('get.attr'));
            //获取属性对应的商品id
            $goods = M('GoodsAttr')->field('group_concat(goods_id) goods_ids')->where(array('attr_values' => array('in', $attrParms)))->find();

            if($goods['goods_ids']){
                $where .= " and id in({$goods['goods_ids']})";
            }
        }
        //根据当前商品的个数判断是否需要显示价格区间
        if($goods_info['goods_count']>2){
            $cha = $goods_info['max_price']-$goods_info['min_price'];
            if($cha<100){
                $sec = 1; //显示价格区间的个数
            }elseif($cha<500){
                $sec = 2;
            }elseif($cha<1000){
                $sec = 3;
            }elseif($cha<5000){
                $sec = 4;
            }elseif($cha<10000){
                $sec = 5;
            }else{
                $sec =6;
            }
            $price = array();//每个区间对应的价格
            $first = ceil($goods_info['min_price']);
            $zl = ceil($cha/$sec);

            //循环运算每个区间的开始价格和结束价格
            for($i=0;$i<$sec; $i++){
                $price[] = $first.'-'.($first+$zl);
                $first += $zl;
            }
        }

        //接收价格条件进行查询
        if(I('get.price')){
            //有具体的价格条件传递
            $tmp = explode('-',I('get.price'));

            $where .= ' and shop_price>'.$tmp[0].' and shop_price<'.$tmp[1];
        }

        $p = I('get.p');

        $pagesize = 4;

        $count = $this->where($where)->count();
        $page = new \Think\Page($count,$pagesize);
        $show = $page->show();

        //接收排序字段
        $sort = I('get.sort')?I('get.sort'):'sale_number';
        $list = $this->where($where)->order($sort.' desc')->page($p,$pagesize)->select();

        return array('list'=>$list,'page'=>$show,'price'=>$price,'attrwhere'=>$attrwhere);

    }

}

