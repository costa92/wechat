<?php
/**
 * Created by PhpStorm.
 * User: costa92
 * Date: 2017/3/16
 * Time: 下午1:01
 */
namespace Costa92\Wechat;


use Costa92\Wechat\Services\WechatService;
use Illuminate\Support\ServiceProvider;

class WechatServiceProvider extends  ServiceProvider
{
    public function boot(){

    }


    public function register()
    {
        $this->app->bind("WechatService",function ($app){
            return new WechatService();
        });
    }
}