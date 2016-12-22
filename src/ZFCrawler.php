<?php
/**
 * 简单通用的正方爬虫库
 *
 * @author Cheukfung Ho <cheukfung08@gmail.com>
 * @license MIT
 */


namespace ZFCrawler;

use ZFCrawler\Traits\Parser;
use Goutte\Client;

class ZFCrawler
{
    use Parser;

    const LINK_INFORMATION = "个人信息";    //个人信息链接名称
    const LINK_SCORE = "成绩查询";    //成绩链接名称
    const BTN_SCORE = "历年成绩";   //查询所有成绩的按钮名称
    const LINK_SCHEDULE = "学生个人课表";   //课表链接名称
    const LINK_CET = "等级考试查询";
    const LINK_EXAM = "学生考试查询"; //考试安排链接名称

    const LOGIN_BUTTON_SELECTOR = "Button1": //登录按钮id
    const SCHEDULE_SELECTOR = "#Table1"; //课表表格id
    const COMMON_TABLE_SELECTOR = "#DataGrid1"; //普通表格id

    private $base_uri;
    private $stu_id;
    private $password;
    private $client;
    private $isLogin;

    private $mainPage;//登录后的主页url

    /**
     * 构造函数
     *
     * @param object|array $user
     * @param string $base_uri
     * @throws \Exception
     */
    public function __construct($user, $base_uri)
    {
        if (is_array($user) && $user['stu_id'] && $user['stu_pwd']) {
            $this->stu_id = $user['stu_id'];
            $this->password = $user['stu_pwd'];
        } elseif (is_object($user) && $user->stu_id && $user->stu_pwd) {
            $this->stu_id = $user->stu_id;
            $this->password = $user->stu_pwd;
        } else {
            throw new \Exception("必须传入学号姓名,例： ['stu_id' => '2012xxxxx', 'stu_pwd' => 'xxxx']", 1, "login");
        }
        $this->client = new Client();
        $this->base_uri = $base_uri;
    }

    /**
     * 模拟登录
     *
     * @return \Symfony\Component\DomCrawler\Crawler
     * @throws \Exception
     */
    private function login()
    {
        if ($this->isLogin) {
            return $this->client->request('get', $this->mainPage);
        }
        /* 这里用空验证码来绕过，如果不能绕过的，可以利用default_ysdx.aspx这个页面
         * 如果连default_ysdx.aspx页面也没有，那就用正方的单点登录接口绕过吧
        */
        $uri = $this->base_uri . 'default2.aspx';
        $crawler = $this->client->request('get', $uri);
        $form = $crawler->filter->(self::LOGIN_BUTTON_SELECTOR)->form();
        $form->setValues(array(
           'txtUserName' => $this->stu_id,
           'TextBox2' => $this->password,
           'txtSecretCode' => '',
           'RadioButtonList1' => '学生',
       ));
        $crawler = $this->client->submit($form);
        $html = $crawler->html();
        if (preg_match("/alert\((.*?)\)/", $html, $fail)) {
            throw new \Exception("登录失败:$fail[1]");
        } else {
            //登录成功
            //TODO 缓存cookie功能
            $this->mainPage = $crawler->getBaseHref();
            $this->isLogin = true;
            return $crawler;
        }
    }

    /**
     * 模拟点击链接或按钮
     *
     * 仅仅传入$link_value则点击该链接(get);
     * 传入$btn_value则先点击$link_value链接,再点击$btn_value提交表单
     *
     * @param string $link_value <a>连接名称
     * @param string $btn_value 提交按钮名称
     * @return \Symfony\Component\DomCrawler\Crawler
     * @throws \Exception
     */
    private function httpQuery($link_value, $btn_value = "")
    {
        try {
            $link = $this->login()->selectLink($link_value)->link();
            if (empty($btn_value)) {
                return $this->client->click($link);
            } else {
                $form = $this->client->click($link)->selectButton($btn_value)->form();
                return $this->client->submit($form);
            }
        } catch (\InvalidArgumentException $e) {
            throw new \Exception("请检查参数是否正确:{$link_value}:{$btn_value}");
        } catch (\ConnectException $e) {
            throw new \Exception('网络出错');
        }
    }

    /**
     * 获取成绩
     * @return array
     */
    public function getScore()
    {
        $crawler = $this->httpQuery(self::LINK_SCORE, self::BTN_SCORE);
        return $this->parserCommonTable($crawler, '#Datagrid1');
    }

    /**
     * 获取课程表
     * @return array
     */
    public function getSchedule()
    {
        $crawler = $this->httpQuery(self::LINK_SCHEDULE);
        return $this->parserSchedule($crawler);
    }

    /**
     * 获取等级考试成绩
     * @return array
     */
    public function getCet()
    {
        $crawler = $this->httpQuery(self::LINK_CET);
        return $this->parserCommonTable($crawler);
    }

    /**
     * 获取考试安排
     * @return array
     */
    public function getExam()
    {
        $crawler = $this->httpQuery(self::LINK_EXAM);
        return $this->parserCommonTable($crawler);
    }

    /**
     * 获取个人信息
     * @return array
     */
    public function getInformation()
    {
        $crawler = $this->httpQuery(self::LINK_INFORMATION);
        return $this->parserInfo($crawler);
    }
}
