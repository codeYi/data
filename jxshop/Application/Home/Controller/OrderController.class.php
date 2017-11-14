<?php
/**
 * Created by PhpStorm.
 * User: yihui
 * Date: 2017/10/9
 * Time: 23:50
 * 商品详情控制器
 */
namespace Home\Controller;
class OrderController extends CommonController
{
    //显示结算页面
    public function check()
    {
        //判断用户是否登录
        $this->checkLogin();

        $model = D('Cart');
        //获取购物车中的具体商品信息
        $data = $model->getList();

        //计算当前购物车总额
        $total = $model->getTotal($data);
        $this->assign('total', $total);
        $this->assign('data', $data);
        $this->display();
    }

    //用户的下单操作
    public function order()
    {
        $model = D('Order');
        $res = $model->order();
        if (!$res) {
            $this->error($model->getError());
        }
        //完成支付操作
        // require_once dirname(dirname(__FILE__)).'/config.php';
        //require_once dirname(__FILE__).'/service/AlipayTradeService.php';
        //require_once dirname(__FILE__).'/buildermodel/AlipayTradePagePayContentBuilder.php';
//        Vendor('pay.config');
        Vendor('pay.pagepay.service.AlipayTradeService');
        Vendor('pay.pagepay.buildermodel.AlipayTradePagePayContentBuilder');


        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $res['id'];

        //订单名称，必填
        $subject = "test";

        //付款金额，必填
        $total_amount = $res['total_price'];

        //商品描述，可空
        $body = "";
        $config=C('ALIPAY');

        //构造参数
        $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setOutTradeNo($out_trade_no);
//        var_dump($payRequestBuilder);exit;
        $aop = new \AlipayTradeService($config);
        /**
         * pagePay 电脑网站支付请求
         * @param $builder 业务参数，使用buildmodel中的对象生成。
         * @param $return_url 同步跳转地址，公网可以访问
         * @param $notify_url 异步通知地址，公网可以访问
         * @return $response 支付宝返回的信息
         */
        $response = $aop->pagePay($payRequestBuilder, $config['return_url'], $config['notify_url']);

        //输出表单
        var_dump($response);
    }

    public function returnUrl()
    {
        //require_once("config.php");
        //require_once 'pagepay/service/AlipayTradeService.php';
//        Vendor('pay.config');
        Vendor('pay.pagepay.service.AlipayTradeService');

        $arr = $_GET;
        $alipaySevice = new \AlipayTradeService(C('ALIPAY'));
        $result = $alipaySevice->check($arr);

        /* 实际验证过程建议商户添加以下校验。
        1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
        2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
        3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
        4、验证app_id是否为该商户本身。
        */
        if ($result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代码

            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

            //商户订单号
            $out_trade_no = htmlspecialchars($_GET['out_trade_no']);

            //支付宝交易号
            $trade_no = htmlspecialchars($_GET['trade_no']);


            //业务逻辑修改
            D('Order')->where('id='.$out_trade_no)->setField('pay_status',1);
            $this->redirect('Member/order');

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        } else {
            //验证失败
            echo "验证失败";
        }
    }

    public function pay()
    {
        $order_id = intval(I('get.order_id'));
        $model = D('Order');
        $res = $model->where('id='.$order_id)->find();
        if (!$res) {
            $this->error('参数错误');
        }
        if($res['pay_status']==1){
            $this->error('已经支付了该订单');
        }
        //完成支付操作
        // require_once dirname(dirname(__FILE__)).'/config.php';
        //require_once dirname(__FILE__).'/service/AlipayTradeService.php';
        //require_once dirname(__FILE__).'/buildermodel/AlipayTradePagePayContentBuilder.php';
//        Vendor('pay.config');
        Vendor('pay.pagepay.service.AlipayTradeService');
        Vendor('pay.pagepay.buildermodel.AlipayTradePagePayContentBuilder');


        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $res['id'];

        //订单名称，必填
        $subject = "test";

        //付款金额，必填
        $total_amount = $res['total_price'];

        //商品描述，可空
        $body = "";
        $config=C('ALIPAY');

        //构造参数
        $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setOutTradeNo($out_trade_no);
//        var_dump($payRequestBuilder);exit;
        $aop = new \AlipayTradeService($config);
        /**
         * pagePay 电脑网站支付请求
         * @param $builder 业务参数，使用buildmodel中的对象生成。
         * @param $return_url 同步跳转地址，公网可以访问
         * @param $notify_url 异步通知地址，公网可以访问
         * @return $response 支付宝返回的信息
         */
        $response = $aop->pagePay($payRequestBuilder, $config['return_url'], $config['notify_url']);

        //输出表单
        var_dump($response);
    }

}