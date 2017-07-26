# 简介

正方教务系统爬虫。

一份代码，通用全部学校的正方教务系统

#感谢

受到@lndj 的Lcrawl项目启发，项目地址:https://github.com/lndj/Lcrawl

我用browser kit库写了模拟登录的代码

现在不需要再去管viewstate了,只需要知道链接的中文名称，和表格的ID即可

# 安装

下载zip包或clone到本地后，执行：
```shell
composer install
```

或者直接在项目文件夹执行:
```shell
composer require cheukfung/zfcrawler
```

# 配置

由于每一个学校的正方教务都不尽相同，为了适应每一个学校，在实例化的时候传入配置参数即可

# 例子

```php

<?php
require 'vendor/autoload.php';
use ZFCrawler\ZFCrawler;
$stu_id = '20161111111';//学号
$password = 'password';//密码
$user = ['stu_id' => $stu_id, 'stu_pwd' => $password];
/*把下面的默认信息修改为你的学校教务系统的信息即可
 *不传如config参数则使用默认参数
 */
$config = array(
        'link_info' => "个人信息",           //个人信息链接名称
        'link_score' => "成绩查询",          //成绩链接名称
        'link_schedule' => "学生个人课表",   //课表链接名称
        'link_cet' => "等级考试查询",        //等级成绩链接名称
        'link_exam' => "学生考试查询",       //考试安排链接名称
        'btn_score' => "历年成绩",           //查询所有成绩的按钮名称
        'btn_login' => "Button1",           //登录按钮标识:id或name
        'table_schedule' => '#Table1',      //课表表格id
        'table_score' => '#Datagrid1',      //成绩表格id
        'table_common' => "#DataGrid1",     //普通表格id
    );
$baseUri="http://210.37.0.27/";
$crawler = new ZFCrawler($user, $baseUri,$config);
try {
    //    $info = $crawler->getInformation(); //个人信息
//    $info = $crawler->getSchedule(); //课表
    $info = $crawler->getScore();   //成绩
//    $info = $crawler->getCet(); //等级成绩考试
//    $info = $crawler->getExam();//考试安排
    echo json_encode($info);
} catch (Exception $e) {
     echo $e->getCode() . $e->getMessage();
}
``` 
# Exception

在这里我只对少量的错误进行了判断，并且只是简单的throw一个Exception

容错可能不是很好，根据自己的情况进行修改

# Exception Code:
    10001:登录失败,教务系统返回相关alert
    10002:无法根据中文名称获取相关功能的页面url(原因：配置错误、教务系统关闭了页面入口)
    10003:查询成绩或其他信息出错，教务系统返回alert，比如没有评教时查询成绩
    10004:处理课表时出错，无法找到table
***
    20001:页面访问出错(非200状态码)，请检查网络的连通性，也不排除被反爬限制了
    20002：正方教务系统返回ERROR错误，原因未知，可能为：访问量太大，教务系统崩溃，被反爬策略识别，登录参数错误等等
    
# PHP 5.4

由于用到guzzleHttp，所以在php5.4里面运行会出错，原因是5.5之前没有curl_reset这个函数

解决方法就是把 guzzlehttp/guzzle/src/Handler/CurlFactory.php的curl_reset($resource);改为 $resource = curl_init();

# 案例

课程表

![](http://ww3.sinaimg.cn/large/6916d6ffgw1faznt2fav0j209h0gv3zr.jpg)

成绩

![](http://ww2.sinaimg.cn/large/6916d6ffgw1fazntlsytxj209h0gvmys.jpg)

# License

MIT License
