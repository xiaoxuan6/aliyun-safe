# AliGreenAPI

阿里内容检测服务

阿里内容检测服务封装，包括垃圾文本、关键词文本检测以及对图片涉黄、暴恐、敏感检测

### 使用方法：

 $aliGreentAPI = AliGreenAPI::getInstance();
 
 ------------单一数据---------------
 
 $aliGreentAPI->checkText("在哪里场所可以进行xingjiaoyi");
 
 $aliGreentAPI->checkImg("http://nos.netease.com/yidun/2-0-0-4f903f968e6849d3930ef0f50af74fc2.jpg");
 
 
  ------------多条数据---------------
  
  文本检测
  
  $textArr = array("haha", "放学了", "交易");
  
  $aliGreentAPI->checkText($textArr);
  
  图片检测
  
  $imgArr = array("http://dun.163.com/res/web/case/terror_danger_3.jpg?3febae60454e63d020d04c66015a65e3","http://nos.netease.com/yidun/2-0-0-4f903f968e6849d3930ef0f50af74fc2.jpg");
  
  $aliGreentAPI->checkImg($imgArr);
 
