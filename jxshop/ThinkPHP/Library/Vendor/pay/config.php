<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2016080800191693",

		//商户私钥
		'merchant_private_key' => "MIIEpAIBAAKCAQEAwofofmKWbJLkuYFpA13EHYWHK6+kPUwNFOti3znl7vpbqOecEhH6Uy3T/xTWxciv8o5AVB6xZdtuPNr+Ugr3T8ydu0wH1IHoHRPoH+OmDdurzMbKhL8nda9XXpecjr1BMf8WM9E1odNm51+PwIuzSVMoZaXWiPrBFn/7e2Fd93ODhTQE4sRNk2THYg97rHnwjBeA1nKqkyMcIzQIZ8lk5vTIQ/JjU49nun31/fyRiSFIQfwW5YqysS71Ln5nwheXGSbnmELHeuhlsJv72aG/+4sU8MVo5TYXXxTQXkuN4rwib5FqOHE18wHtDFl1pED3L7znsyGfAn+UmkPDlkHOHQIDAQABAoIBAG4+gZcmniM3+GHdIjtjJ2KnqtwqNUT71aoWYDkr4dBWmB2X7M4c3CXJogw8rh72xigLUA+cOWMFQWtBnMG4L0JNLtLXmtRnLrlT78gqxo+x/6IUwu8KOf2q+jPEblhCjzEbZGTHWsK0QOw9LwWq5ldWxl0c6AFHQqhSrZ3Vp2A1XIieCM5hG5RDxqs5BJoWt6oKP2Rv2Xb5fC3og0EP57+dJfd3hOn0hBTz9Vt/X+msHFLmzVQD5UFBZWnrsoODHvA6rZ3/fvdd3yENgNVwOnWkPXPXUl8m9b4OTGw7vgPizq3b+kVe2XWFL7WduOWqI/5u/5yBe/5XwzW+5MIu6CECgYEA6g1aK2k+XdYP+/v3OTScjCceY7D6lS8j/xiyEzEamUS70ko6p2XtZotQdvD7KmAYTgOb9OcvtUA4O/r1RKT7awqQOMDkBL0VA9LzC9w1gZUl9ABYxXIAXZhNBc7n5jDQ8h0NPpOU15bnStfmxRzvg+6wPH3eIWWRpTVNNeIdBQkCgYEA1MXPUk6TXtw/zMqHnli296MOSsAVWNRcZAFNGphrZLyRInFPC73tAp1eylfOb5UxxKyDKN+q9On8w4DuuZe4FK58q9FRUDpZd/GMOTQ/HkYzlyzXC7gEVWtbI5pFihKsDhiA0dufQm9inm+ay1vocWjo5UZyRIW5Ougi7o1DuXUCgYEAuY+ieJ51IsJPY/GNPR54Kynsjw9GbDa2rE3xCQQ6WV/EJWJShFtsT4uOXO6Lwznyqw9ze0Q7b9EdhHhgMm83IdN7KnPLk/IxEhoSNcn95eHQ+FW6C7hpj8rq7frvonH7hqj/0igBrrdmYtEvlgFt58S6lwsXrJSxVzEeKOU/KrkCgYAPicqUJizY9piqKfgxdiUWfI/koZZtgESnAPlSgeMF3kdzDndJUjtmv3tWp4bp2ylhRX/mkj7318afuGC2qP/Gct+j5ItdooU6HyewXcJmQZYu6hViQ7P9UPO86908MhTiiqONr843mc7H5zqUs0eMUK8BX12k52cZiC5U4XgaqQKBgQDXKsivaMqDgni13nGdzHuqJSU8EHod8baqkMosnJeDor+DHaH2Kq2O5qnngtJF1WDvR2Y+bKh6hyVWxVsB6Fcl7C4s/X7YCvTtawYfnvhRHldq7h2KJM6VxKMWxAIUXSAsOOV/czBJTK5Pb8QOdKYmR5tm/U6h52Jc1rRwZAB4Kg==",
		
		//异步通知地址
		'notify_url' => "",
		
		//同步跳转
		'return_url' => "http://jxshop.com/home/Order/returnUrl.html",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipaydev.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsb9wxvzyxURhGduQ+GaEypx/Ab7AYVk2yoCtrUupWt4G3PDvqZ+BQ4mEpKiyhhn0bczX9vRrWsxFnMENz9Sh+gPe740uRAr9rScxu8olIbMiHcbLZbqvwIDhiDwzfGMHlVipaqS+W+eted96XLfi+kWYG4m2ILh4KTLf0du8esGH/Uai4MtPqZAevs6UbJTlkAvAzEqS+fr9N0s9rEDzGBePXKTO/fDBy1GvgE3piO0Z91aLX2uVJqMVfEptqYFpykEVVHS4+IaLwiwdtirkctYpUjGNAuLEc7c2Qs3s4CaK96e4lQ8MuAucy2zZkVqcFY/MbGLMlDmUnLW/TPmQBwIDAQAB",
);