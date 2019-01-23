# AliGreen

阿里内容检测服务

阿里内容检测服务封装，包括垃圾文本、关键词文本检测以及对图片涉黄、暴恐、敏感检测
### 安装：
   1、引入包
   
    composer require james.xue/ali-safe-api
    
   2、发布config
   
    php artisan vendor:publish --tag=config
    
### 使用方法：

    前提：配置config/aliyun.php

    use James\AliGreen\AliGreen;
 
    $ali = AliGreen::getInstance();
 
   ------------字符串---------------
 
    $ali->checkText("约炮");
 
    $ali->checkImg("http://nos.netease.com/yidun/2-0-0-4f903f968e6849d3930ef0f50af74fc2.jpg");
 
 
  ------------数组---------------
  
   文本检测
  
    $textArr = array("测试", "约炮");
  
    $ali->checkText($textArr);
  
  图片检测
  
    $imgArr = array("http://nos.netease.com/yidun/2-0-0-4f903f968e6849d3930ef0f50af74fc2.jpg", "http://blog.jstm365.com/images/page_bg.jpg");
  
    $result = $ali->checkImg($imgArr);
 
