<?php
/**
 * Created by PhpStorm.
 * User: chengxiang
 * Date: 2017/11/25
 * Time: 19:20
 */

namespace app\index\controller;

use core\Controller;

class Index extends Controller
{
    public function index()
    {
    	//获取配置信息
		$appid = config('weixin.appid');
		$appSecert = config('weixin.appserect');

		//数据库操作 示例
		/**
		 * 增加数据
		 */
		$this->db->table('article')
			->insert([
				'title'=>generate_random_str(),
				'content'=>'agsfsfsf',
				'add_time'=>date('Y-m-d H:i:s',time())
			]);

		/**
		 * 删除数据
		 */
		$this->db->table('article')->where(['id'=>1])->delete();

		/**
		 * 修改数据
		 */
		$this->db->table('article')
			->where(['id'=>['>',1]])
			->update(['title'=>'hello,world']);

		/**
		 * 查询数据（多条信息)
		 */
		$list = $this->db->table('article')
			->where([
				'id'=>['>',0],
				'title'=>['<>',''],
			])
			->order('id desc')
			->group('id')
			->limit(10)
			->select();

		/**
		 * 获取单条数据
		 */
		$row = $this->db->getRow('article',['id'=>2],'id desc');
		var_dump($row);
		
		//传递模板变量
        $this->assign('title', 'hello,world!');
        $this->assign('welcome', generate_random_str());
        $this->assign('list', $list);
        //渲染模板
        $this->render('index/index');
    }

    public function demo()
    {
        $this->assign('list', 'hello,world');
        $this->render('index/demo');
    }
}