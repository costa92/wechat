<?php

/**
 * Created by PhpStorm.
 * User: costa92
 * Date: 2017/3/16
 * Time: 下午1:16
 */
namespace Costa92\Wechat\Services;
use Costa92\Wechat\Https;
use Costa92\Wechat\Method\Form;
use Costa92\Wechat\Method\Jssdk;
use Costa92\Wechat\Method\Red;
use Costa92\Wechat\Method\Wechat;
use Symfony\Component\VarDumper\Caster\ReflectionCaster;

class WechatService
{
    protected $_params = array();
    public function __construct(){

    }

    public function WechatMethod($method=""){
        if($method){
           return  $this->reflection(Wechat::class,$method);
        }
        return false;
    }

    public function JssdkMethod($method=""){
        if($method){
            return  $this->reflection(Jssdk::class,$method);
        }
        return false;
    }

    public function Form($method=""){
        if($method){
            return  $this->reflection(Form::class,$method);
        }
        return false;
    }


    public function WechatRed($method=""){
        if($method){
            return  $this->reflection(Red::class,$method);
        }
        return false;
    }

    /**
     * 设置参数
     * @param $params
     * @return $this|bool
     */
    public function setParams($params){
        if(is_array($params)){
            $this->_params = $params;
            return $this;
        }
        return false;
    }

    /**
     * 获取参数
     * @return array
     */
    public function getParams(){
        return $this->_params;
    }

    /**
     * 反射
     * @param string $class
     * @param string $method
     * @return bool|mixed
     */
    protected function reflection($class = "",$method=""){
        if($class && $method){
            $reflectionMethod = new \ReflectionMethod($class,$method);
            return $reflectionMethod->invokeArgs(new $class,$this->getParams());
        }
        return false;
    }
}