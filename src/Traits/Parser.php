<?php

namespace ZFCrawler\Traits;

use Symfony\Component\DomCrawler\Crawler;

/**
 * 提取页面信息
 */
trait  Parser
{
    /**
     * 提取课表，并处理跨行
     *
     * @param Crawler $crawler
     * @param string $selector 课表table ID
     * @return array
     * @throws \Exception
     */
    public function parserSchedule($crawler, $selector = self::SCHEDULE_SELECTOR)
    {
        $this->checkContent($crawler);
        try {
            $crawler = $crawler->filter($selector);
            $page = $crawler->children();
        } catch (\InvalidArgumentException $e) {
            throw new \Exception('无法获取课表信息');
        }
        //删除第一、二行(星期、早晨）
        $page = $page->reduce(function (Crawler $node, $i) {
            if ($i == 0 || $i == 1) {
                return false;
            }
        });
        $array = $page->each(function (Crawler $node, $i) {
            return $node->children()->each(function (Crawler $node, $j) {
                $span = $node->attr('rowspan');
                return $node->html() . (empty($span) ? '' : "span={$span}");
            });
        });
        /*这里一定要先遍历列再遍历行
          否则可能会出现课程错乱  */
        for ($i = 0; $i < 9; $i++) {//列数
            for ($j = 0; $j < count($array); $j++) {//行数
                if (is_array($array[$j][$i]) || $i == 1) {
                    //处理过的跨行或者是第二列则跳过，第二列不会出现跨行也不是课程
                    continue;
                }
                if (preg_match("/span=(.?)/", $array[$j][$i], $regs)) { // 如果跨行
                    $array[$j][$i] = preg_replace("/span=./", '', $array[$j][$i]);
                    $array[$j][$i] = $i == 0 ? $array[$j][$i] : self::formatLesson($array[$j][$i]);
                    $k = $regs[1];
                    while (--$k > 0) { // 插入跨行元素
                        $array[$j + $k] = array_merge(array_slice($array[$j + $k], 0, $i), array($array[$j][$i]), array_splice($array[$j + $k], $i));
                    }
                } else {
                    $array[$j][$i] = $i == 0 ? $array[$j][$i] : self::formatLesson($array[$j][$i]);
                }
            }
        }
        for ($j = 0; $j < count($array); $j++) {
            array_shift($array[$j]);
            array_shift($array[$j]);
        }
        return $array;
    }

    /**
     * 格式化课程
     *
     * 提取单个<td>内(单个或多个)课程为数组
     * 分别提取课程、周数、教师、上课地点等
     *
     * @param string $lesson
     * @param string $delimiter 多个课程间有多少个<br>,例如 "<br><br>"
     * @return array
     */
    private function formatLesson($lesson, $delimiter = "<br><br>")
    {
        $lesson = trim($lesson);
        /*由于老师填写课表有误等奇葩原因
         *空课程的td可能包含中文的空格，
         * 或者其他奇奇怪怪的编码和字符，
         * 为了方便和兼容性，只判断长度*/
        if (strlen($lesson) < 6) {
            return [];
        }
        $lesson = preg_replace('/(<br><br><font.*?font>)/', '', $lesson);

        /*如果多个课程的br比正常情况的多出一个
         *说明 教师/地点/上课时间 其中一个缺失
         *为了数据的一致性，添加一个"未知"  */
        if (preg_match("/<br>{$delimiter}/", $lesson, $regs)) {
            $lesson = str_replace('<br>' . $delimiter, '<br>未知' . $delimiter, $lesson);
            $lesson = preg_replace('/<br>$/', '<br>未知', $lesson);//最后一个课程
        }
        $array = explode($delimiter, $lesson);
        foreach ($array as &$item) {
            $tempArr = explode('<br>', $item);
            $week = [];
            foreach ($tempArr as $s) {
                if (preg_match("/{第(.*?)周/", $s, $regs)) {
                    $week = self::formatWeek($s, $regs[1]);
                    break;
                }
            }
            $tempArr['weekRange'] = $week;
            $item = $tempArr;
        }
        return $array;
    }

    /**
     * 识别周数
     *
     * @param string $baseString 用于判断单双周
     * @param string $weekString
     * @return array
     */
    private function formatWeek($baseString, $weekString)
    {
        $oddOrEven = '';
        if (preg_match("/(双)|(单)/", $baseString, $regs2)) {
            $oddOrEven = $regs2[1];
        }
        $numArr = explode('-', $weekString);
        $weekRange = [];
        for ($i = (int)$numArr[0]; $i < $numArr[1] + 1; $i++) {
            if ($oddOrEven == "单") {
                if ($i % 2 == 0) continue;
            } else if ($oddOrEven == '双') {
                if ($i % 2 == 0) continue;
            }
            array_push($weekRange, $i);
        }
        return $weekRange;
    }


    /**
     * 提取普通表格
     * 比如成绩、考试安排等表格
     * 某些正方教务表格不为DataGrid1
     * 这里应该可以通过查找页面的第一
     * 个table来识别，而不用id
     *
     * @param Crawler $crawler
     * @param string $selector
     * @return array
     */
    protected function parserCommonTable($crawler, $selector = self::COMMON_TABLE_SELECTOR)
    {
        if (!$this->checkContent($crawler)) {
            return [];
        }
        $crawler = $crawler->filter($selector);
        $page = $crawler->children();
        $data = $page->each(function (Crawler $node, $i) {
            return $node->children()->each(function (Crawler $node, $j) {
                return $node->text();
            });
        });
        //去除首行的标题
        unset($data[0]);
        return array_merge($data);
    }


    /**
     * 提取个人信息
     *
     * @param Crawler $crawler
     * @return array
     */
    protected function parserInfo($crawler)
    {
        $this->checkContent($crawler);
        $name = $crawler->filter("#xm")->text();//姓名
        $studentID = $crawler->filter("#xh")->text();//学号
        $sexual = $crawler->filter("#lbl_xb")->text();//性别
        $college = $crawler->filter("#lbl_xy")->text();//学院
        $major = $crawler->filter("#lbl_zymc")->text();//专业名称
        $class = $crawler->filter("#lbl_xzb")->text();//行政班
        $highSchool = $crawler->filter("#lbl_byzx")->text();//毕业中学
        $idCard = $crawler->filter("#lbl_sfzh")->text();//身份证号
        $examNum = $crawler->filter("#lbl_ksh")->text();//考生号
        $province = $crawler->filter("#lbl_lys")->text();//来源省
        $city = $crawler->filter("#lbl_lydq")->text();//来源地区
        return array(
            'studentID' => $studentID,
            'name' => $name,
            'gender' => $sexual,
            'idCard' => $idCard,
            'college' => $college,
            'major' => $major,
            'class' => $class,
            'highSchool' => $highSchool,
            'examNum' => $examNum,
            'province' => $province,
            'city' => $city
        );
    }

    /**
     * 检查页面是否正确
     * 若没有进行评教或学生欠费
     * 此时查询成绩教务系统会
     * 返回一个alert提示
     *
     * @param Crawler $crawler
     * @throws \Exception
     * @return boolean
     */
    protected function checkContent($crawler)
    {
        if (is_null($crawler)) {
            return false;
        }
        if (preg_match("/alert\((.*?)\)/", $crawler->html(), $fail) && !preg_match("/name=\"__VIEWSTATE\" /", $crawler->html(), $regs)) {
            $err = $this->unicode_decode($fail[1]);
            throw new \Exception("查询失败:" . $err);
        }
        return true;
    }

    /**
     * unicode转中文
     *
     * @param string $uniStr
     * @param string $encoding
     * @param string $prefix
     * @param string $postfix
     * @return string
     */
    public function unicode_decode($uniStr, $encoding = 'utf-8', $prefix = '&#', $postfix = ';')
    {
        $arrUni = explode($prefix, $uniStr);
        $uniStr = '';
        for ($i = 1, $len = count($arrUni); $i < $len; $i++) {
            if (strlen($postfix) > 0) {
                $arrUni[$i] = substr($arrUni[$i], 0, strlen($arrUni[$i]) - strlen($postfix));
            }
            $temp = intval($arrUni[$i]);
            $uniStr .= ($temp < 256) ? chr(0) . chr($temp) : chr($temp / 256) . chr($temp % 256);
        }
        return iconv('UCS-2', $encoding, $uniStr);
    }
}