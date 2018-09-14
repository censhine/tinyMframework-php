<?php
/**
 * Created by PhpStorm.
 * User: chengxiang
 * Date: 2017/11/25
 * Time: 19:20
 */

namespace app\index\controller;

use core\Controller;
use core\Model;

class Index extends Controller
{
    public function index()
    {
		echo config('weixin.appserect');
        $list = $model = Model::db()->table('article')->where([
        	'id'=>['>',0],
			'title'=>['<>',''],
			])
            ->order('id desc')
			->group('id')
            ->limit(10)
			->select();
		Model::db()->table('article')->where(['id'=>['>',1]])->update(['title'=>'hello,world']);
		Model::db()->table('article')->insert(['title'=>generate_random_str(),'content'=>'agsfsfsf','add_time'=>date('Y-m-d H:i:s',time())]);
        $this->assign('title', 'hello,world!');
        $this->assign('welcome', generate_random_str());
        $this->assign('list', $list);
        $this->display('index/index');
    }

    public function demo()
    {
        $this->assign('list', 'hello,world');
        $this->display('index/demo');
    }
}