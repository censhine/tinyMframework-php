<?php
/**
 * Created by PhpStorm.
 * User: chengxiang
 * Date: 2017/11/25
 * Time: 21:09
 */
namespace core;
$config = include_once './config/config.php';
include_once './common/function.php';
class Start{

    protected $load_maps = [];

    public function __construct()
    {
        spl_autoload_register([$this, 'autoload']);
    }

    public function autoLoad($className)
    {
        $pos = strrpos($className,'\\');
        $namespace = substr($className,0, $pos);
        $realClassName = substr($className,$pos+1);

        $this->loadMaps($namespace, $realClassName);
    }

    public function loadMaps($namespace, $realClassName)
    {
        $filePath = str_replace('\\','/',$namespace);
        $classFilePath = $filePath.'/'.ucfirst(strtolower($realClassName)).'.php';
        if(isset($this->load_maps[$namespace]) || file_exists($classFilePath))
        {
            include_once $classFilePath;
            $this->addMaps($namespace, $realClassName);
        }
        else
        {
            die($classFilePath.' cannot be found!');
        }
    }

    public function addMaps($namespace, $realClassName)
    {
        if( !array_key_exists($namespace, $this->load_maps) )
        {
            $this->load_maps[$namespace] = $realClassName;
        }
    }

    public static function router()
    {
        new self();
        $m = isset($_GET['m']) && !empty($_GET['m']) ? $_GET['m'] : 'index';
        $a = isset($_GET['a']) && !empty($_GET['a']) ? $_GET['a'] : 'index';
        $className = "\\app\\index\\controller\\";
        $class =  $className.ucfirst(strtolower($m));
		$requestObject = new $class();
        call_user_func([$requestObject, $a]);
    }

    public function __get($name)
    {
        // TODO: Implement __get() method.
        if($name == 'load_maps')
        {
            return $this->load_maps;
        }
    }
}