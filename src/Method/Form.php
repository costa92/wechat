<?php
/**
 * Created by PhpStorm.
 * User: costa92
 * Date: 2017/3/20
 * Time: 上午11:01
 */

namespace Costa92\Wechat\Method;

use Costa92\Wechat\DataSql\SqlForm;
class Form
{

    private $_table;
    private  $_SqlForm;
    public function __construct(){

    }

    /**
     * @param $table
     * @param $id
     * @param bool $bool
     */
    public function find($table,$uid,$bool=true){
        return $this->getSqlForm($table)->find($uid);
    }


    public function save($table,$data){
        if(isset($data['x_uid'])){
            if($this->find($table,$data['x_uid'])){
                return $this->getSqlForm($table)->save($data);
            }
        }
        return false;
    }

    public function getSqlForm($table){
        $SqlForm = new SqlForm();
        return $SqlForm->setTable($table);
    }
}