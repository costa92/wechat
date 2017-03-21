<?php
/**
 * Created by PhpStorm.
 * User: costa92
 * Date: 2017/3/20
 * Time: 下午12:38
 */

namespace Costa92\Wechat\Method;
use  Costa92\Wechat\Red\Red as PayRed;

class Red
{
    public function __construct()
    {
    }

    public function pay($table,$data){

        $red =  new PayRed($data);
        print_r($red);
    }

}