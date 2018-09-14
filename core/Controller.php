<?php
/**
 * Created by PhpStorm.
 * 控制器类
 * User: chengxiang
 * Date: 2017/11/25
 * Time: 19:21
 */

namespace core;

class Controller extends Template
{
    public function __construct()
    {
		date_default_timezone_set(config('DATE_DEFAULT_TIMEZONE_SET'));
        parent::__construct();
    }
}