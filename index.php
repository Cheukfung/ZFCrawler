<?php
ini_set("display_errors", "On");
require 'vendor/autoload.php';

use ZFCrawler\ZFCrawler;

$stu_id = '2017000000';//学号
$password = '000000';//密码
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
    echo $e->getCode() . $e->getMessage();

}
