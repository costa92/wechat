<?php

/**
 * Created by PhpStorm.
 * User: costa92
 * Date: 2017/3/20
 * Time: 下午1:55
 */
namespace  Costa92\Wechat\Red;

class Red
{
    private static $wx_config;

    private $app_id = '';
    private $app_secret = '';
    private $app_mchid = '';

    protected $_nick_name;
    protected $_send_name;
    protected $_send_activict;
    protected $_send_cash;
    protected $_send_wishing;

    /**
     * Red constructor.
     * @param string $nick_name 活动名称
     * @param string $send_name 红包提供方
     * @param string $send_cash 红包金额
     * @param string $send_wishing 红包祝福
     * @param string $send_activict 红包活动
     */
    public function __construct($data)
    {
        $this->app_id         =  $data->wechat->appid;
        $this->app_secret     =  $data->wechat->secret;
        $this->_nick_name     =  $data->name;
        $this->_send_name     =  $send_name?$send_name:self::$wx_config['_send_name'];
        $this->_send_cash     =  $send_cash?$send_cash:self::$wx_config['_send_cash'];
        $this->app_mchid      =  $data->wechat->app_mchid;
        $this->_send_activict =  $data->name;
        $this->_send_wishing  =  $send_wishing;
    }

    /**
     * 微信支付
     *
     * @param string $openid 用户openid
     */

    public function pay($re_openid,$db=null)
    {
        include_once('WxHongBaoHelper.php');
        $commonUtil = new CommonUtil();
        $wxHongBaoHelper = new WxHongBaoHelper();

        $wxHongBaoHelper->setParameter("nonce_str", $this->great_rand());//随机字符串，丌长于 32 位
        $wxHongBaoHelper->setParameter("mch_billno", $this->app_mchid.date('YmdHis').rand(1000, 9999));//订单号
        $wxHongBaoHelper->setParameter("mch_id", $this->app_mchid);//商户号
        $wxHongBaoHelper->setParameter("wxappid", $this->app_id);
        $wxHongBaoHelper->setParameter("nick_name", $this->_nick_name);//提供方名称
        $wxHongBaoHelper->setParameter("send_name", $this->_send_name);//红包发送者名称
        $wxHongBaoHelper->setParameter("re_openid", $re_openid);//相对于医脉互通的openid
        $wxHongBaoHelper->setParameter("total_amount", $this->_send_cash);//付款金额，单位分
        $wxHongBaoHelper->setParameter("min_value", 700);//最小红包金额，单位分
        $wxHongBaoHelper->setParameter("max_value", 100);//最大红包金额，单位分
        $wxHongBaoHelper->setParameter("total_num", 1);//红包収放总人数
        $wxHongBaoHelper->setParameter("wishing", $this->_send_wishing);//红包祝福诧
        $wxHongBaoHelper->setParameter("client_ip", '127.0.0.1');//调用接口的机器 Ip 地址
        $wxHongBaoHelper->setParameter("act_name", $this->_send_activict);//活劢名称
        $wxHongBaoHelper->setParameter("remark", '快来抢！');//备注信息
        $postXml = $wxHongBaoHelper->create_hongbao_xml();
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
        $responseXml = $wxHongBaoHelper->curl_post_ssl($url, $postXml);
//         $responseObj = simplexml_load_string($responseXml, 'SimpleXMLElement', LIBXML_NOCDATA);
//         return $responseObj->return_code;
        return $this->xmlToArray($responseXml) ;
    }


    function xmlToArray($xml){

        //禁止引用外部xml实体

        libxml_disable_entity_loader(true);

        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        $val = json_decode(json_encode($xmlstring),true);
        return $val;

    }


    /**
     * 获取微信授权链接
     *
     * @param string $redirect_uri 跳转地址
     * @param mixed $state 参数
     */
    public function get_authorize_url($redirect_uri = '', $state = '')
    {
        $redirect_uri = urlencode($redirect_uri);
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->app_id}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_userinfo&state={$state}#wechat_redirect";
        echo "<script language='javascript' type='text/javascript'>";
        echo "window.location.href='$url'";
        echo "</script>";
    }

    /**
     * 获取授权token
     *
     * @param string $code 通过get_authorize_url获取到的code
     */
    public function get_access_token($code = '')
    {
//         $token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->app_id}&secret={$this->app_secret}&code={$code}&grant_type=authorization_code";
        $token_data = $this->http($token_url);
        if(!empty($token_data[0]))
        {
            return json_decode($token_data[0], TRUE);
        }

        return FALSE;
    }

    /**
     * 获取授权后的微信用户信息
     *
     * @param string $access_token
     * @param string $open_id
     */
    public function get_user_info($access_token = '', $open_id = '')
    {
        if($access_token && $open_id)
        {
            $access_url = "https://api.weixin.qq.com/sns/auth?access_token={$access_token}&openid={$open_id}";
            $access_data = $this->http($access_url);
            $access_info = json_decode($access_data[0], TRUE);
            if($access_info['errmsg']!='ok'){
                exit('页面过期');
            }
            $info_url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$open_id}&lang=zh_CN";
            $info_data = $this->http($info_url);
            if(!empty($info_data[0]))
            {
                return json_decode($info_data[0], TRUE);
            }
        }

        return FALSE;
    }
    /**
     * Http方法
     *
     */
    public function http($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $output = curl_exec($ch);//输出内容
        curl_close($ch);
        return array($output);
    }

    /**
     * 生成随机数
     *
     */
    public function great_rand(){
        $str = '1234567890abcdefghijklmnopqrstuvwxyz';
        $t1 = "";
        for($i=0;$i<30;$i++){
            $j=rand(0,35);
            $t1 .= $str[$j];
        }
        return $t1;
    }
}