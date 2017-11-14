<?php if (!defined('THINK_PATH')) exit();?><!-- $Id: category_info.htm 16752 2009-10-20 09:59:38Z wangleisvn $ -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>ECSHOP 管理中心 - 商品添加 </title>
<meta name="robots" content="noindex, nofollow">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href="/Public/Admin/Styles/general.css" rel="stylesheet" type="text/css" />
<link href="/Public/Admin/Styles/main.css" rel="stylesheet" type="text/css" />
</head>
<body>
<h1>

    <span class="action-span"><a href="<?php echo U('category/index');?>">商品分类</a></span>
    <span class="action-span1"><a href="<?php echo U('index/index');?>">ECSHOP 管理中心</a></span>
    <span id="search_id" class="action-span1"> - 添加分类 </span>
    <div style="clear:both"></div>

</h1>
<div class="main-div">
    
        <div id="tabbody-div">
            <form enctype="multipart/form-data" action="" method="post">
                <table width="90%" class="table" align="center">
                    <tr>
                        <td class="label">库存量：</td>
                        <td><input type="text" name="goods_number" value="<?php echo ($info["goods_number"]); ?>"size="30" />
                            <font color="red">*</font>
                    </tr>

                </table>
                <input type="hidden" name="goods_id" value="<?php echo ($_GET['goods_id']); ?>">
                <div class="button-div">
                    <input type="submit" value=" 确定 " class="button"/>
                    <input type="reset" value=" 重置 " class="button" />
                </div>
            </form>
        </div>

</div>
<div id="footer">
共执行 3 个查询，用时 0.162348 秒，Gzip 已禁用，内存占用 2.266 MB<br />
版权所有 &copy; 2005-2012 上海商派网络科技有限公司，并保留所有权利。</div>
</body>
</html>
<script type="text/javascript" src="/Public/Admin/Js/jquery-1.8.3.min.js"></script>

    <script type="text/javascript">
        //实例化编辑器
        //建议使用工厂方法getEditor创建和引用编辑器实例，如果在某个闭包下引用该编辑器，直接调用UE.getEditor('editor')就能拿到相关的实例
        var ue = UE.getEditor('editor');


        //实现扩展分类的点击按钮增加select
        $('#addExtCate').click(function(){
            //复制select
            var newSelect = $(this).next().clone();
            //将内容写入到td中
            $(this).parent().append(newSelect);
        });
        //
        $('#tabbar-div p span').click(function(){
            $('.table').hide();
            var i = $(this).index();
            $('.table').eq(i).show();
        });

        $('#type_id').change(function(){
            //获取当前被选中的类型标识
            var type_id = $(this).val(); //选项的ID值
            $.ajax({
                url:"<?php echo U('showAttr');?>",
                data:{type_id:type_id},
                type:'post',
                success:function(msg){
                    $('#showAttr').html(msg);
                }
            });
        });

        function clonethis(obj){
            //获取当前的tr对象
            var current = $(obj).parent().parent();
            if($(obj).html() == "[+]"){
                //复制当前的tr
                var newtr = current.clone();
                //将当前的特殊符号改为[-];
                newtr.find('a').html('[-]');
                current.after(newtr);
            }else{
                current.remove();
            }
        }

        //增加图片按钮
        $('.addNewPic').click(function(){
            //将上传框tr进行复制
            var newfile = $(this).parent().parent().next().clone();
            //将复制的上传框内容追加到table中
            $('.pic').append(newfile);
        });
    </script>