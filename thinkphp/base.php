<?php

//引导文件

//thinkphp版本
define('THINK_VERSION', '5.0.3');
//开始时间
define('THINK_START_TIME', microtime(true));
//获取分配php内存量
define('THINK_START_MEM', memory_get_usage());
//定义.php 的替代
define('EXT', '.php');
//定义 '\/'的替代
define('DS', DIRECTORY_SEPARATOR);
//定义think的路径
defined('THINK_PATH') or define('THINK_PATH', __DIR__.DS);
//程序库路径
defined('LIB_PATH') or define('LIB_PATH', THINK_PATH.'library'.DS);
//核心代码路径
defined('CORE_PATH') or define('CORE_PATH', LIB_PATH.'think'.DS);
//特质目录traits
defined('TRAIT_PATH') or define('TRAIT_PATH', LIB_PATH.'traits'.DS);
//app的目录
defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . DS);
//APP_PATH的真实目录
defined('ROOT_PATH') or define('ROOT_PATH', dirname(realpath(APP_PATH)) . DS);
//继承的目录
defined('EXTEND_PATH') or define('EXTEND_PATH', ROOT_PATH . 'extend' . DS);
//第三方类库目录
defined('VENDOR_PATH') or define('VENDOR_PATH', ROOT_PATH . 'vendor' . DS);
/*---------------缓存目录-------------------*/
defined('RUNTIME_PATH') or define('RUNTIME_PATH', ROOT_PATH . 'runtime' . DS);
defined('LOG_PATH') or define('LOG_PATH', RUNTIME_PATH . 'log' . DS);
defined('CACHE_PATH') or define('CACHE_PATH', RUNTIME_PATH . 'cache' . DS);
defined('TEMP_PATH') or define('TEMP_PATH', RUNTIME_PATH . 'temp' . DS);
/*----------------------------------*/
defined('CONF_PATH') or define('CONF_PATH', APP_PATH); // 配置文件目录
defined('CONF_EXT') or define('CONF_EXT', EXT); // 配置文件后缀
defined('ENV_PREFIX') or define('ENV_PREFIX', 'PHP_'); // 环境变量的配置前缀
// 环境常量
define('IS_CLI', PHP_SAPI == 'cli' ? true : false);
define('IS_WIN', strpos(PHP_OS, 'WIN') !== false);

// 载入Loader类
require CORE_PATH . 'Loader.php';

//加载环境配置
if (is_file(ROOT_PATH.'.env')) {

	//把文件内容读成一个数组 true二维数组
	$name = parse_ini_file(ROOT_PATH.'.env', true);

	foreach ($name as $key => $val) {
		
		$name = ENV_PREFIX.strtoupper($key);

		if (is_array($val)) {
			
			foreach ($val as $k => $v) {
				
				$name = $name.'_'.strtoupper($v);
				
				putenv("$name=$v");
			}
		} else {
			putenv("$name=$val");
		}
	}
}

//注册自动加载
think\Loader::register();