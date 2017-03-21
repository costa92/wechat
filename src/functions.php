<?php
/**
 * Created by PhpStorm.
 * User: costa92
 * Date: 2017/3/21
 * Time: 下午4:43
 */

if (function_exists("mkdir_file")){
    function mkdir_file($path){
        if($path){
            if(!file_exists($path)){
                mkdir($path,0777,true);
            }
        }
    }
}