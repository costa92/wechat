<?php
namespace  Costa92\Wechat\Red;
/**
* 微信红包类
* @author longqiuhong
*/
use Illuminate\Support\Facades\Redis;
use PhpParser\Node\Expr\Exit_;

include_once("CommonUtil.php");
include_once("SDKRuntimeException.class.php");
include_once("MD5SignUtil.php");
class WxHongBaoHelper
{

    protected $mch_arr =array();
    private $path;
    public function __construct(){
        $this->path = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR.'cert'.DIRECTORY_SEPARATOR;
    }

    public function set($key,$value){
         $this->mch_arr["$key"] = $value;
        return $this;
    }

    public function get($key){
        return $this->mch_arr["$key"];
    }

    var $parameters; //cft 参数
	function setParameter($parameter, $parameterValue) {
		$this->parameters[CommonUtil::trimString($parameter)] = CommonUtil::trimString($parameterValue);
	}
	function getParameter($parameter) {
		return $this->parameters[$parameter];
	}
	function check_sign_parameters(){
		if($this->parameters["nonce_str"] == null || 
			$this->parameters["mch_billno"] == null ||
			$this->parameters["mch_id"] == null || 
			$this->parameters["wxappid"] == null || 
			$this->parameters["nick_name"] == null || 
			$this->parameters["send_name"] == null ||
			$this->parameters["re_openid"] == null || 
			$this->parameters["total_amount"] == null || 
			$this->parameters["max_value"] == null || 
			$this->parameters["total_num"] == null || 
			$this->parameters["wishing"] == null || 
			$this->parameters["client_ip"] == null || 
			$this->parameters["act_name"] == null || 
			$this->parameters["remark"] == null || 
			$this->parameters["min_value"] == null
			)
		{
			return false;
		}
		return true;

	}
	/**
	  例如：
	 	appid：    wxd111665abv58f4f
		mch_id：    10000100
		device_info：  1000
		Body：    test
		nonce_str：  ibuaiVcKdpRxkhJA
		第一步：对参数按照 key=value 的格式，并按照参数名 ASCII 字典序排序如下：
		stringA="appid=wxd930ea5d5a258f4f&body=test&device_info=1000&mch_i
		d=10000100&nonce_str=ibuaiVcKdpRxkhJA";
		第二步：拼接支付密钥：
		stringSignTemp="stringA&key=192006250b4c09247ec02edce69f6a2d"
		sign=MD5(stringSignTemp).toUpperCase()="9A0A8659F005D6984697E2CA0A
		9CF3B7"
	 */
	protected function get_sign(){
	    define('PARTNERKEY',$this->get("key"));
		try {
			if (null == PARTNERKEY || "" == PARTNERKEY ) {
				throw new SDKRuntimeException("密钥不能为空！" . "<br>");
			}
			if($this->check_sign_parameters() == false) {   //检查生成签名参数
			   throw new SDKRuntimeException("生成签名参数缺失！" . "<br>");
		    }
			$commonUtil = new CommonUtil();
			ksort($this->parameters);
			$unSignParaString = $commonUtil->formatQueryParaMap($this->parameters, false);
			$md5SignUtil = new MD5SignUtil();
			return $md5SignUtil->sign($unSignParaString,$commonUtil->trimString(PARTNERKEY));
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}

	}
	
	//生成红包接口XML信息
	/*
	<xml>
		<sign>![CDATA[E1EE61A9]]</sign>
		<mch_billno>![CDATA[00100]]</mch_billno>
		<mch_id>![CDATA[888]]</mch_id>
		<wxappid>![CDATA[wxcbda96de0b165486]]</wxappid>
		<nick_name>![CDATA[nick_name]]</nick_name>
		<send_name>![CDATA[send_name]]</send_name>
		<re_openid>![CDATA[onqOjjXXXXXXXXX]]</re_openid>
		<total_amount>![CDATA[100]]</total_amount>
		<min_value>![CDATA[100]]</min_value>
		<max_value>![CDATA[100]]</max_value> 
		<total_num>![CDATA[1]]</total_num>
		<wishing>![CDATA[恭喜发财]]</wishing>
		<client_ip>![CDATA[127.0.0.1]]</client_ip>
		<act_name>![CDATA[新年红包]]</act_name>
		<act_id>![CDATA[act_id]]</act_id>
		<remark>![CDATA[新年红包]]</remark>
	</xml>
	*/
	function create_hongbao_xml($retcode = 0, $reterrmsg = "ok"){
		 try {
		    $this->setParameter('sign', $this->get_sign());
		    $commonUtil = new CommonUtil();
		    return  $commonUtil->arrayToXml($this->parameters);
		   
		}catch (SDKRuntimeException $e)
		{
			die($e->errorMessage());
		}		

	}
	
	function curl_post_ssl($url, $vars, $second=30,$aHeader=array())
	{
	    $this->SaveSsl();

		$ch = curl_init();
		//超时时间
		curl_setopt($ch,CURLOPT_TIMEOUT,$second);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		//这里设置代理，如果有的话
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);


		curl_setopt($ch,CURLOPT_SSLCERT,$this->path.$this->get("mch_id").DIRECTORY_SEPARATOR."apiclient_cert.pem");
		curl_setopt($ch,CURLOPT_SSLKEY,$this->path.$this->get("mch_id").DIRECTORY_SEPARATOR."apiclient_key.pem");
		curl_setopt($ch,CURLOPT_CAINFO,$this->path.$this->get("mch_id").DIRECTORY_SEPARATOR."rootca.pem");


		if( count($aHeader) >= 1 ){
			curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
		}
	 
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
		echo $data = curl_exec($ch);
		if($data){
			curl_close($ch);
			return $data;
		} else {
			$error = curl_errno($ch);
			curl_close($ch);
			return false;
		}
	}

    private function SaveSsl(){
        $mch_id =  Redis::get("mch_id".$this->get("mch_id"));
        if(!$mch_id){
             $path = $this->path.$this->get("mch_id").DIRECTORY_SEPARATOR;
             $this->mkdir_file($path);
             $this->save_Ssl($this->get("apiclient_cert"));
             $this->save_Ssl($this->get("apiclient_key"),2);
             $this->save_Ssl($this->get("rootca"),3);
             Redis::setex("mch_id",3600,$this->get("mch_id"));
        }
    }

    public function save_Ssl($data,$type=1){
        switch ($type){
            case 1:
                $name = "apiclient_cert.pem";
                break;
            case 2:
                $name = "apiclient_key.pem";
                break;
            case 3:
                $name = "rootca.pem";
                break;
            default:
                return false;
        }
        $ssl_path = $this->path.$this->get("mch_id").DIRECTORY_SEPARATOR.$name;
        touch($ssl_path);
        file_put_contents($ssl_path,$data);
    }

    public function mkdir_file($path){
        if($path){
            if(!file_exists($path)){
                mkdir($path,0777,true);
            }
        }
    }
}

?>