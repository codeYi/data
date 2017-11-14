<?php 
namespace Home\Model;
use Think\Model;
class CartModel extends Model{
	//指定自定义字段信息
	protected $fields=array('id','user_id','goods_id','goods_count','goods_attr_ids');
	//作用就是实现具体商品信息加入到购物车
	public function addCart($goods_id,$goods_count,$attr)
	{
		//将属性信息做一个从小到大的排序操作
		sort($attr);//目的就是为了考虑后期库存量的检查
		//将属性信息转换为字符串的格式
		$goods_attr_ids = $attr?implode(',',$attr):'';
		//实现库存量检查
		$res = $this->checkGoodsNumber($goods_id,$goods_count,$goods_attr_ids);
		if(!$res){
			$this->error='库存不足';
			return false;
		}
		//获取用户的ID标识
		$user_id = session('user_id');
		if($user_id){
			//说明用户已经登录 接下来数据的操作去操作对应的数据表
			//根据当前要写入的数据信息判断数据库中书否存在，如果存在直接更新对应的数量否则直接写入即可
			$map =array(
				'user_id'=>$user_id,
				'goods_id'=>$goods_id,
				'goods_attr_ids'=>$goods_attr_ids
			);
			$info = $this->where($map)->find();
			if($info){
				//说明目前数据已经存在 已经存在需要更新对应的数量
				$this->where($map)->setField('goods_count',$goods_count+$info['goods_count']);
			}else{
				//说明目前数据不存在 直接写入数据即可
				$map['goods_count']=$goods_count;
				$this->add($map);
			}
		}else{
			//表示用户没有登录 对应的应该操作cookie中的数据
			//规定关于商品加入购物车cookie中记录数据 使用cart的名称，对于数据从php中的数组转换为字符串是通过序列化操作
			$cart=unserialize(cookie('cart'));
			//先判断目前添加的商品信息是否存在
			//首先先拼接出对应的key下标
			$key = $goods_id.'-'.$goods_attr_ids;
			if(array_key_exists($key, $cart)){
				//说明目前添加的商品信息已经存在
				$cart[$key]+=$goods_count;
			}else{
				//说明目前添加的商品信息不存在
				$cart[$key]=$goods_count;
			}
			//处理完之后需要将最新的数据再次写入cookie
			cookie('cart',serialize($cart));
		}
		return true;
	}

	//作用就是实现商品库存的检查
	public function checkGoodsNumber($goods_id,$goods_count,$goods_attr_ids)
	{
		//1、检查总的库存量
		$goods = D('Admin/Goods')->where("id=$goods_id")->find();
		if($goods['goods_number']<$goods_count){
			//表示目前库存不够
			return false;
		}
		//2、根据单选属性检查对于的属性组合库存量
		if($goods_attr_ids){
			$where="goods_id=$goods_id and goods_attr_ids='$goods_attr_ids'";
			$number = M('GoodsNumber')->where($where)->find();
			if(!$number || $number['goods_number']<$goods_count){
				//表示库存不够
				return false;
			}
		}
		return true;
	}

	//具体实现购物车cookie中的数据转移到数据库中
	public function cookie2db()
	{
		//获取cookie中购物车的数据
		$cart = unserialize(cookie('cart'));
		//获取当前用户的ID标识
		$user_id = session('user_id');
		if(!$user_id){
			return false;
		}
		foreach ($cart as $key => $value) {
			//先将目前的下标对应的商品ID以及属性值组合拆分出来
			$tmp = explode('-',$key);
			//先拼接条件查询当前商品信息是否存在
			$map=array(
				'user_id'=>$user_id,
				'goods_id'=>$tmp[0],
				'goods_attr_ids'=>$tmp[1]
			);

			$info = $this->where($map)->find();
			if($info){
				//说明目前的商品信息存在，直接更新对应的数量即可
				$this->where($map)->setField('goods_count',$value+$info['goods_count']);
			}else{
				//说明目前的商品信息不存在，直接将数据写入即可
				$map['goods_count']=$value;
				$this->add($map);
			}
		}
		//将目前cookie中的数据清空掉
		cookie('cart',null);
	}
}