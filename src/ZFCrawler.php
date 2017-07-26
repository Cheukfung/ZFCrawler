<?php
/**
 * 简单通用的正方爬虫库
 *
 * @author Cheukfung Ho <cheukfung08@gmail.com>
 * @license MIT
 */


namespace ZFCrawler;

use Goutte\Client;
use ZFCrawler\Traits\Parser;

class ZFCrawler
{
    use Parser;

    protected $config = array(
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

    //user_agent
    private $ua = array(
        "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; AcooBrowser; .NET CLR 1.1.4322; .NET CLR 2.0.50727)",
        "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; Acoo Browser; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.0.04506)",
        "Mozilla/4.0 (compatible; MSIE 7.0; AOL 9.5; AOLBuild 4337.35; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727)",
        "Mozilla/5.0 (Windows; U; MSIE 9.0; Windows NT 9.0; en-US)",
        "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 2.0.50727; Media Center PC 6.0)",
        "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 1.0.3705; .NET CLR 1.1.4322)",
        "Mozilla/4.0 (compatible; MSIE 7.0b; Windows NT 5.2; .NET CLR 1.1.4322; .NET CLR 2.0.50727; InfoPath.2; .NET CLR 3.0.04506.30)",
        "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN) AppleWebKit/523.15 (KHTML, like Gecko, Safari/419.3) Arora/0.3 (Change: 287 c9dfb30)",
        "Mozilla/5.0 (X11; U; Linux; en-US) AppleWebKit/527+ (KHTML, like Gecko, Safari/419.3) Arora/0.6",
        "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.2pre) Gecko/20070215 K-Ninja/2.1.1",
        "Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9) Gecko/20080705 Firefox/3.0 Kapiko/3.0",
        "Mozilla/5.0 (X11; Linux i686; U;) Gecko/20070322 Kazehakase/0.4.5",
        "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.0.8) Gecko Fedora/1.9.0.8-1.fc10 Kazehakase/0.5.6",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/535.20 (KHTML, like Gecko) Chrome/19.0.1036.7 Safari/535.20",
        "Opera/9.80 (Macintosh; Intel Mac OS X 10.6.8; U; fr) Presto/2.9.168 Version/11.52",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.11 TaoBrowser/2.0 Safari/536.11",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.71 Safari/537.1 LBBROWSER",
        "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; LBBROWSER)",
        "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; QQDownload 732; .NET4.0C; .NET4.0E; LBBROWSER)",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.84 Safari/535.11 LBBROWSER",
        "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E)",
        "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E; QQBrowser/7.0.3698.400)",
        "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; QQDownload 732; .NET4.0C; .NET4.0E)",
        "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; SV1; QQDownload 732; .NET4.0C; .NET4.0E; 360SE)",
        "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; QQDownload 732; .NET4.0C; .NET4.0E)",
        "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C; .NET4.0E)",
        "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.89 Safari/537.1",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.0.1180.89 Safari/537.1",
        "Mozilla/5.0 (iPad; U; CPU OS 4_2_1 like Mac OS X; zh-cn) AppleWebKit/533.17.9 (KHTML, like Gecko) Version/5.0.2 Mobile/8C148 Safari/6533.18.5",
        "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.0b13pre) Gecko/20110307 Firefox/4.0b13pre",
        "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:16.0) Gecko/20100101 Firefox/16.0",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.64 Safari/537.11",
        "Mozilla/5.0 (X11; U; Linux x86_64; zh-CN; rv:1.9.2.10) Gecko/20100922 Ubuntu/10.10 (maverick) Firefox/3.6.10"
    );

    private $client;    //浏览器实例
    private $base_uri;  //教务系统地址
    private $stu_id;    //学号
    private $password;  //密码
    private $isLogin;   //是否已经登录
    private $mainPage;  //登录后的主页url

    /**
     * 构造函数
     *
     * @param object|array $user 用户名和密码
     * @param string $base_uri 教务系统地址
     * @param array $config 爬虫配置
     * @throws \Exception
     */
    public function __construct($user, $base_uri, $config = array())
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
        //反反爬
        /*user-agent方面，因为browserKit这个库会强行把ua设置为"Symfony BrowserKit"
         *因而造成setHeader之后访问页面会出错，如果要自定义ua的话，需要修改browser-kit库的源码
         */
//        $this->client->setHeader('User-Agent', $this->ua[rand(0, count($this->ua) - 1)] . rand(0, 10000));
        $this->client->setHeader('Accept-Encoding', 'gzip,deflate');
        $this->client->setHeader('Accept-Language', 'zh-CN,zh;q=0.8');
        $this->client->setHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8');
        $this->config = array_merge($this->config, $config);
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
         * 单点登录的接口是不需要验证码的
        */
        //先访问主页一次，绕过某些奇怪的反爬策略
        $this->client->request('get', $this->base_uri);
        $loginUrl = $this->base_uri . 'default2.aspx';
        $crawler = $this->client->request('get', $loginUrl);
        $form = $crawler->selectButton($this->config['btn_login'])->form();
        $form->setValues(array(
            'txtUserName' => $this->stu_id,
            'TextBox2' => $this->password,
            'txtSecretCode' => '',
            'RadioButtonList1' => '学生',
        ));
        $crawler = $this->client->submit($form);
        $this->checkResult($crawler);
        $html = $crawler->html();
        $page = $crawler->getBaseHref();
        //跳转到xs_main的页面的alert为学生的提示，不是登录错误，所以需要排除
        if (!strpos($page, "xs_main") && preg_match("/alert\((.*?)\)/", $html, $fail)) {
            throw new \Exception("登录失败:{$fail[1]}", 10001);
        } else {
            //登录成功
            //TODO 缓存cookie功能
            $this->mainPage = $page;
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
            throw new \Exception("请检查参数是否正确:{$link_value}:{$btn_value}", 10002);
        }
    }

    /**
     * 检查是否成功打开页面(检查网络)
     *
     * @param \Symfony\Component\DomCrawler\Crawler $crawler
     * @throws \Exception
     */
    private function checkResult($crawler)
    {
        $status = $this->client->getInternalResponse()->getStatus();
        if ($status != 200) {
            throw new \Exception("网络错误:code{$status}", 20001);
        }
        if (preg_match("/ERROR/", $crawler->text())) {
            throw new \Exception("访问失败:教务系统出错", 20002);
        }
    }

    /**
     * 获取成绩
     * @return array
     */
    public function getScore()
    {
        $crawler = $this->httpQuery($this->config['link_score'], $this->config['btn_score']);
        return $this->parserCommonTable($crawler, $this->config['table_score']);
    }

    /**
     * 获取课程表
     * @return array
     */
    public function getSchedule()
    {
        $crawler = $this->httpQuery($this->config['link_schedule']);
        return $this->parserSchedule($crawler, $this->config['table_schedule']);
    }

    /**
     * 获取等级考试成绩
     * @return array
     */
    public function getCet()
    {
        $crawler = $this->httpQuery($this->config['link_cet']);
        return $this->parserCommonTable($crawler, $this->config['table_common']);
    }

    /**
     * 获取考试安排
     * @return array
     */
    public function getExam()
    {
        $crawler = $this->httpQuery($this->config['link_exam']);
        return $this->parserCommonTable($crawler, $this->config['table_common']);
    }

    /**
     * 获取个人信息
     * @return array
     */
    public function getInformation()
    {
        $crawler = $this->httpQuery($this->config['link_info']);
        return $this->parserInfo($crawler);
    }
}
