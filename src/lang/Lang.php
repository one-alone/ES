<?php

class Lang
{

    const lang_arr = ['cn','en','vi','ko','ms', 'id'];// 中文cn 英语en  越南语vi 韩语ko 马来语ms 印尼语id
    const json_file =  APP_PATH.'../lang/lang.json';

    /**
     * 实时翻译
     * @param $message
     * @param $key
     * @return string|string[]
     */
    public static function translate ($message, $key='en') {
        $jsonFile = self::json_file;
        $configs = json_decode(file_get_contents($jsonFile),true)?:[];
        preg_match_all("/%\w%/", $message, $match);
        if($match){
            foreach ($match[0] as &$replaceStr){
                $message = str_replace($replaceStr,'%_%',  $message);
                $replaceStr = trim($replaceStr,'%');
            }

        }
        if(!array_key_exists($message,$configs)){
            //走在线翻译
            LangJob::redisJoin($message);
        }
        $translateMsg = $configs[$message][$key] ? : $message;
        if($match){
            foreach ($match[0] as $replaceStr){
                $translateMsg = str_replace('%_%', $replaceStr, $translateMsg);
            }
        }
        return $translateMsg;
    }
    /**
     * 在线翻译 中文cn 英语en  越南语vi 韩语ko 马来语ms 印尼语id
     * @param $text
     * @return void
     */
    //
    public static function translateOnLine ($text) {
        preg_match_all("/%\w%/", $text, $match);
        if($match){
            foreach ($match[0] as $replaceStr){
                $text = str_replace($replaceStr,'%_%',  $text);
            }
            unset($replaceStr);
        }
        require_once ( 'ots_php_demo.php');
        $a = new ots_test();
        $enFileText = [];
        foreach (self::lang_arr as $to){
            $re = $a->xfyun($text,$to);
           // var_dump($re);
            if(!isset($re['code'])){
                continue;
            }
            if($re['code'] > 0){
                continue;
            }
            $message =$re['data']['result']['trans_result']['dst'];
            preg_match_all("/\s?%\s?\w\s?%\s?/", $message, $match);
            if($match){
                foreach ($match[0] as $replaceStr){
                    $message = str_replace($replaceStr,' %_% ',  $message);
                }
            }
            preg_match_all("/\s+\s+/", $message, $match);
            if($match){
                foreach ($match[0] as $replaceStr){
                    $message = str_replace($replaceStr,' ',  $message);
                }
            }
            $enFileText[$to] = $message;
           // $cnFileText[$text] = $re['data']['result']['trans_result']['src'];
        }

        self::updateConfig([$text=>$enFileText]);
    }

    /**
     * 写入语言配置json文件
     * @param $enFileText
     * @return void
     */
    public static function updateConfig ($enFileText) {
        $jsonFile = self::json_file;
        $configs = file_get_contents($jsonFile);
        $arr = json_decode($configs,true)?:[];//将整个文件内容读入到一个字符串中
        $newArr = array_merge($arr,$enFileText);
        //查看有无该文件，没有则创建
        $myFile = fopen($jsonFile, 'w');
        //要写入文件的内容
        fwrite($myFile,  json_encode($newArr, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
        //关闭文件
        fclose($myFile);

    }


}