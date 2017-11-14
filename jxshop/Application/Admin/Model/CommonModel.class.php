<?php
/**
公共模型
 */
namespace Admin\Model;

use Think\Model;

class CommonModel extends Model
{
    public function findOneById($id){
        return $this->where('id='.$id)->find();
    }
}