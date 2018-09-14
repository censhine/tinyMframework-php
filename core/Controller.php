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
	public $db;
    public function __construct()
    {
    	$this->db = Model::db();
		date_default_timezone_set(config('DATE_DEFAULT_TIMEZONE_SET'));
        parent::__construct();
    }
}