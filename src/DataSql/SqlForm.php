<?php

/**
 * Created by PhpStorm.
 * User: costa92
 * Date: 2017/3/20
 * Time: 上午11:06
 */
namespace Costa92\Wechat\DataSql;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class SqlForm implements DataSql {
    public $_table;
    public function __construct()
    {

    }

    public function setTable($table)
    {
        $this->_table = $table."_from";
        if(!$this->isTable()){
            $this->runSql();
        }
        return $this;
    }

    public function getTable()
    {
        return $this->_table;
    }

    public function isTable()
    {
        if(DB::select("SHOW TABLES LIKE '{$this->_table}'")){
            return true;
        }
        return false;
    }

    public function runSql()
    {
        Schema::create($this->getTable(),function ($table){
            $table->increments('id');
            $table->integer('x_uid');
            $table->char('username');
            $table->char('phone');
            $table->integer('type');
            $table->timestamps();
        });
    }

    public function find($uid){
        return DB::table($this->getTable())->where('x_uid', '=', $uid)->get();
    }

    public function save($data){
        $data['type'] =  isset($data['type'])?$data['type']:1;
        $data['created_at'] = Carbon::now("Asia/Shanghai")->format('Y-m-d H:m:s');
        $data['updated_at'] = Carbon::now("Asia/Shanghai")->format('Y-m-d H:m:s');
        return DB::table($this->getTable())->insertGetId($data);
    }
}