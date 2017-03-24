<?php

/**
 * Created by PhpStorm.
 * User: costa92
 * Date: 2017/3/16
 * Time: 下午2:54
 */
namespace  Costa92\Wechat\Method;
use Costa92\Wechat\Https;
use Costa92\Wechat\DataSql\SqlUser;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Wechat
{
    private $host="https://api.weixin.qq.com/";
    private $https;
    private $table ="wx_users";
    public function __construct(){
        $this->https = new Https();
    }

    /**
     * 获取用户配置信息
     *
     */
    public function getWechat($appid = "",$sercen=""){

    }


    public function getAccessToken($appid = "",$sercen="",$code=""){
           $url =$this->host."sns/oauth2/access_token?appid=".$appid."&secret=".$sercen.'&code='.$code."&grant_type=authorization_code";
           $rs=json_decode($this->https->httpGet($url));
           if(empty($rs->errcode)){
               return $rs;
           }
           return false;
    }


    public function getUserInfo($access_token,$openid){
            $url = $this->host.'sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
            $rs=json_decode($this->https->httpGet($url));
            return $rs;
    }


    public function UserInfo($appid = "",$sercen="",$code=""){
        $access_token = $this->getAccessToken($appid,$sercen,$code);
        if($access_token){
            $user = $this->getUserInfo($access_token->access_token,$access_token->openid);
           return $this->save($user,false);
        }
        return false;
    }


    public function save($data){
        if($data){
            $user = $this->findOpnenId($data->openid);
            if (!$user->toArray()){
                if(is_object($data)){
                    $data = $this->objectToArray($data);
                    $data['addtime']=Carbon::now("Asia/Shanghai")->format('Y-m-d H:m:s');
                    unset($data['privilege']);
                }
                $id= DB::table($this->table)->insertGetId($data);
                $user = $this->find($id);
            }
            return $user;
        }
        return false;
    }

    public function find($uid){
      return  $this->getSqlUser($this->table)->find($uid);
    }

    public function findOpnenId($openid){
        return  $this->getSqlUser($this->table)->findOpnenId($openid);
    }

    public function getSqlUser($table){
        $SqlForm = new SqlUser();
        return $SqlForm->setTable($table);
    }

    //数组转对象
   public function arrayToObject($e){
        if( gettype($e)!='array' ) return;
        foreach($e as $k=>$v){
            if( gettype($v)=='array' || getType($v)=='object' )
                $e[$k]=(object)arrayToObject($v);
        }
        return (object)$e;
    }

//对象转数组
    public function objectToArray($e){
        $e=(array)$e;
        foreach($e as $k=>$v){
            if( gettype($v)=='resource' ) return;
            if( gettype($v)=='object' || gettype($v)=='array' )
                $e[$k]=(array)$this->objectToArray($v);
        }
        return $e;
    }
}