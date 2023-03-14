<?php
/**
 * 机器翻译2.0(niutrans) WebAPI 接口调用示例
 * 运行前：请先填写Appid、APIKey、APISecret
 * 
 * 1.接口文档（必看）：https://www.xfyun.cn/doc/nlp/niutrans/API.html
 * 2.错误码链接：https://www.xfyun.cn/document/error-code （错误码code为5位数字）
 * 3.个性化翻译术语自定义：
 * ***登陆开放平台 https://www.xfyun.cn/
 * ***在控制台--机器翻译(niutrans)--自定义翻译处
 * ***上传自定义翻译文件（打开上传或更新窗口，可下载示例文件）
 * @author iflytek
 */
class ots_test {
    function tocurl($url, $header, $content){
        $ch = curl_init();
        if(substr($url,0,5)=='https'){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
            curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        if (is_array($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_POST, true);
        if (!empty($content)) {
            if (is_array($content)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($content));
            } else if (is_string($content)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
            }
        }
        $response = curl_exec($ch);
        $error=curl_error($ch);
        //var_dump($error);
        if($error){
            die($error);
        }
        $header = curl_getinfo($ch);

        curl_close($ch);
        $data = array('header' => $header,'body' => $response);
        return $data;
    }
    function xfyun($text,$to = 'en',$from = 'cn') {
        //在控制台-我的应用-机器翻译获取
        $app_id = "";
        //在控制台-我的应用-机器翻译获取
        $api_sec = "";
        //在控制台-我的应用-机器翻译获取
        $api_key = "";
		// 机器翻译接口地址
        $url = "https://ntrans.xfyun.cn/v2/ots";

        //body组装
       // $text = "中华人民共和国于1949年成立";//待翻译文本
        $body = json_encode($this->getBody($app_id, $from, $to, $text));

        // 组装http请求头
        $date =gmdate('D, d M Y H:i:s') . ' GMT';

        $digestBase64  = "SHA-256=".base64_encode(hash("sha256", $body, true));
        $builder = sprintf("host: %s
date: %s
POST /v2/ots HTTP/1.1
digest: %s", "ntrans.xfyun.cn", $date, $digestBase64);
        // echo($builder);
        $sha = base64_encode(hash_hmac("sha256", $builder, $api_sec, true));

        $authorization = sprintf("api_key=\"%s\", algorithm=\"%s\", headers=\"%s\", signature=\"%s\"", $api_key,"hmac-sha256",
            "host date request-line digest", $sha);

        $header = [
            "Authorization: ".$authorization,
            'Content-Type: application/json',
            'Accept: application/json,version=1.0',
            'Host: ntrans.xfyun.cn',
            'Date: ' .$date,
            'Digest: '.$digestBase64
        ];
        $response = $this->tocurl($url, $header, $body);
        return json_decode($response['body'],true);
    }

    function getBody($app_id, $from, $to, $text) {
        $common_param = [
            'app_id'   => $app_id
        ];

        $business = [
            'from' => $from,
            'to'   => $to,
        ];

        $data = [
            "text" => base64_encode($text)
        ];

        return $body = [
            'common' => $common_param,
            'business' => $business,
            'data' => $data
        ];
    }
}

