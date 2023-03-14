<?php

Class Helper
{
    //致命错误级别
    const FATAL_ERROR = 'fatal_error';

    /**
     * 检查参数合法性
     * @param $name
     * @return int
     */
    public static function is_available_classname($name)
    {
        return preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $name);
    }

    /**
     * 获取规则的url
     * @param $c
     * @param $a
     * @param array $param
     * @return string
     */
    public static function url($c, $a, $param = array())
    {
        GLOBAL $__module;
        GLOBAL $GLOBALS;
        $rewrite = $GLOBALS['rewrite'];
        $c = empty($c) ? $rewrite['c'] : $c;
        $a = empty($a) ? $rewrite['a'] : $a;
        $params = empty($param) ? '' : http_build_query($param);
        if($_SERVER['SERVER_PORT'] ==443){
            $protocol = 'https';
        }else{
            $protocol = 'http';
        }
        if ($rewrite['isRewrite']) {
            if (!empty($params)) {
                $params = "?$params";
            }
            if ($__module == $rewrite['m'][0]) {
                $url = "$protocol://" . $_SERVER["HTTP_HOST"] . '/' . $c . '/' . $a . $params;
            } else {
                $url = "$protocol://" . $_SERVER["HTTP_HOST"] . '/' . $__module . '/' . $c . '/' . $a . $params;
            }
        } else {
            if ($__module != $rewrite['m'][0]) {
                $url = "$protocol://" . $_SERVER["SCRIPT_NAME"] . "?m=$__module&c=$c&a=$a$params";
            } else {
                $url = "$protocol://" . $_SERVER["SCRIPT_NAME"] . "?c=$c&a=$a$params";
            }
        }
        return $url;
    }

    /**
     * 设置路由
     */
    public static function setRoute()
    {
        $rewrite = $GLOBALS['rewrite'];
        $list_route = [$rewrite['m'][0], $rewrite['c'], $rewrite['a'],$rewrite['_p']];
        if ($rewrite['isRewrite'] && isset($_SERVER['REQUEST_URI'])) {
            $requestURI = $_SERVER['REQUEST_URI'];
            $requestURI = str_replace('?' . $_SERVER["QUERY_STRING"], '', $requestURI);
            $route = explode("/", $requestURI);
            if (in_array($route[1], $rewrite['m'])) {
                $list_route[0] = $route[1];
                $route = array_slice($route, 1, count($route));
            }
            $list_route[1] = empty($route[1]) ? $list_route[1] : $route[1];
            $list_route[2] = empty($route[2]) ? $list_route[2] : $route[2];
            $list_route[3] = empty($route[3]) ? $list_route[3] : $route[3];
        }

        $_REQUEST['m'] = strtolower(self::request("m", $list_route[0]));
        $_REQUEST['c'] = strtolower(self::request("c", $list_route[1]));
        $_REQUEST['a'] = strtolower(self::request("a", $list_route[2]));
        $_REQUEST['_p'] = strtolower(self::request("_p", $list_route[3]??''));
    }

    /**
     * 启动程序
     */
    public static function start()
    {
        GLOBAL $__module, $__action, $__controller,$__param;

        //模块对应目录
        if (!self::is_available_classname($__module)) {
            die("Err: Module name '$__module' is not correct!");
        }
        if (!is_dir(APP_PATH . '../controller' . DS . $__module)) {
            self::responseJson("Err: Module '$__module' is not exists!", 404);
        }

        if (!self::is_available_classname($__controller)) {
            self::responseJson("Err: Controller name '$__controller' is not correct!", 404);
        }
        $controller_name = $__controller . 'Controller';
        //处理restful
        $httpMethod = strtolower(empty($_SERVER['REQUEST_METHOD']) ? 'get' : $_SERVER['REQUEST_METHOD']);
        $action_name = $httpMethod . ucfirst($__action);//旧版的？问题 2022111104
//        $action2 = explode('?',$__action);
//        $action_name = $httpMethod . ucfirst($action2[0]);
        if (!class_exists(ucfirst($controller_name), true)) {
            self::responseJson("Err: Controller '$controller_name' is not exists!", 404);
        }
        $controller_obj = new $controller_name();

        if (!method_exists($controller_obj, $action_name)) {
            $action_name = 'action' . $__action;
            if (!method_exists($controller_obj, $action_name)) {
                self::responseJson("Err: Method '$action_name' of '$controller_name' is not exists!", 404);
            }
        };
        $controller_obj->$action_name($__param);
    }

    /**
     * 所有的输出格式统一 支持shell 输出
     * @param $message 输出对象
     * @param $code 输出错误码
     */
    public static function responseJson($message, $code = 0,$data=[],$count = 0,$totalRow = [])
    {
        if (PHP_SAPI === 'cli') {
            printf("[%s] %s", date('Y/m/d H:i:s'), $message . PHP_EOL);
        } else {
            header('x-powered-by:ES.1.0');
            header('Content-type: application/json');
            header('APP-CODE:'.$code);
            exit(json_encode(['code' => $code, 'msg' => $message, 'message' => $message,
                                 'count'=>$count,'data'=>$data,'totalRow'=>$totalRow]));
        }
    }


    /**
     * @param $msg
     * @param string $url
     * @param int $code 非0 错误提示
     */
    public static function redirect($msg, $url = '', $code = 0)
    {
        if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
            if (is_array($msg)) {
                exit(json_encode($msg));
            } else {
                self::responseJson(['alertStr' => $msg, 'redirect' => $url], $code);
            }
        } else {
            $strAlert = "";
            if (!empty($msg)) {
                $strAlert = "alert(\"{$msg}\");";
            }
            if ($url == "") {
                exit("<script>alert('$msg');window.history.go(-1);</script>");
            } else {
            }
            exit("<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><script>function sptips(){  {$strAlert} location.href=\"{$url}\";}</script></head><body onload=\"sptips()\"></body></html>");
        }
    }

    /**
     * 获取客户端ip
     * @return array|false|string
     */
    public static function userIp()
    {
        if (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                $ip = getenv("HTTP_X_FORWARDED_FOR");
            } else {
                if (getenv("REMOTE_ADDR")) {
                    $ip = getenv("REMOTE_ADDR");
                } else {
                    $ip = "Unknow";
                }
            }
        }
        return $ip;
    }

    /** 日志记录
     * @param $errMsg
     * @param $level (debug, info, error)
     */
    public static function log($errMsg, $level = 'info')
    {
        if(is_object($errMsg)){
            $errMsg = $errMsg->getMessage() . ' in ' . $errMsg->getFile() . ':' . $errMsg->getLine() . PHP_EOL ;
        }
        if(!is_string($errMsg)){
            $errMsg = json_encode($errMsg,JSON_UNESCAPED_UNICODE);
        }
        //shell 的操作权限跟web不一样，所以需要区分
        global $__module;
        $logPath = APP_DIR . DS . $GLOBALS['logPath'] . DS . $level .
            "_".$__module. date('Ymd') . ".log";
        error_log(date('Ymd H:i:s') . "  " . $errMsg . PHP_EOL, 3, $logPath);
        if (strtolower(trim($level)) === 'fatal_error') {
            if ($GLOBALS['debug'] ) {
                header('Content-type: application/json');
                die();
                // Helper::responseJson($errMsg, 500);
            } else {

//                self::pushException($errMsg);
                Helper::responseJson('异常查看系统日志', 500);
            }
        }
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
    /** 自定义错误
     * @param $errNo (错误码)
     * @param $errStr (错误说明)
     * @param $errFile 错误文件
     * @param $errLine 错误行号
     */
    public static function customError($errNo, $errStr, $errFile, $errLine)
    {
        $errMsg = "[{$errNo}] {$errStr} {$errFile} {$errLine} ";
        if ($errNo == E_ERROR) {
            $errNo = 'fatal_error';
            self::log($errMsg, $errNo);
        }
        if ($GLOBALS["debug"]) {
            echo $errMsg;
        }
    }
    /**requestAll获取所有信息设置默认值
     * @param $names [name=>default,name1=>default1]
     * @param $default
     * @param bool $isSafe
     * @return mixed
     */
    public static function requestAll($names=[], $default='', $isSafe = true)
    {
        $header = Helper::getallheaders();
        $request = [];
        $contentType = strpos($header['Content-Type'], 'application/json') !== false;
        $isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
        if($isPost && $contentType){
            $request = json_decode(file_get_contents("php://input"),true);
        }
        if(!is_array($names)){
            if($request){
                $param =  isset($request[$names])?$request[$names]:$default;
            }else{
                $param = Helper::request($names,$default,$isSafe);
            }
            return $isSafe ? str_replace("''", "", $param) : $param;
        }
        if(empty($names)){
            if($isPost){
                if($request){
                    $names = $request;
                }else{
                    $names = $_POST;
                }
            }else{
                $names = $_GET;
            }
        }
        $data = [];
        foreach ($names as $name => $def){
            if($request){
                $data[$name] = isset($request[$name])?$request[$name]:$def;
            }else{
                $data[$name] = Helper::request($name,$def,$isSafe);
            }
        }
        return $data;
    }
    /**request获取信息设置默认值
     * @param $name
     * @param $default
     * @param bool $isSafe
     * @return mixed
     */
    public static function request($name, $default, $isSafe = true)
    {

        $header = Helper::getallheaders();
        if($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($header['Content-Type'], 'application/json') !== false ){
            $request = json_decode(file_get_contents("php://input"),true);
        }else{
            $request = $_REQUEST;
        }
        if (!isset($request[$name])) {
            return $default;
        } else {
            return $isSafe ? str_replace("''", "", $request[$name]) : $request[$name];
        }
    }

    /** 字段过滤
     * @param array $input
     * @param $fields
     */
    public static function filterFields(array &$input, $fields)
    {
        $operator = ['*', '+', '-', '/', '#'];
        if (empty($fields)) {
            return;
        }
        foreach ($input as $k => $v) {
            $key = $k;
            if (in_array(substr($k, 0, 1), $operator)) {
                $key = substr($k, 1);
            }
            if (!in_array($key, $fields)) {
                unset($input[$k]);
            }
        }
    }

    /**
     * 异常推送
     * @param Throwable $e
     * @param array     $context
     * @throws \JsonException
     */
    public static function pushException(Throwable $e, array $context = []): void
    {
        $className  = get_class($e);
        $contextStr = json_encode($context, JSON_THROW_ON_ERROR);
        $title      = $GLOBALS('ding_ding_robot.title');
        $ctx        = $contextStr ? '' : <<<CTX
### 异常上下文
```
{$context}
```  \n\n
CTX;

        $str = <<<ET
## {$title}\n\n

### 错误: 
 <span style="color: #ff0000; font-family: 黑体,sans-serif; "> {$e->getMessage()} </span> \n\n  

### 异常类: 
 <span style="color: #ff0000; font-family: 黑体,sans-serif; "> {$className} </span> \n\n

{$ctx}

### 行号: 
{$e->getLine()}\n\n

### 文件: 
{$e->getFile()}\n\n

### 堆栈:
```text
{$e->getTraceAsString()}
```
ET;

        $content = [
            'title' => $e->getMessage(),
            'text'  => $str
        ];

        PushToDingDingJob::doJob($content);
    }
}
