<?php
/**
 * Created by PhpStorm.
 * User: costa92
 * Date: 2017/3/24
 * Time: 下午2:51
 */

namespace Costa92\Wechat\DataSql;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class SqlUser implements DataSql
{
    private $_table;
    public  function __construct()
    {
    }

    public function setTable($table)
    {
        $this->_table =$table;
        if(!$this->isTable()){
            $this->runSql();
        }
        return $this;
    }

    public function getTable()
    {
       return $this->_table;
    }


    public function runSql()
    {
        Schema::create("wx_users",function ($table){
            $table->engine="InnoDB";
            $table->increments('id');
            $table->char('openid',50);
            $table->text("nickname");
            $table->tinyInteger('sex')->default(0);
            $table->text("headimgurl");
            $table->char("language",25);
            $table->char('city',25);
            $table->char('province',25);
            $table->char('country',25);
            $table->dateTime('addtime');
            $table->rememberToken();
        });
    }


    public function find($uid)
    {
        return DB::table($this->getTable())->where('id','=',$uid)->get();
    }

    public function findOpnenId($openid){
        return DB::table($this->getTable())->where('openid','=',$openid)->get();
    }

    public function isTable()
    {
        if(DB::select("SHOW TABLES LIKE '{$this->getTable()}'")){
            return true;
        }
        return false;
    }

    public function save($data)
    {

    }
}