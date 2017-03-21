<?php
/**
 * Created by PhpStorm.
 * User: costa92
 * Date: 2017/3/16
 * Time: 下午1:26
 */

namespace Costa92\Wechat;


use Illuminate\Support\Facades\Facade as LaravelFacade;

class Facade extends LaravelFacade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(){
        return 'WechatService';
    }
}