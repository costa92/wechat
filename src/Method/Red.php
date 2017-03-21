<?php
/**
 * Created by PhpStorm.
 * User: costa92
 * Date: 2017/3/20
 * Time: 下午12:38
 */

namespace Costa92\Wechat\Method;
use Costa92\Wechat\DataSql\SqlRed;
use  Costa92\Wechat\Red\Red as PayRed;

class Red
{
    public function __construct()
    {
    }

    public function pay($table="",$data,$openid="",$uid = 0){


         $arr_data =array(
             'x_uid'=>$uid,
             'red'=>$data->hasRed->send_cash,
         );
         $this->save($table,$arr_data);

        $red =  new PayRed($data);
        $red->pay($openid);
    }


    public function find($table,$uid){
        return $this->getSqlRed($table)->find($uid);
    }


    public function save($table,$data){
        if(isset($data['x_uid'])){
            if($this->find($table,$data['x_uid'])){
                return $this->getSqlRed($table)->save($data);
            }
        }
        return false;
    }

    public function getSqlRed($table){
        $SqlForm = new SqlRed();
        return $SqlForm->setTable($table);
    }
}