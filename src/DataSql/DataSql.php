<?php
/**
 * Created by PhpStorm.
 * User: costa92
 * Date: 2017/3/20
 * Time: 上午11:08
 */

namespace Costa92\Wechat\DataSql;


interface DataSql
{
    public function setTable($table);

    public function getTable();

    public function isTable();

    public function runSql();

    public function find($uid);

    public function save($data);

}