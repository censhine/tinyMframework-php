<?php
/**
 * Created by PhpStorm.
 * 视图模版类
 * User: chengxiang
 * Date: 2017/11/26
 * Time: 13:38
 */
namespace core;

class Template
{
    protected $template_dir;//模版目录
    protected $cache_dir;//编译目录
    protected $cache_life_time;//编译文件缓存时间
    protected $vars = [];//assign变量数组

    /**
     * 模版构造方法
     * Template constructor.
     */
    function __construct()
    {
        $this->cache_dir = './cache/';
        $this->template_dir = './app/index/view/';
        $this->cache_life_time = 60;
    }

    /**
     * 模版变量传递
     * @param $key
     * @param $val
     * @return array
     */
    function assign($key, $val)
    {
        $this->vars[$key] = $val;
    }

    /**
     * 显示模版文件
     * @param $tpl 模版文件
     * @param bool $is_include 是否先编译后包含
     * @param string $uri 路径
     * @return void
     */
    function display($tpl, $is_include = true, $uri = "")
    {
        //解析模版变量
        extract($this->vars);

        $tpl_name = rtrim($tpl,'.html');
        $tpl_name = ltrim($tpl_name,'/');

        //拼接模版路径
        $html_tpl_file = $this->template_dir.$tpl_name.'.html';

        //解析模版
        $content = file_get_contents($html_tpl_file);
        $content = $this->compiler($content);

        $php_cache_file = $this->cache_dir.md5($tpl_name).'.php';

        //判断编译文件是否过期
        $is_modify = filemtime($html_tpl_file) + $this->cache_life_time > time() ? true : false;
        $is_changed = filectime($html_tpl_file) + $this->cache_life_time > time() ? true : false;

        //模版文件是否有改动
        if( $is_modify || $is_changed || !file_exists($php_cache_file))
        {
            file_put_contents($php_cache_file, $content);
        }

        //编译后是否需要包将文件含进来
        if( $is_include == true ) {
            include_once($php_cache_file);
        }
    }

    /**
     * 编译模版文件（正则替换）
     * @param $content 内容
     * @return string
     */
    protected function compiler($content)
    {
        $array = [
            '{$%%}'=>'<?=$\1;?>',
            '{if %%}'=>'<?php if(\1):?>',
            '{/if}'=>'<?php endif;?>',
            '{foreach %%}'=>'<?php foreach (\1):?>',
            '{/foreach}'=>'<?php endforeach;?>',
            '{include file=%%}'=>'',
        ];

        foreach ($array as $k => $val)
        {
            $pattern = '#'.str_replace('%%','(.+?)', preg_quote($k,'#')).'#';
            if(strstr($pattern, 'include')){
                $content = preg_replace_callback($pattern, [$this,'parselInclude'], $content);
            }else{
                $content = preg_replace($pattern, $val, $content);
            }
        }

        return $content;

    }

    /**
     * 解析模版中include文件
     * @param $data 匹配的数组
     * @return string
     */
    protected function parselInclude($data)
    {
        $filename = trim($data[1],'\'"');
        $filename = rtrim($filename, '.html');
        $this->display($filename, true);
        $cache = md5($filename).'.php';
        $cachePath = rtrim($this->cache_dir,'/').'/'.$cache;
        return '<?php include "'.$cachePath.'";?>';
    }
}