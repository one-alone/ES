<?php

/**
 * 公用的非业务性方法
 * User: LJC
 * Date: 2017/9/29
 * Time: 14:53
 */

use GuzzleHttp\Client;
use OSS\Core\OssException;
use OSS\Core\OssUtil;

class Common
{
    //递归创建文件夹
    public static   function createFolder($path)
    {
        if(!file_exists($path)){
            mkdir($path,0777,true);//0777表示文件夹权限，windows默认已无效，但这里因为用到第三个参数，得填写；true/false表示是否可以递归创建文件夹
        }
    }
    //PHP根据当前日期获取星期几
    public static  function getMonthTxt($date,$lang = 0){
        //转换日期格式
        $date_str=date('n',strtotime($date.'-01'));

        //自定义星期数组
        if($lang == 0){
            $weekArr=array("1月","2月","3月","4月","5月","6月","7月",'8月','9月','10月','11月','12月');
        }elseif($lang == 1){
            $weekArr=array('JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
        }
        //获取数字对应的星期
        return $weekArr[$date_str-1];
    }

        //PHP根据当前日期获取星期几
    public static  function getWeekTxt($date,$lang = 0){
        //转换日期格式
        $date_str=date('Y-m-d',strtotime($date));
        //把日期拆分成数组
        $arr=explode("-", $date_str);
        //参数赋值
        //年
        $year=$arr[0];
        //月，输出2位整型，不够2位右对齐
        $month=sprintf('%02d',$arr[1]);
        //日，输出2位整型，不够2位右对齐
        $day=sprintf('%02d',$arr[2]);
        //时分秒默认赋值为0；
        $hour = $minute = $second = 0;
        //转换成时间戳
        $strap = mktime($hour,$minute,$second,$month,$day,$year);
        //获取数字型星期几
        $number_wk=date("w",$strap);
        //自定义星期数组
        if($lang == 0){
            $weekArr=array("星期日","星期一","星期二","星期三","星期四","星期五","星期六");
        }elseif($lang == 1){
            $weekArr=array('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
        }
        //获取数字对应的星期
        return $weekArr[$number_wk];
    }
    /**
     * 使用雪花算法生成订单ID
     * @return string
     * @throws \Exception
     */
    public static function getNewOrderId ($newId, string $prefix = 'meta') {
        $snowflake = new \Godruoyi\Snowflake\Snowflake($newId);
        //32位
        if (PHP_INT_SIZE == 4) {
            $id = abs($snowflake->id());
        } else {
            $id = $snowflake->setStartTimeStamp(strtotime('2022-08-04') * 1000)->id();
        }
        return $prefix . $id;
    }
    /**
     * 0.12300000  可以給你返回 0.123
     * @param $num
     * @return array|string|string[]|null
     */
    public static function myFloat ($num) {
        $num = (string)$num;
        if (strpos($num, '.') === false) {
            return $num;
        }
        return preg_replace('/[.]$/', '', preg_replace('/0+$/', '', $num));
    }

    /**
     * 科学计数法转字符串
     * @param $num
     * @return string
     */
    public static function sctonum ($num) {
        if (false !== stripos($num, "e+")) {
            $a = explode("e+", strtolower($num));
            return bcmul($a[0], bcpow(10, $a[1]));
        }
        if (false !== stripos($num, "e-")) {
            $a = explode("e-", strtolower($num));
            return bcdiv($a[0], bcpow(10, $a[1]), self::getFloatLength($a[0]) + $a[1]);
        }
        return $num;
    }

    /**
     * @param $data 需要处理的数据
     * @param int $precision 保留几位小数
     * @return array|string
     */
    public static function fix_number_precision ($data, $precision = 2) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::fix_number_precision($value, $precision);
            }
            return $data;
        }

        if (is_numeric($data)) {
            $precision = is_float($data) ? $precision : 0;
            return number_format($data, $precision, '.', '');
        }

        return $data;
    }


    public static function is_address ($address, $type = 'BSC') {
        /* PHP 代码段*/
        // ETH地址合法校验
        if ($type == 'ETH' or $type == 'BSC') {
            if (!(preg_match('/^(0x)?[0-9a-fA-F]{40}$/', $address))) {
                return false; //满足if代表地址不合法
            }
        }
        if ($type == 'BTC') {
            // BTC地址合法校验
            if (!(preg_match('/^(1|3)[a-zA-Z\d]{24,33}$/', $address) && preg_match('/^[^0OlI]{25,34}$/', $address))) {
                return false; //满足if代表地址不合法
            }
        }
        if ($type == 'TRC') {
            // TRC地址合法校验
            if (!preg_match('/^T[a-zA-Z\d]{30,60}$/', $address)) {
                return false; //满足if代表地址不合法
            }
        }
        return true;
    }

    //todo 抽奖函数
    public static function get_rand ($proArr) {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);

        return $result;
    }

    public function base64 ($image = '') {
        if (empty($image)) {
            return ['code' => 1, 'msg' => '请传入base64'];
        }
        //接收base64数据
        //$image= $_POST['image_base64'];
        //设置图片名称
        $imageName = "25220_" . date("His", time()) . "_" . rand(1111, 9999) . '.png';
        //判断是否有逗号 如果有就截取后半部分
        if (strstr($image, ",")) {
            $image = explode(',', $image);
            $image = $image[1];
        }
        //设置图片保存路径
        $path = APP_DIR . '/upload/';
        //判断目录是否存在 不存在就创建
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        //图片路径
        $imageSrc = $path . $imageName;

        //生成文件夹和图片
        $r = file_put_contents($imageSrc, base64_decode($image));
        if (!$r) {
            return ['code' => 1, 'msg' => '图片生成失败'];
        } else {
            return ['code' => 0, 'msg' => ['path' => '/upload/' . $imageName,
                'url' => self::nowUrl() . '/upload/' . $imageName]];
        }
    }

    /**
     * 解码邀请码获取用户ID
     * @param $code
     * @return float|int
     */
    public static function decode($code) {
        static $source_string = 'E5FCDG3HQA4B1NOPIJ2RSTUV67MWX89KLYZ';
        if (strrpos($code, '0') !== false){
            $code = substr($code, strrpos($code, '0')+1);
        }
        $len = strlen($code);
        $code = strrev($code);
        $num = 0;
        for ($i=0; $i < $len; $i++) {
            $num += strpos($source_string, $code[$i]) * pow(35, $i);
        }
        return $num;

    }
    /**
     * 通过ID生成唯一邀请码
     * @param $user_id
     * @return string
     */
   public static function createCode($user_id) {
        static $source_string = 'E5FCDG3HQA4B1NOPIJ2RSTUV67MWX89KLYZ';
        $num = $user_id;
        $code = '';
        while ( $num > 0) {
            $mod = $num % 35;
            $num = ($num - $mod) / 35;
            $code = $source_string[$mod].$code;
        }
        if(empty($code[3]))
            $code = str_pad($code,4,'0',STR_PAD_LEFT);
        return $code;

    }

    /**
     * 根据起点坐标和终点坐标测距离
     * @ApiParams(name="$from", type="array", nullable=false, description=" 起点坐标,经纬度",sample="[118.012951,36.810024]")
     * @ApiParams(name="$to ", type="array", nullable=false, description=" 终点坐标,经纬度",sample="[118.012951,36.810024]")
     * @ApiParams(name="$km", type="bool", nullable=true, description=" 是否以公里为单位 ",sample="false:米 默认true:千米")
     * @ApiParams(name="$decimal", type="int", nullable=true, description=" 精度 保留小数位数 ",sample="默认2")
     */
    public static function getDistance ($from, $to, $km = true, $decimal = 2) {
        sort($from);
        sort($to);
        $EARTH_RADIUS = 6370.996; // 地球半径系数

        $distance = $EARTH_RADIUS * 2 * asin(
                sqrt(
                    pow(
                        sin(($from[0] * pi() / 180 - $to[0] * pi() / 180) / 2),
                        2
                    ) + cos($from[0] * pi() / 180) * cos($to[0] * pi() / 180)
                    * pow(
                        sin(($from[1] * pi() / 180 - $to[1] * pi() / 180) / 2),
                        2
                    )
                )
            ) * 1000;

        if ($km) {
            $distance = $distance / 1000;
        }

        return round($distance, $decimal);
    }

    function secToTime ($time) {
        if ($time > 3600) {
            $hours = intval($time / 3600);
            $minutes = $time % 3600;
            $times = $hours . ":" . gmstrftime('%M:%S', $minutes);
        } else {
            $times = gmstrftime('%H:%M:%S', $time);
        }
        return $times;
    }

    /**
     * 对象转数组
     * @param $object
     * @return array|mixed
     */
    public static function object2array (&$object) {
        return json_decode(json_encode($object), true);
    }

    /**
     * 秒转换为天，小时，分钟
     *
     * @param int $second 时间戳
     *
     * @return string
     */
    function secondChanage ($second = 0) {
        $newtime = '';
        $d = floor($second / (3600 * 24));
        $h = floor(($second % (3600 * 24)) / 3600);
        $m = floor((($second % (3600 * 24)) % 3600) / 60);
        $s = $second - ($d * 24 * 3600) - ($h * 3600) - ($m * 60);

        empty($d) ?
            $newtime = (
            empty($h) ? (
            empty($m) ? $s . '秒' : (
            empty($s) ? $m . '分' : $m . '分' . $s . '秒'
            )
            ) : (
            empty($m) && empty($s) ? $h . '时' : (
            empty($m) ? $h . '时' . $s . '秒' : (
            empty($s) ? $h . '时' . $m . '分' : $h . '时' . $m . '分' . $s . '秒'
            )
            )
            )
            ) : $newtime = (
        empty($h) && empty($m) && empty($s) ? $d . '天' : (
        empty($h) && empty($m) ? $d . '天' . $s . '秒' : (
        empty($h) && empty($s) ? $d . '天' . $m . '分' : (
        empty($m) && empty($s) ? $d . '天' . $h . '时' : (
        empty($h) ? $d . '天' . $m . '分' . $s . '秒' : (
        empty($m) ? $d . '天' . $h . '时' . $s . '秒' : (
        empty($s) ? $d . '天' . $h . '时' . $m . '分' : $d . '天' . $h . '时' . $m . '分' . $s . '秒'
        )
        )
        )
        )
        )
        )
        );

        return $newtime;

    }

    public static function is_ajax () {
        // php 判断是否为 ajax 请求  http://www.cnblogs.com/sosoft/
        if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
            // ajax 请求的处理方式
            return true;
        } else {
            // 正常请求的处理方式
            return false;
        }
    }

    /**
     * 拼接本地图片域名
     * @param $list
     * @param $filed
     */
    public static function replace_img_host ($list, $filed) {
        if (empty($list)) {
            return $list;
        }
        if (isset($GLOBALS['oss']) and $GLOBALS['oss']['state'] == 1) {
            $url = $GLOBALS['oss']['ossUrl'];
        } else {
            $url = self::nowUrl();
        }
        $url = $url . '/';
        if (count($list) == count($list, 1)) {
            if (is_array($filed)) {
                foreach ($filed as $value) {
                    if (isset($list[$value]) && $list[$value] && strpos($list[$value], 'http') === false) {
                        $list[$value] = $url . $list[$value];
                    }
                }
            } else {
                if (isset($list[$filed]) && $list[$filed] && strpos($list[$filed], 'http') === false) {
                    $list[$filed] = $url . $list[$filed];
                }
            }
        } else {
            foreach ($list as $k => $item) {
                if (is_array($filed)) {
                    foreach ($filed as $value) {
                        if (isset($item[$value]) && $item[$value] && strpos($item[$value], 'http') === false) {
                            $list[$k][$value] = $url . $item[$value];
                        }
                    }
                } else {
                    if (isset($item[$filed]) && $item[$filed] && strpos($item[$filed], 'http') === false) {
                        $list[$k][$filed] = $url . $item[$filed];
                    }
                }

            }
        }
        return $list;
    }

    /**
     *设置登录token  唯一性
     * @param $phone 用户手机号
     * @retrun  String
     */
    public static function setToken ($phone) {
        $str = md5(uniqid(md5(microtime(true)), true));
        $token = sha1($str . $phone);
        return $token;
    }

    /**
     * 获取压缩图片名
     * @param $file
     */

    public static function abbrFilename ($file) {
        if (isset($GLOBALS['oss'])) {
            return 'oss拼接';
        }
        //判断该图片是否存在
        if (!file_exists(APP_DIR . $file)) return $file;
        //判断图片格式
        $attach_fileext = (new self())->get_filetype($file);
        if (!in_array($attach_fileext, array('jpg', 'png', 'jpeg'))) {
            return $file;
        }
        //压缩图片
        $sFileNameS = str_replace("." . $attach_fileext, '_abbr.' . $attach_fileext, $file);
        //判断是否已压缩图片，若是则返回压缩图片路径
        if (file_exists(APP_DIR . $sFileNameS)) {
            return $sFileNameS;
        }
    }

    /**
     * 图片压缩处理
     * @param string $sFile 图片路径
     * @param int $iWidth 自定义图片宽度
     * @param int $iHeight 自定义图片高度
     */
    function getThumb ($sFile, $iWidth, $iHeight) {
        //判断该图片是否存在
        if (!file_exists(APP_DIR . $sFile)) return $sFile;
        //判断图片格式
        $attach_fileext = $this->get_filetype($sFile);
        if (!in_array($attach_fileext, array('jpg', 'png', 'jpeg'))) {
            return $sFile;
        }
        //压缩图片
        $sFileNameS = str_replace("." . $attach_fileext, '_abbr.' . $attach_fileext, $sFile);
        //判断是否已压缩图片，若是则返回压缩图片路径
        if (file_exists(APP_DIR . $sFileNameS)) {
            return $sFileNameS;
        }
        //解决手机端上传图片被旋转问题
        if (in_array($attach_fileext, array('jpeg'))) {
            $this->adjustPicOrientation(APP_DIR . $sFile);
        }
        //生成压缩图片，并存储到原图同路径下
        $this->resizeImage(APP_DIR . $sFile, APP_DIR . $sFileNameS, $iWidth, $iHeight);
        if (!file_exists(APP_DIR . $sFileNameS)) {
            return $sFile;
        }
        return $sFileNameS;
    }

    /**
     *获取文件后缀名
     */
    function get_filetype ($filename) {
        $extend = explode(".", $filename);
        return strtolower($extend[count($extend) - 1]);
    }

    /**
     * 解决手机上传图片被旋转问题
     * @param string $full_filename 文件路径
     */
    function adjustPicOrientation ($full_filename) {
        $exif = exif_read_data($full_filename);
        if ($exif && isset($exif['Orientation'])) {
            $orientation = $exif['Orientation'];
            if ($orientation != 1) {
                $img = imagecreatefromjpeg($full_filename);

                $mirror = false;
                $deg = 0;

                switch ($orientation) {
                    case 2:
                        $mirror = true;
                        break;
                    case 3:
                        $deg = 180;
                        break;
                    case 4:
                        $deg = 180;
                        $mirror = true;
                        break;
                    case 5:
                        $deg = 270;
                        $mirror = true;
                        break;
                    case 6:
                        $deg = 270;
                        break;
                    case 7:
                        $deg = 90;
                        $mirror = true;
                        break;
                    case 8:
                        $deg = 90;
                        break;
                }
                if ($deg) $img = imagerotate($img, $deg, 0);
                if ($mirror) $img = _mirrorImage($img);
                //$full_filename = str_replace('.jpg', "-O$orientation.jpg", $full_filename);新文件名
                imagejpeg($img, $full_filename, 95);
            }
        }
        return $full_filename;
    }


    /**
     * 生成图片
     * @param string $im 源图片路径
     * @param string $dest 目标图片路径
     * @param int $maxwidth 生成图片宽
     * @param int $maxheight 生成图片高
     */
    function resizeImage ($im, $dest, $maxwidth, $maxheight) {
        $img = getimagesize($im);
        switch ($img[2]) {
            case 1:
                $im = @imagecreatefromgif($im);
                break;
            case 2:
                $im = @imagecreatefromjpeg($im);
                break;
            case 3:
                $im = @imagecreatefrompng($im);
                break;
        }

        $pic_width = imagesx($im);
        $pic_height = imagesy($im);
        $resizewidth_tag = false;
        $resizeheight_tag = false;
        if (($maxwidth && $pic_width > $maxwidth) || ($maxheight && $pic_height > $maxheight)) {
            if ($maxwidth && $pic_width > $maxwidth) {
                $widthratio = $maxwidth / $pic_width;
                $resizewidth_tag = true;
            }

            if ($maxheight && $pic_height > $maxheight) {
                $heightratio = $maxheight / $pic_height;
                $resizeheight_tag = true;
            }

            if ($resizewidth_tag && $resizeheight_tag) {
                if ($widthratio < $heightratio)
                    $ratio = $widthratio;
                else
                    $ratio = $heightratio;
            }


            if ($resizewidth_tag && !$resizeheight_tag)
                $ratio = $widthratio;
            if ($resizeheight_tag && !$resizewidth_tag)
                $ratio = $heightratio;
            $newwidth = $pic_width * $ratio;
            $newheight = $pic_height * $ratio;

            if (function_exists("imagecopyresampled")) {
                $newim = imagecreatetruecolor($newwidth, $newheight);
                imagecopyresampled($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $pic_width, $pic_height);
            } else {
                $newim = imagecreate($newwidth, $newheight);
                imagecopyresized($newim, $im, 0, 0, 0, 0, $newwidth, $newheight, $pic_width, $pic_height);
            }

            imagejpeg($newim, $dest);
            imagedestroy($newim);
        } else {
            imagejpeg($im, $dest);
        }
    }

    /**
     *短信宝
     * */
    public static function sms_dxb ($phone, $code) {
        $statusStr = array(
            "0" => "短信发送成功",
            "-1" => "参数不全",
            "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
            "30" => "密码错误",
            "40" => "账号不存在",
            "41" => "余额不足",
            "42" => "帐户已过期",
            "43" => "IP地址限制",
            "50" => "内容含有敏感词"
        );
        $smsapi = "http://api.smsbao.com/";
        $user = "zd123456"; //短信平台帐号
        $pass = md5("qazwsx123"); //短信平台密码
        $content = "【宝沙绿博】您的验证码为{$code}，在5分钟内有效。";//要发送的短信内容
        //要发送短信的手机号码
        $sendurl = $smsapi . "sms?u=" . $user . "&p=" . $pass . "&m=" . $phone . "&c=" . urlencode($content);
        $result = file_get_contents($sendurl);
        return ['code' => $result, 'msg' => $statusStr[$result]];
    }

    /**
     * 字节换算成kb Mb
     *
     * @param $byte
     * @return string
     */
    function trans_byte ($byte) {

        $KB = 1024;

        $MB = 1024 * $KB;

        $GB = 1024 * $MB;

        $TB = 1024 * $GB;

        if ($byte < $KB) {

            return $byte . "B";

        } elseif ($byte < $MB) {

            return round($byte / $KB, 2) . "KB";

        } elseif ($byte < $GB) {

            return round($byte / $MB, 2) . "MB";

        } elseif ($byte < $TB) {

            return round($byte / $GB, 2) . "GB";

        } else {

            return round($byte / $TB, 2) . "TB";

        }

    }

    /**
     * 生成宣传海报
     * @param array  参数,包括图片和文字
     * @param string $filename 生成海报文件名,不传此参数则不生成文件,直接输出图片
     * @return [type] [description]
     */
    public static function createPoster ($config = array(), $filename = "") {
        //如果要看报什么错，可以先注释调这个header
        if (empty($filename)) header("content-type: image/png");
        $imageDefault = array(
            'left' => 0,
            'top' => 0,
            'right' => 0,
            'bottom' => 0,
            'width' => 100,
            'height' => 100,
            'opacity' => 100
        );
        $textDefault = array(
            'text' => '',
            'left' => 0,
            'top' => 0,
            'fontSize' => 32,       //字号
            'fontColor' => '255,255,255', //字体颜色
            'angle' => 0,
        );
        $background = $config['background'];//海报最底层得背景
        //背景方法
        $backgroundInfo = getimagesize($background);
        $backgroundFun = 'imagecreatefrom' . image_type_to_extension($backgroundInfo[2], false);
        $background = $backgroundFun($background);
        $backgroundWidth = imagesx($background);  //背景宽度
        $backgroundHeight = imagesy($background);  //背景高度
        $imageRes = imageCreatetruecolor($backgroundWidth, $backgroundHeight);
        $color = imagecolorallocate($imageRes, 0, 0, 0);
        imagefill($imageRes, 0, 0, $color);
        // imageColorTransparent($imageRes, $color);  //颜色透明
        imagecopyresampled($imageRes, $background, 0, 0, 0, 0, imagesx($background), imagesy($background), imagesx($background), imagesy($background));
        //处理了图片
        if (!empty($config['image'])) {
            foreach ($config['image'] as $key => $val) {
                $val = array_merge($imageDefault, $val);
                $info = getimagesize($val['url']);

                $function = 'imagecreatefrom' . image_type_to_extension($info[2], false);

                if ($val['stream']) {   //如果传的是字符串图像流
                    $info = getimagesizefromstring($val['url']);
                    $function = 'imagecreatefromstring';
                }
                $res = $function($val['url']);
                $resWidth = $info[0];
                $resHeight = $info[1];
                //建立画板 ，缩放图片至指定尺寸
                $canvas = imagecreatetruecolor($val['width'], $val['height']);
                imagefill($canvas, 0, 0, $color);
                //关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
                imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'], $resWidth, $resHeight);
                $val['left'] = $val['left'] < 0 ? $backgroundWidth - abs($val['left']) - $val['width'] : $val['left'];
                $val['top'] = $val['top'] < 0 ? $backgroundHeight - abs($val['top']) - $val['height'] : $val['top'];
                //放置图像
                imagecopymerge($imageRes, $canvas, $val['left'], $val['top'], $val['right'], $val['bottom'], $val['width'], $val['height'], $val['opacity']);//左，上，右，下，宽度，高度，透明度
            }
        }
        //处理文字
        if (!empty($config['text'])) {
            foreach ($config['text'] as $key => $val) {
                $val = array_merge($textDefault, $val);
                list($R, $G, $B) = explode(',', $val['fontColor']);
                $fontColor = imagecolorallocate($imageRes, $R, $G, $B);
                $val['left'] = $val['left'] < 0 ? $backgroundWidth - abs($val['left']) : $val['left'];
                $val['top'] = $val['top'] < 0 ? $backgroundHeight - abs($val['top']) : $val['top'];
                imagettftext($imageRes, $val['fontSize'], $val['angle'], $val['left'], $val['top'], $fontColor, $val['fontPath'], $val['text']);
            }
        }
        //生成图片
        if (!empty($filename)) {
            $res = imagejpeg($imageRes, $filename, 90); //保存到本地
            imagedestroy($imageRes);
            if (!$res) return false;
            return $filename;
        } else {
            imagejpeg($imageRes);     //在浏览器上显示
            imagedestroy($imageRes);
        }
    }


    /**
     * 本地文件上传
     * */
    public static function upload ($limit = 2, $ratio = 0.5) {
        if (isset($GLOBALS['oss']) && $GLOBALS['oss']['state'] == 1) {
            return self::upload_oss($limit);
        }
        $files = $_FILES;
        $paths = [];
        $names = [];
        $arr = [];
        $uris = [];
        $exts = [];
        $num = count($files);
        if (empty($files)) {
            return ['msg' => '请选择文件', 'paths' => [], 'names' => [],
                'size' => [], 'code' => 1];
        }
        //echo '<pre>';
        //print_r($files);
        foreach ($files as $v) {
            if ($v['error'] > 0) {
                array_push($paths, $v['error']);
                if ($num <= 1) {
                    return ['msg' => $v['error'], 'paths' => [], 'names' => [],
                        'size' => [], 'code' => 1];
                }
                continue;
            }
            $arr[] = $v['size'];
            $name_arr = explode('.', $v['name']);
            $ext = array_pop($name_arr);
            $array = array('jpg', 'gif', 'png', 'jpeg');
            $is_image = in_array($ext, $array);
            if ($is_image) {
                if ($v['size'] > $limit * 1024 * 1024) {
                    array_push($paths, '文件大小超过限制' . $v['size'] . '>' . $limit * 1024 * 1024);
                    if ($num <= 1) {
                        return ['msg' => '文件大小超过限制' . $v['size'] . '>' . $limit * 1024 * 1024, 'paths' => [], 'names' => [],
                            'size' => [], 'code' => 1];
                    }
                    continue;
                }
            }

            $file_road = '/upload/' . date('Y_m_d_') . uniqid() . '.' . $ext;
            $path = APP_DIR . $file_road;
            $bool = move_uploaded_file($v['tmp_name'], $path);
            if (!$bool) {
                array_push($paths, '上传失败');
                if ($num <= 1) {
                    return ['msg' => '上传失败', 'paths' => [], 'names' => [],
                        'size' => [], 'code' => 1];
                }
                continue;
            }
            // if ($is_image) {
            //     $size = round(filesize($path) / 1024, 2);
            //     if ($size > 40 and $ratio < 1) {
            //         $img_info = getimagesize($path);
            //         $file_road = (new self())->getThumb($file_road, $img_info[0] * $ratio, $img_info[1] * $ratio);
            //     }
            // }
            array_push($exts, $ext);
            array_push($paths, $file_road);
            array_push($uris, self::nowUrl() . $file_road);
            array_push($names, $v['name']);
        }

        return [
            'msg' => '上传成功', 'paths' => $paths, 'names' => $names, 'uris' => $uris,
            'size' => $arr,'exts'=>$exts, 'code' => 0
        ];


    }

    public static function img_abbr ($img) {
        return preg_replace('/_abbr(?!.*_abbr)/', '', $img);
    }

    /**
     * 文件删除
     * */
    public static function file_delete ($file) {
        if (isset($GLOBALS['oss'])) {
            return self::delete_oss($file);
        }
        $file = APP_DIR . $file;
        $url = iconv('utf-8', 'gbk', $file);
        if (PATH_SEPARATOR == ':') { //linux
            unlink($file);
        } else { //Windows
            unlink($url);
        }
        return true;
    }

    /**
     * OSS文件上传
     * */
    public static function upload_oss ($limit = 2, $type = 0) {
        $files = $_FILES;
        $paths = [];
        $names = [];
        $arr = [];
        $uris = [];
        $exts = [];
        foreach ($files as $v) {
            $arr[] = $v['size'];
            $name_arr = explode('.', $v['name']);
            $ext = array_pop($name_arr);
            $path = 'images/' . date('YmdHis') . uniqid() . '.' . $ext;
           // $path = date('Y/m/d/') . uniqid() . ".jpg";
            App::oss()->uploadFile(
                $GLOBALS['oss']['Bucket'], $path, $v['tmp_name']
            );
            if ($v['size'] > $limit * 1024 * 1024) {
                return ['msg' => '文件大小超过限制' . $v['size'] . '>' . $limit * 1024 * 1024, 'paths' => [], 'names' => [],
                    'size' => [], 'code' => 1];
            }
            array_push($exts, $ext);
            array_push($paths, $path);
            array_push($uris, rtrim($GLOBALS['oss']['ossUrl'] ,'/').'/'. $path);
            array_push($names, $v['name']);
        }
        return [
            'msg' => '上传成功', 'paths' => $paths, 'names' => $names, 'uris' => $uris,
            'size' => $arr,'exts'=>$exts, 'code' => 0
        ];
        return [
            'msg' => '上传成功', 'paths' => $paths, 'names' => $names, 'uris' => $uris,
            'size' => $arr
        ];

    }

    public static function gmt_iso8601 ($time) {
        return str_replace('+00:00', '.000Z', gmdate('c', $time));
    }

    public static function formatBytes ($size) {
        $units = array(' B', ' KB', ' MB', ' GB', ' TB');
        for ($i = 0; $size >= 1024 && $i < 4; $i++) {
            $size /= 1024;

        }
        return round($size, 2) . $units[$i];
    }

    public static function oss_url_download ($object, $timeout = 3600) {

        // 使用try catch捕获异常，如果捕获到异常，则说明下载失败；如果没有捕获到异常，则说明下载成功。
        $bucket = $GLOBALS['oss']['Bucket'];
        $ossClient = App::oss();
        try {
            $ossClient->getObject($bucket, $object);
        } catch (OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
    }

    public static function oss_url_sign ($object, $timeout = 3600, $options = null) {
        // 填写Bucket名称。
        $bucket = $GLOBALS['oss']['Bucket'];
// 填写不包含Bucket名称在内的Object完整路径。
// 设置签名URL的有效时长为3600秒。
        $ossClient = App::oss();
        return $ossClient->signUrl($bucket, $object, $timeout, 'GET', $options);
    }

    /**
     * oss 上传签名
     */
    public static function oss_sign () {
        $id = $GLOBALS['oss']['accessKeyId'];          // 请填写您的AccessKeyId。
        $key = $GLOBALS['oss']['accessKeySecret'];     // 请填写您的AccessKeySecret。
// $host的格式为 bucketname.endpoint，请替换为您的真实信息。
        $host = $GLOBALS['oss']['ossUrl'];
// $callbackUrl为上传回调服务器的URL，请将下面的IP和Port配置为您自己的真实URL信息。
        $callbackUrl = 'http://88.88.88.88:8888/main/osscallback';
        $dir = 'upload/' . date('Y/m/d/');         // 用户上传文件时指定的前缀。

        $callback_param = array(
            'callbackUrl' => $callbackUrl,
            'callbackBody' => 'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}',
            'callbackBodyType' => "application/x-www-form-urlencoded"
        );
        $callback_string = json_encode($callback_param);

        $base64_callback_body = base64_encode($callback_string);
        $now = time();
        $expire = 30;  //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问。
        $end = $now + $expire;
        $expiration = self::gmt_iso8601($end);

//最大文件大小.用户可以自己设置
        $condition = array(0 => 'content-length-range', 1 => 0, 2 => 2147483648);
        $conditions[] = $condition;
        // $conditions[] = array(0 => 'Content-Disposition', 1 => '$key', 2 => 'attachment');
// 表示用户上传的数据，必须是以$dir开始，不然上传会失败，这一步不是必须项，只是为了安全起见，防止用户通过policy上传到别人的目录。
        $start = array(0 => 'starts-with', 1 => '$key', 2 => $dir);
        $conditions[] = $start;


        $arr = array('expiration' => $expiration, 'conditions' => $conditions);
        $policy = json_encode($arr);
        $base64_policy = base64_encode($policy);
        $string_to_sign = $base64_policy;
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));

        $response = array();
        $response['accessid'] = $id;
        $response['host'] = $host;
        $response['policy'] = $base64_policy;
        $response['signature'] = $signature;
        $response['expire'] = $end;
        $response['callback'] = $base64_callback_body;
        $response['dir'] = $dir;  // 这个参数是设置用户上传文件时指定的前缀。
//        $response = [
//            'code'=>0,
//            'success'=>true,
//            'msg'=>'签名成功',
//            'data'=>[
//                'accessid'=>$id,
//                'host'=>$host,
//                'policy'=>$base64_policy,
//                'signature'=>$signature,
//                'expire'=>$end,
//            ]
//        ];
        return $response;
    }

    /**
     * OSS文件大文件分片上传 步骤4：查询。
     * */
    public static function upload_oss_part_post_query ($file) {
        $bucket = $GLOBALS['oss']['Bucket'];
        $object = $file['oss_object'];
        $uploadId = $file['oss_id'];
        try {
            $ossClient = App::oss();
            $listPartsInfo = $ossClient->listParts($bucket, $object, $uploadId);
            return ['code' => 0, 'msg' => count($listPartsInfo->getListPart())];
//            foreach ($listPartsInfo->getListPart() as $partInfo) {
////                return ['code'=>0,'msg'=>$partInfo->getPartNumber() . "\t" . $partInfo->getSize() . "\t" .
////                    $partInfo->getETag() . "\t" . $partInfo->getLastModified() . "\n"];
//               //  print($partInfo->getPartNumber() . "\t" . $partInfo->getSize() . "\t" . $partInfo->getETag() . "\t" . $partInfo->getLastModified() . "\n");
//            }
        } catch (OssException $e) {
            return ['code' => 1, 'msg' => $e->getMessage()];
        }
    }

    /**
     * OSS文件大文件分片上传 步骤3：完成上传。
     * */
    public static function upload_oss_part_post_upload ($file) {
        $ossClient = App::oss();
        $bucket = $GLOBALS['oss']['Bucket'];
        $object = $file['oss_object'];
        $uploadId = $file['oss_id'];
        $uploadParts = $file['oss_uploadParts'];
        try {
            // 执行completeMultipartUpload操作时，需要提供所有有效的$uploadParts。OSS收到提交的$uploadParts后，会逐一验证每个分片的有效性。当所有的数据分片验证通过后，OSS将把这些分片组合成一个完整的文件。
            $ossClient->completeMultipartUpload($bucket, $object, $uploadId, $uploadParts);
            return ['code' => 0, 'msg' => $file];
        } catch (OssException $e) {
            return ['code' => 1, 'msg' => $e->getMessage()];
        }
    }

    /**
     * OSS文件大文件分片上传 步骤2：上传分片。
     * */
    public static function upload_oss_part_post_part ($file) {
        $ossClient = App::oss();
        $partSize = 10 * 1024 * 1024;
        $uploadFileSize = $file['size'];
        $pieces = $ossClient->generateMultiuploadParts($uploadFileSize, $partSize);
        $responseUploadPart = array();
        $uploadPosition = 0;
        $isCheckMd5 = true;
        $uploadFile = $file['tmp_name'];
        $bucket = $GLOBALS['oss']['Bucket'];
        $object = $file['oss_object'];
        $uploadId = $file['oss_id'];

        foreach ($pieces as $i => $piece) {
            $fromPos = $uploadPosition + (integer)$piece[$ossClient::OSS_SEEK_TO];
            $toPos = (integer)$piece[$ossClient::OSS_LENGTH] + $fromPos - 1;
            $upOptions = array(
                // 上传文件。
                $ossClient::OSS_FILE_UPLOAD => $uploadFile,
                // 设置分片号。
                $ossClient::OSS_PART_NUM => ($i + 1),
                // 指定分片上传起始位置。
                $ossClient::OSS_SEEK_TO => $fromPos,
                // 指定文件长度。
                $ossClient::OSS_LENGTH => $toPos - $fromPos + 1,
                // 是否开启MD5校验，true为开启。
                $ossClient::OSS_CHECK_MD5 => $isCheckMd5,
            );
            // 开启MD5校验。
            if ($isCheckMd5) {
                $contentMd5 = OssUtil::getMd5SumForFile($uploadFile, $fromPos, $toPos);
                $upOptions[$ossClient::OSS_CONTENT_MD5] = $contentMd5;
            }
            try {
                // 上传分片。
                $responseUploadPart[] = $ossClient->uploadPart($bucket, $object, $uploadId, $upOptions);
            } catch (OssException $e) {
                return ['code' => 1, 'msg' => $e->getMessage()];
            }
        }
// $uploadParts是由每个分片的ETag和分片号（PartNumber）组成的数组。
        $uploadParts = array();
        foreach ($responseUploadPart as $i => $eTag) {
            $uploadParts[] = array(
                'PartNumber' => ($i + 1),
                'ETag' => $eTag,
            );
        }
        $file['oss_uploadParts'] = $uploadParts;
        try {
            // 执行completeMultipartUpload操作时，需要提供所有有效的$uploadParts。OSS收到提交的$uploadParts后，会逐一验证每个分片的有效性。当所有的数据分片验证通过后，OSS将把这些分片组合成一个完整的文件。
            $ossClient->completeMultipartUpload($bucket, $object, $uploadId, $uploadParts);
            return ['code' => 0, 'msg' => $file];
        } catch (OssException $e) {
            return ['code' => 1, 'msg' => $e->getMessage()];
        }
        // return ['code'=>0,'msg'=>$file];
    }

    /**
     *OSS文件大文件分片上传 步骤1：初始化一个分片上传事件，获取uploadId。
     * */
    public static function upload_oss_part_get_id ($file) {
        $bucket = $GLOBALS['oss']['Bucket'];
        $name_arr = explode('.', $file['name']);
        $ext = array_pop($name_arr);
        $file['oss_object'] = $object = 'upload/' . date('Y/m/d/') . uniqid() . '.' . $ext;
        /**
         *  步骤1：初始化一个分片上传事件，获取uploadId。
         */
        try {
            $ossClient = App::oss();
            //返回uploadId。uploadId是分片上传事件的唯一标识，您可以根据uploadId发起相关的操作，如取消分片上传、查询分片上传等。
            $file['oss_id'] = $ossClient->initiateMultipartUpload($bucket, $object);
            return ['code' => 0, 'msg' => $file];
        } catch (OssException $e) {
            return ['code' => 1, 'msg' => $e->getMessage()];
        }

    }

    /**
     * OSS文件上传 不控制大小 特殊图片上传
     * */
    public static function upload1 ($limit = 2, $height = null, $width = null) {
        $files = $_FILES;
        $paths = [];
        $names = [];
        $arr = [];
        foreach ($files as $v) {
            if ($v['size'] > $limit * 1024 * 1024) {
                return [
                    'msg' => '上传失败,不能大于' . $limit . 'M', 'paths' => $paths,
                    'names' => $names, 'size' => $arr
                ];
            }
            $arr[] = $v['size'];
            $hou = substr(strrchr($v['name'], '.'), 1);
            if ($hou == 'jpeg') {
                $hou = 'jpg';
            }
            $path = date('Y/m/d/') . uniqid() . "." . $hou;
            App::oss()->uploadFile(
                $GLOBALS['oss']['Bucket'], $path, $v['tmp_name']
            );
            array_push($paths, $GLOBALS['oss']['ossUrl'] . $path);
            array_push($names, $v['name']);
        }
        return [
            'msg' => '上传成功', 'paths' => $paths, 'names' => $names,
            'size' => $arr
        ];
    }

    //浏览器下载图片
    public function downImg ($url) {
        $mime = 'application/force-download';
        header('Pragma: public'); // required
        header('Expires: 0'); // no cache
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . basename($url) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Connection: close');
        readfile($url); // push it out
        exit;
    }

    /**
     * wx下载文件到本地
     * @param int $userid 站长UID
     * @param int $vipid 分站ID
     * @param bool $type 默认为false,修改和添加分站时. 删除分站时为true,还原各用户vip_id字段为总站
     * @return bool
     */
    public static function wxDownloadFile ($url) {
        //方法一：//推荐用该方法
        $header = array(
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:45.0) Gecko/20100101 Firefox/45.0',
            'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
            'Accept-Encoding: gzip, deflate',);
        // $url='http://mmbiz.qpic.cn/mmbiz_png/6zuH0H9ttkOuC1TR7x3sywEVBP9B7u5FcVktdNotiajVe2LVbq85ibEFl8NeIxLGPqcDo104lJqr07A1jlTvYZxw/0?wx_fmt=png';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        $data = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($code == 200) {//把URL格式的图片转成base64_encode格式的！
            $imgBase64Code = "data:image/jpg;base64," . base64_encode($data);
        }
        $img_content = $imgBase64Code;//图片内容
        //echo $img_content;exit;

        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $img_content, $result)) {
            $type = $result[2];//得到图片类型png?jpg?gif?
            $tmpFile = sys_get_temp_dir() . DS . uniqid() . '.' . $type;
            // $new_file = "./cs/cs.{$type}";

            if (file_put_contents($tmpFile, base64_decode(str_replace($result[1], '', $img_content)))) {
                $path = date('Y/m/d/') . uniqid() . "." . $type;
                App::oss()->uploadFile(
                    $GLOBALS['oss']['Bucket'], $path, $tmpFile
                );
                unlink($tmpFile);
                return $GLOBALS['oss']['ossUrl'] . $path;
            }
        }
        return false;

    }

    /**
     * oss下载文件到本地
     * @param int $userid 站长UID
     * @param int $vipid 分站ID
     * @param bool $type 默认为false,修改和添加分站时. 删除分站时为true,还原各用户vip_id字段为总站
     * @return bool
     */
    public static function downloadFile ($bigImage) {
        preg_match_all("/(.*?(\.jpg|\.png))/", $bigImage, $arr);
        $filePath = substr($arr[0][0], -28);
        $tmpFile = sys_get_temp_dir() . DS . uniqid() . '.jpg';
        $options = array(
            (App::oss())::OSS_FILE_DOWNLOAD => $tmpFile
        );
        (App::oss())->getObject(
            $GLOBALS['oss']['Bucket'], $filePath, $options
        );
        return $tmpFile;
    }

    /**
     * OSS文件删除
     * */
    public static function delete_oss ($file) {
        $path = str_replace($GLOBALS['oss']['ossUrl'], '', $file);
        try {
            App::oss()->deleteObject($GLOBALS['oss']['Bucket'], $path);
            return true;
        } catch (OssException $e) {
            return false;
        }
    }

    /**
     * 字符串截取首尾长度 如  （123456，1）=》 1****6
     * */
    function strDeal ($str, $len) {
        if (mb_strlen($str) >= $len) {
            $chars = '';
            if (mb_strlen($str) > 2 * $len + 1) {
                for ($i = 0; $i < mb_strlen($str) - 2 * $len; $i++) {
                    $chars .= '*';
                }
            } else {
                $chars .= '***';
            }
            return mb_substr($str, 0, $len, 'UTF-8') . $chars . mb_substr(
                    $str, mb_strlen($str) - $len, $len, 'UTF-8'
                );
        } else {
            return $str . '***' . $str;
        }
    }

    const KEY = "fb57583391ee92b2b93b9be67bbf5386";

    public static function gaodeLocation ($address, $city = "全国") {

        $client = new Client(
            [
                'base_uri' => 'http://restapi.amap.com/v3/geocode/geo',
                'timeout' => 2.0,
            ]
        );
        $response = $client->request(
            'GET', '', [
                     'query' => [
                         'key' => self::KEY, 'address' => $address, 'city' => $city
                     ]
                 ]
        );
        return $response->getBody()->getContents();
    }

    /**
     * 根据经纬度获取城市
     *
     * @param $location
     */
    public static function gaodeAddress ($location) {
        $client = new Client(
            [
                'base_uri' => 'http://restapi.amap.com/v3/geocode/regeo',
                'timeout' => 2.0,
            ]
        );
        $response = $client->request(
            'GET', '', [
                     'query' => ['key' => self::KEY, 'location' => $location]
                 ]
        );
        return json_decode(
            $response->getBody()->getContents(), true
        )['regeocode'];
    }

    /**
     *  获取头部信息
     *
     * @return mixed
     *
     */
    public static function getallheaders () {
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(
                    ' ', '-',
                    ucwords(strtolower(str_replace('_', ' ', substr($name, 5))))
                )]
                    = $value;
            }
        }
        return $headers;
    }

    /**
     * 对银行卡号进行掩码处理
     * @param string $bankCardNo 银行卡号
     * @return string             掩码后的银行卡号
     */
    function formatBankCardNo ($bankCardNo) {
        //截取银行卡号前6位
        $prefix = substr($bankCardNo, 0, 6);
        //截取银行卡号后4位
        $suffix = substr($bankCardNo, -4, 4);

        $maskBankCardNo = $prefix . "***" . $suffix;

        return $maskBankCardNo;
    }

    /**
     *是否手机端浏览器
     */
    public static function isMobileBrowser () {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) {
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高。其中'MicroMessenger'是电脑微信
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile', 'MicroMessenger');
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }

    //判断微信浏览器
    function is_weixin () {
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            return true;
        }
        return false;
    }

    /**
     * @param        $msg
     * @param string $url
     * @param int $code 非0 错误提示
     */
    public static function redirectTop ($msg, $url = '', $code = 0) {
        if (isset($_SERVER["HTTP_X_REQUESTED_WITH"])
            && strtolower(
                $_SERVER["HTTP_X_REQUESTED_WITH"]
            ) == "xmlhttprequest"
        ) {
            if (is_array($msg)) {
                exit(json_encode($msg));
            } else {
                Helper::responseJson(
                    ['alertStr' => $msg, 'redirect' => $url], $code
                );
            }
        } else {
            $strAlert = "";
            if (!empty($msg)) {
                $strAlert = "alert(\"{$msg}\");";
            }
            if ($url == "") {
                echo '<script src="' . APP_ROOT . $GLOBALS['static'] . '/forest/Public/js/jquery-1.11.3.min.js"></script>
<script type="text/javascript" src="' . APP_ROOT . $GLOBALS['static'] . '/public/layer-v3.5.1/layer.js"></script>';
                exit("
<script>layer.msg('$msg',function (){window.history.go(-1);},2000);</script>
");
            }

            exit("<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script>function sptips(){  {$strAlert} top.location.href=\"{$url}\";}</script></head><body onload=\"sptips()\"></body></html>");
        }
    }

    /**
     * 发送微信模板消息
     *
     * @param $msg
     */
    public static function sendTemplateMsg ($app, $msg) {
        Helper::log(json_encode($msg), 'wx_template_send');
        unset($msg['company_id']);
        unset($msg['type']);
        if ($msg['sign']) {
            Helper::log(json_encode($msg), 'sign');
            if (strpos($msg['url'], '?') !== false) {
                $msg['url'] .= '&sign=' . $msg['touser'];
            } else {
                $msg['url'] .= '?sign=' . $msg['touser'];
            }
        }
        unset($msg['sign']);
        $result = $app->template_message->send($msg);
        return $result;
    }

    /**
     * 发送post请求
     *
     * @param string $url
     * @param string $param
     *
     * @return bool|mixed
     */
    public static function request_post ($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch); //运行curl
        curl_close($ch);
        return $data;
    }


    /**
     *  生成CSV文件并返回OSS路径
     *
     * @param array $data 查询结果 二维数组
     * @param string $filename 文件名
     */
    public static function getPubOssCsv ($data, $filename = 'book.csv') {
        if (empty($data)) {
            return false;
        }
        ob_start();
        $tmpFile = sys_get_temp_dir() . DS . $filename;
        $file = fopen($tmpFile, 'w');
        fwrite($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($file);
        foreach ($data as $k => $v) {
            fputcsv($file, $v);
        }
        App::oss()->uploadFile(
            $GLOBALS['oss']['Bucket'], $filename, $tmpFile
        );
        fclose($file);
        unlink($tmpFile);
        return $GLOBALS['oss']['ossUrl'] . $filename;
    }

    /**
     * 导出新
     * @param $data
     * @param $fieldArr
     * @param string $file_name
     */
    public static function exportCsv ($data, $fieldArr, $file_name = '') {
        ini_set("max_execution_time", "3600");
        $csv_data = '';
        /** 标题 */
        $titleArr = [];
        foreach ($fieldArr as $k => $f) {
            array_push($titleArr, $f);
        }
        $csv_data .= join(',', $titleArr) . "\r\n";
        foreach ($data as $k => $row) {
            $csvArr = [];
            foreach ($fieldArr as $key => $v) {
                $row[$key] = str_replace("\"", "\"\"", $row[$key]);
                array_push($csvArr, $row[$key]);
            }
            $csv_data .= join(',', $csvArr) . "\r\n";
            unset($data[$k]);
        }

        $csv_data = mb_convert_encoding($csv_data, "cp936", "UTF-8");
        $file_name = empty($file_name) ? date('Y-m-d-H-i-s', time())
            : $file_name;
        // 解决IE浏览器输出中文名乱码的bug
        if (preg_match('/MSIE/i', $_SERVER['HTTP_USER_AGENT'])) {
            $file_name = urlencode($file_name);
            $file_name = iconv('UTF-8', 'GBK//IGNORE', $file_name);
        }
        $file_name = $file_name . '.csv';
        ob_end_clean();
        header('Content-Type: application/download');
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition:attachment;filename=" . $file_name);
        header('Cache-Control: max-age=0');
        header('Expires:0');
        header('Pragma:public');
        echo $csv_data;
        exit();
    }


    /**
     *导出CSV
     *
     * @param $data      array  要下载的数据
     * @param $title_arr array　表头
     * @param $filename  string　文件名称
     */
    public static function export_csv ($data, $title_arr, $file_name = '') {
        ini_set("max_execution_time", "3600");

        $csv_data = '';

        /** 标题 */
        $nums = count($title_arr);

        for ($i = 0; $i < $nums - 1; ++$i) {
            //$csv_data .= '"' . $title_arr[$i] . '",';
            $csv_data .= $title_arr[$i] . ',';
        }
        if ($nums > 0) {
            $csv_data .= $title_arr[$nums - 1] . "\r\n";
        }

        foreach ($data as $k => $row) {
            $_tmp_csv_data = '';
            foreach ($row as $key => $r) {
                $row[$key] = str_replace("\"", "\"\"", $r);

                if ($_tmp_csv_data == '') {
                    $_tmp_csv_data = $row[$key];
                } else {
                    $_tmp_csv_data .= ',' . $row[$key];
                }

            }

            $csv_data .= $_tmp_csv_data . $row[$nums - 1] . "\r\n";
            unset($data[$k]);
        }

        $csv_data = mb_convert_encoding($csv_data, "cp936", "UTF-8");
        $file_name = empty($file_name) ? date('Y-m-d-H-i-s', time())
            : $file_name;
        // 解决IE浏览器输出中文名乱码的bug
        if (preg_match('/MSIE/i', $_SERVER['HTTP_USER_AGENT'])) {
            $file_name = urlencode($file_name);
            $file_name = iconv('UTF-8', 'GBK//IGNORE', $file_name);
        }
        $file_name = $file_name . '.csv';
        ob_end_clean();
        header('Content-Type: application/download');
        //  header("Content-type:text/csv;");
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition:attachment;filename=" . $file_name);
        header('Cache-Control: max-age=0');
        // header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $csv_data;
        exit();
    }

    /**
     * 判断是否为合法的身份证号码
     *
     * @param $mobile
     *
     * @return int
     */
    public static function isCreditNo ($vStr) {
        $vCity = array(
            '11', '12', '13', '14', '15', '21', '22',
            '23', '31', '32', '33', '34', '35', '36',
            '37', '41', '42', '43', '44', '45', '46',
            '50', '51', '52', '53', '54', '61', '62',
            '63', '64', '65', '71', '81', '82', '91'
        );
        if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr)) {
            return false;
        }
        if (!in_array(substr($vStr, 0, 2), $vCity)) {
            return false;
        }
        $vStr = preg_replace('/[xX]$/i', 'a', $vStr);
        $vLength = strlen($vStr);
        if ($vLength == 18) {
            $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-'
                . substr($vStr, 12, 2);
        } else {
            $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2)
                . '-' . substr($vStr, 10, 2);
        }
        if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) {
            return false;
        }
        if ($vLength == 18) {
            $vSum = 0;
            for ($i = 17; $i >= 0; $i--) {
                $vSubStr = substr($vStr, 17 - $i, 1);
                $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a')
                        ? 10
                        : intval(
                            $vSubStr, 11
                        ));
            }
            if ($vSum % 11 != 1) {
                return false;
            }
        }
        return true;
    }


    /**
     * 二维数组排序
     * @param $arrays
     * @param $sort_key
     * @param int $sort_order
     * @param int $sort_type
     * @return array|false
     */
    public static function array_sort (
        $arrays, $sort_key, $sort_order = SORT_DESC, $sort_type = SORT_NUMERIC
    ) {
        if (is_array($arrays)) {
            foreach ($arrays as $array) {
                if (is_array($array)) {
                    $key_arrays[] = $array[$sort_key];
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
        array_multisort($key_arrays, $sort_order, $sort_type, $arrays);
        return $arrays;
    }

    //搜索标记
    public static function search_sign ($word, $str) {
        if (empty($word) or empty($str)) {
            return $str;
        }
        return preg_replace(
            "|($word)|Ui",
            "<span style=\"background:#FF913B;\"><b>$1</b></span>",
            $str
        );
    }

    //手机号码中间四位打*号
    public static function hidtel ($phone) {
        $IsWhat = preg_match(
            '/(0[0-9]{2,3}[\-]?[2-9][0-9]{6,7}[\-]?[0-9]?)/i', $phone
        ); //固定电话
        if ($IsWhat == 1) {
            return preg_replace(
                '/(\d{3,4}[\-]?[2-9])[0-9]{3,4}([0-9]{3}[\-]?[0-9]?)/i',
                '$1****$2', $phone
            );
        } else {
            if (strlen($phone) >= 11) {
                $replacement = '$1****$2';
            } else {
                $replacement = '$1***$2';
            }
            return preg_replace(
                '/(\d{2}[1-9][0-9]{2})[0-9]{3,4}([0-9]{3,4})/i', $replacement, $phone
            );
        }
    }

    /**
     * @Description: 生成唯一订单号
     * @param $begintime
     * @param string $endtime
     */
    public static function get_order_no () {

        $rand = strtoupper(substr(uniqid(sha1(time())), 0, 4));
        return $rand;
    }

    /**
     * @Description: 获取随机时间
     * @param string $begintime 开始时间
     * @param string $endtime 结束时间，为空时取当前时间
     * @return int
     */
    public static function getRandTime ($begintime, $endtime = '') {
        $begin = strtotime($begintime);
        $end = $endtime == '' ? mktime() : strtotime($endtime);
        $timestamp = rand($begin, $end);
        return $timestamp;
    }

    /**
     * @Description: 将时间转换为几秒前、几分钟前、几小时前、几天前
     * @Author: Yang
     * @param $the_time 需要转换的时间
     * @return string
     */
    public static function time_tran ($the_time) {
        $now_time = date("Y-m-d H:i:s", time());
        $now_time = strtotime($now_time);
        $show_time = strtotime($the_time);
        $dur = $now_time - $show_time;
        if ($dur <= 0) {
            return '刚刚';
        } else {
            if ($dur < 60) {
                return $dur . '秒前';
            } else {
                if ($dur < 3600) {
                    return floor($dur / 60) . '分钟前';
                } else {
                    if ($dur < 86400) {
                        return floor($dur / 3600) . '小时前';
                    } else {
                        if ($dur < 259200) { // 3天内
                            return floor($dur / 86400) . '天前';
                        } else {
                            return $the_time;
                        }
                    }
                }
            }
        }
    }

    /**
     * 判断手机号码
     * @param $text
     * @return bool
     */
    public static function isMobile ($text) {
        $phone = '/^0?1[3|4|5|6|7|8|9][0-9]\d{8}\w?$/';
        $zuoji = '/^\d{3,4}-\d{7,8}\w?$/';
        if (preg_match($phone, $text) or preg_match($zuoji, $text)) {
            return (true);
        } else {
            return (false);
        }
    }

    /**
     * 批量生成带*手机号
     * @return array
     */
    public static function generate_mobile ($num = 20) {
        $arr = array(
            130, 131, 132, 133, 134, 135, 136, 137, 138, 139,
            144, 147,
            150, 151, 152, 153, 155, 156, 157, 158, 159,
            176, 177, 178,
            180, 181, 182, 183, 184, 185, 186, 187, 188, 189,
            198, 199,
        );
        for ($i = 0; $i < $num; $i++) {
            $tmp[] = $arr[array_rand($arr)] . ' ' . mt_rand(1000, 9999) . '' . mt_rand(1000, 9999);
        }
        $phones = [];
        $my_phone_num = array_unique($tmp);
        foreach ($my_phone_num as $val) {
            $sjs = '****';
            $phones[] = self::hidtel(substr_replace($val, $sjs, 3, 5));
        }
        return $phones;
    }

    /**
     * 生成随机大写字母
     * @param $length
     * @return false|string
     */
    function createRandomStr ($length) {
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';//26个字符
        $strlen = 26;
        while ($length > $strlen) {
            $str .= $str;
            $strlen += 26;
        }
        $str = str_shuffle($str);
        return substr($str, 0, $length);
    }

    /**
     * 生成唯一邀请码
     * @return string
     */
    public static function create_invite_code ($len = 4) {
        $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $rand = $code[rand(0, 25)]
            . strtoupper(dechex(date('m')))
            . date('d')
            . substr(time(), -5)
            . substr(microtime(), 2, 5)
            . sprintf('%02d', rand(0, 99));
        for (
            $a = md5($rand, true),
            $s = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            $d = '',
            $f = 0;
            $f < $len;
            $g = ord($a[$f]),
            $d .= $s[($g ^ ord($a[$f + 8])) - $g & 0x1F],
            $f++
        ) ;
        return $d;
    }

    /**
     * 分享图缩略
     * @param $url
     * @return string
     */
    public static function processList ($url) {
        preg_match_all("/.*(\.jpg|\.png)/", $url, $arr);
        return $arr[0][0] . '?x-oss-process=list';
    }


    public static function processCove ($url) {
        preg_match_all("/.*(\.jpg|\.png)/", $url, $arr);
        return $arr[0][0] . '?x-oss-process=cover';
    }


    function base64url_encode ($data) {
        return rtrim(
            strtr(base64_encode($data), '+/', '-_'), '='
        );
    }

    function base64url_decode ($data) {
        return base64_decode(
            str_pad(
                strtr($data, '-_', '+/'), strlen($data) % 4, '=',
                STR_PAD_RIGHT
            )
        );
    }

    /**
     *URL生成二维码图片 返回OSS路径
     */
    public static function urlToImgUrl1 ($url, $filename = '') {
        $base64 = App::Qrcode($url);
        $base64 = substr($base64, strpos($base64, ',') + 1);
        $tmpFile = sys_get_temp_dir() . DS . uniqid() . '.png';
        $filename = $filename ?: date('Y/m/d/') . uniqid() . ".png";
        $file = fopen($tmpFile, "w");
        fwrite($file, base64_decode($base64));
        fclose($file);
        App::oss()->uploadFile(
            $GLOBALS['oss']['Bucket'], $filename, $tmpFile
        );
        unlink($tmpFile);
        return $GLOBALS['oss']['ossUrl'] . $filename;
    }

    /**
     *URL生成二维码图片 返回本地路径
     */
    public static function urlToImgUrl ($url, $filename = '') {
        if (isset($GLOBALS['oss'])) {
            return self::urlToImgUrl1($url, $filename);
        }
        $base64 = App::Qrcode($url);
        $base64 = substr($base64, strpos($base64, ',') + 1);
        $tmpFile = sys_get_temp_dir() . DS . uniqid() . '.png';
        $file = fopen($tmpFile, "w");
        fwrite($file, base64_decode($base64));
        fclose($file);
        $filename = $filename ?: '/upload/' . date('Y_m_d_') . uniqid() . '.png';

        $path = APP_DIR . $filename;
        $bool = rename($tmpFile, $path);
        if (!$bool) {
            return false;
        }
        return $filename;
    }


    /**
     *识别二维码图片 返回二维码内容
     */
    public static function qrCodeContent ($url) {
        $qrcode = new Zxing\QrReader($url);
        $text = $qrcode->text(); //return decoded text from QR Code
        return $text;
    }

    /**
     * 判断金额类型
     * @param $money
     * @return bool
     */
    public static function isMoney ($money) {
        if (!is_numeric($money)) {
            return false;
        }
        if (doubleval($money) <= 0) {
            return false;
        }
        return true;
    }

    /**
     *      获取当前的域名:http://payment.web
     * */
    public static function nowUrl () {
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
                $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        if ($host == 'doudoupay.com') {
            $host = 'api.doudoupay.com';
        }
        return $http_type . $host;
    }


    /**
     * 过滤表情
     * @param $str
     * @return string|string[]|null
     */
    public static function filterEmoji ($str) {
        $str = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);

        return $str;
    }


//定义图片字符集
    public function array_iconv ($data, $in_charset = 'GBK', $out_charset = 'UTF-8') {
        if (!is_array($data)) {
            $output = iconv($in_charset, $out_charset, $data);
        } elseif (count($data) === count($data, 1)) {//判断是否是二维数组
            foreach ($data as $key => $value) {
                $output[$key] = iconv($in_charset, $out_charset, $value);
            }
        } else {
            eval_r('$output = ' . iconv($in_charset, $out_charset, var_export($data, TRUE)) . ';');
        }
        return $output;
    }

    public function getfiles ($dir, &$files = array()) {
        if (!file_exists($dir) || !is_dir($dir)) {
            return mkdir($dir);
        }
        if (substr($dir, -1) == '/') {
            $dir = substr($dir, 0, strlen($dir) - 1);
        }
        $_files = scandir($dir);
        foreach ($_files as $v) {
            if ($v != '.' && $v != '..') {
                if (is_dir($dir . '/' . $v)) {
                    $this->getfiles($dir . '/' . $v, $files);
                } else {
                    $files[] = $dir . '/' . $v;
                }
            }
        }
        return $files;
    }

    /**
     * 字符串分别提取数字,字符串
     * $return [0]:数字
     *  $return [1]:字符串
     */

    function findNum ($str = '') {
        $str = trim($str);
        if (empty($str)) {
            return '';
        }
        $result = ['', ''];
        for ($i = 0; $i < strlen($str); $i++) {
            if (is_numeric($str[$i])) {
                $result[0] .= $str[$i];
            } else {
                $result[1] .= $str[$i];
            }
        }
        return $result;
    }

    //字符串自动补全
    static function my_str_pad ($input, $pad_length, $pad_string, $pad_type) {
        $strlen = (strlen($input) + mb_strlen($input, 'UTF8')) / 2;
        if ($strlen < $pad_length) {
            $difference = $pad_length - $strlen;
            switch ($pad_type) {
                case STR_PAD_RIGHT:
                    return $input . str_repeat($pad_string, $difference);
                    break;
                case STR_PAD_LEFT:
                    return str_repeat($pad_string, $difference) . $input;
                    break;
                default:
                    $left = $difference / 2;
                    $right = $difference - $left;
                    return str_repeat($pad_string, $left) . $input . str_repeat($pad_string, $right);
                    break;
            }
        } else {
            return mb_substr($input, 0, $pad_length - 3, 'utf-8') . '...';
        }
    }


    //字符串换行
    function str_wrap ($str, $num = 15) {
        $page = mb_strlen($str) / $num;
        if ($page <= 1) {
            return [$str];
        } else {
            $page = floor($page);
            $address = [];
            for ($i = 0; $i <= $page; $i++) {
                $start = $i * $num;
                array_push($address, mb_substr($str, $start, ($i + 1) * $num, 'utf-8'));
            }
            return $address;
            //   $dataRow['msg']['content'] = array_merge($dataRow['msg']['content'],$address);
        }
    }


    //放款速度 单位为分钟 >60为小时 >3600为天
    function timeUnitConversion ($num) {
        $re = $num;
        if ($num < 60) {
            $re = $num . '分钟';
        } elseif ($num >= 60 and $num < 3600) {
            $re = $num . '小时';
        } elseif ($num >= 3600) {
            $re = $num . '天';
        }
        return $re;
    }


    /**
     * 判断二维数组中是否含有某个值
     */
    public static function deep_in_array ($value, $array) {
        foreach ($array as $item) {
            if (!is_array($item)) {
                if ($item == $value) {
                    return true;
                } else {
                    continue;
                }
            }

            if (in_array($value, $item)) {
                return true;
            } else if (self::deep_in_array($value, $item)) {
                return true;
            }
        }
        return false;
    }

    /***
     * 获取操作系统版本
     * @return string
     */
    public static function get_device_type () {
        //全部变成小写字母
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $type = 'other';
        //分别进行判断
        if (strpos($agent, 'iphone') || strpos($agent, 'ipad')) {
            $type = 'ios';
        }

        if (strpos($agent, 'android')) {
            $type = 'android';
        }
        return $type;
    }
}