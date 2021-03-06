<?php
/**
 * Created by PhpStorm.
 * User: costa92
 * Date: 2017/3/16
 * Time: 下午4:17
 */

namespace Costa92\Wechat\Method;

use Illuminate\Support\Facades\Redis as Redis;
use Costa92\Wechat\Https;
class Jssdk
{
    protected  $_valid_time = 3600;
    private  $_http;
    private $appId ="";
    private $secrt ="";
    public function __construct(){
        $this->_http = new Https();
    }

    public function getSignPackage($appId="" ,$secren =""){
        $this->appId = $appId;
        $this->secrt = $secren;
        $jsapiTicket = $this->getJsApiTicket();
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }

    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    private function getJsApiTicket() {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data= Redis::get('jsapi_ticket');
        if (!$data) {
            $accessToken = $this->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode($this->_http->httpGet($url));
            $ticket = $res->ticket;
            if ($ticket) {
                Redis::setex("jsapi_ticket",$this->_valid_time,$ticket) ;
            }
        } else {
            $ticket = $data;
        }

        return $ticket;
    }


    private function getAccessToken() {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
//    $data = json_decode(file_get_contents(".json"));
        $data =$data= Redis::get('access_token_red');
        if (!$data) {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->secrt";
            $res = json_decode($this->_http->httpGet($url));
            $access_token = $res->access_token;
            if ($access_token) {
                Redis::setex("access_token_red",$this->_valid_time,$access_token) ;
            }
        } else {
            $access_token = $data;
        }
        return $access_token;
    }
}