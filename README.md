# 简介

正方教务系统爬虫。

只要做很少的修改，即可用于不同的正方教务

#感谢

受到@lndj 的Lcrawl项目启发，项目地址:https://github.com/lndj/Lcrawl

我用browser kit库写了模拟登录的代码

现在不需要再去管viewstate了

# 安装

下载zip包或clone到本地后，执行：
```shell
composer install
```

# 配置

由于每一个学校的正方教务都不尽相同

所以还是需要修改一点东西

按照各自的正方教务的情况，修改ZFCrawler.php里的常量

```php
<?php
    const LINK_INFORMATION = "个人信息";    //个人信息链接名称
    const LINK_SCORE = "成绩查询";    //成绩链接名称
    const BTN_SCORE = "历年成绩";   //查询所有成绩的按钮名称
    const LINK_SCHEDULE = "学生个人课表";   //课表链接名称
    const LINK_CET = "等级考试查询";
    const LINK_EXAM = "学生考试查询"; //考试安排链接名称
    const LOGIN_BUTTON_SELECTOR = "Button1": //登录按钮id
    const SCHEDULE_SELECTOR = "#Table1"; //课表表格id
    const COMMON_TABLE_SELECTOR = "#DataGrid1"; //普通表格id
```

# 例子

```php

<?php
require 'vendor/autoload.php';
use ZFCrawler\ZFCrawler;
$stu_id = '20161111111';//学号
$password = 'password';//密码
$user = ['stu_id' => $stu_id, 'stu_pwd' => $password];
$crawler = new ZFCrawler($user, "http://210.37.0.27/");
try {
    //    $info = $crawler->getInformation(); //个人信息
//    $info = $crawler->getSchedule(); //课表
    $info = $crawler->getScore();   //成绩
//    $info = $crawler->getCet(); //等级成绩考试
//    $info = $crawler->getExam();//考试安排
    echo json_encode($info);
} catch (Exception $e) {
    echo $e->getMessage();
}
``` 
# Exception

在这里我只对少量的错误进行了判断，并且只是简单的throw一个Exception

容错可能不是很好，根据自己的情况进行修改

# License

MIT License
