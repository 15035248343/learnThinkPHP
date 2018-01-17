<?php
namespace think;

//loader类用于自动加载
class Loader
{
	    /**
     * @var array 实例数组
     */
    protected static $instance = [];

    /**
     * @var array 类名映射
     */
    protected static $map = [];

    /**
     * @var array 命名空间别名
     */
    protected static $namespaceAlias = [];

    /**
     * @var array PSR-4 命名空间前缀长度映射
     */
    private static $prefixLengthsPsr4 = [];

    /**
     * @var array PSR-4 的加载目录
     */
    private static $prefixDirsPsr4 = [];

    /**
     * @var array PSR-4 加载失败的回退目录
     */
    private static $fallbackDirsPsr4 = [];

    /**
     * @var array PSR-0 命名空间前缀映射
     */
    private static $prefixesPsr0 = [];

    /**
     * @var array PSR-0 加载失败的回退目录
     */
    private static $fallbackDirsPsr0 = [];

    /**
     * @var array 自动加载的文件
     */
    private static $autoloadFiles = [];

    /**
     * [自动加载]
     * @access public
     * @param  $class 类名
     * @return bool
     */
    public static function autoload($class)
    {
    	//检查命名空间别名是否存在
    	if ( !empty(self::$namespaceAlias) ) {
    		
    		$namespace = dirname($class);

    		if ( isset(self::$namespace[$namespace]) ) {

    			$original = self::$namespace[$namespace] .'\\'. basename($class);

    			if (class_exists($original)) {
    				return class_alias($original, $class, false);
    			}
    		} 
    	}

    	//判断类是否存在
    	if ($file = self::findFile($class)) {
    		
            // 非 Win 环境不严格区分大小写
            if (!IS_WIN || pathinfo($file, PATHINFO_FILENAME) == pathinfo(realpath($file), PATHINFO_FILENAME)) {

            	__include_file($file);
            	return true;
            }
    	}


    	return false;
    }


    /**
     * 查找文件
     * @access private
     * @param string $class 类名
     * @return bool|string 
     */
    private function findFile($class)
    {
    	//类库映射
    	if ( !empty(self::$map[$class])) {
    		return self::$map[$class];
    	}

    	//检查规范 PSR-4
    	$logicalPathPsr4 = strtr($class, '\\', DS);

    	$first = $class[0];

    	//命名空间前缀长度映射
    	if (isset(self::$prefixLengthsPsr4[$first])) {

    		foreach (self::$prefixLengthsPsr4 as $prefix => $length) {
    			
    			if (0 === strpos($class, $prefix)) {
    				
    				//PSR-4 的加载目录
    				foreach (self::$prefixDirsPsr4 as $dir) {
    					
    					if (is_file($file = $dir.DS.substr($logicalPathPsr4, $length))) {
    						
    						return $file;
    					}
    				}
    			}
    		}
    	}

    	//查找PSR-4 fileback dirs 加载失败的回退目录
    	foreach (self::$fallbackDirsPsr4 as $dir) {
    		if (is_file($file = $dir.DS.substr($logicalPathPsr4, $length))) {
    						
				return $file;
			}
    	}

    	// 查找 PSR-0
        if (false !== $pos = strrpos($class, '\\')) {
            // namespace class name
            $logicalPathPsr0 = substr($logicalPathPsr4, 0, $pos + 1)
            . strtr(substr($logicalPathPsr4, $pos + 1), '_', DS);
        } else {
            // PEAR-like class name
            $logicalPathPsr0 = strtr($class, '_', DS) . EXT;
        }

        if (isset(self::$prefixesPsr0[$first])) {
            foreach (self::$prefixesPsr0[$first] as $prefix => $dirs) {
                if (0 === strpos($class, $prefix)) {
                    foreach ($dirs as $dir) {
                        if (is_file($file = $dir . DS . $logicalPathPsr0)) {
                            return $file;
                        }
                    }
                }
            }
        }

        // 查找 PSR-0 fallback dirs
        foreach (self::$fallbackDirsPsr0 as $dir) {
            if (is_file($file = $dir . DS . $logicalPathPsr0)) {
                return $file;
            }
        }

        // 找不到则设置映射为 false 并返回
        return self::$map[$class] = false;
    }

    
    /**
     * 注册自动加载
     * @access public
     * @param callable $autoload 自动加载处理方法
     * @return void
     */
    public static function register($autoload='')
    {
    	//注册自动加载
    	spl_autoload_register($autoload?: 'think\\Loader::autoload',true, true);


    	//注册命名空间
    	self::addNamespace([
    		'think'=>LIB_PATH.'think'.DS,
    		'behavior'=>LIB_PATH.'behavior'.DS,
    		'traits'=>LIB_PATH.'traits'.DS
    	]);

    	//加载类库映射文件
        if (is_file(RUNTIME_PATH.'classmap'.EXT)) {
            self::addClassMap(__include_file(RUNTIME_PATH.'classmap'.EXT));
        }

        //
    }

    /**
     * 注册命名空间
     * @access private
     * @param string|array $namespace
     * @param string       $path
     * @return void
     */
    private static function addNamespace($namespace, $path='')
    {
    	if (is_array($namespace)) {
    		
    		foreach ($namespace as $prefix => $paths) {
    			self::addPsr4($prefix.'\\', rtrim($paths, DS), true);
    		}
    	} else {

    		self::addPsr4($namespace.'\\', rtrim($paths, DS), true);
    	}
    }

    /**
     * 添加 PSR-4 空间
     * @access private
     * @param  array|string $prefix  空间前缀
     * @param  string       $paths   路径
     * @param  bool         $prepend 预先设置的优先级更高
     * @return void
     */
    private static function addPsr4($prefix, $paths, $prepend=false)
    {
        if (!$prefix) {
            
            self::$fallbackDirsPsr4 = $prepend?
            array_merge((array)$paths, self::$fallbackDirsPsr4) :
            array_merge(self::$fallbackDirsPsr4, (array)$paths);

        } elseif(!isset(self::$fallbackDirsPsr4[$prefix])) {

            $length = strlen($prefix);

            if ('\\' !== $prefix[$length-1]) {

                throw new \InvalidArgumentException(
                    "A non-empty PSR-4 prefix must end with a namespace separator."
                );
            }

            self::$prefixLengthsPsr4[$prefix[0]][$prefix] = $length;
            self::$prefixDirsPsr4[$prefix]              = (array)$paths;

        } else {

            self::$prefixDirsPsr4[$prefix] = $prepend?
            array_merge((array)$paths, self::$prefixDirsPsr4[$prefix]) :
            array_merge(self::$prefixDirsPsr4[$prefix], (array)$paths);
        }
    }


    /**
     * 注册 classmap
     * @access public
     * @param  string|array $class 类名
     * @param  string       $map   映射
     * @return void
     */
    public static function  addClassMap($class, $map='')
    {
        if (is_array($class)) {
            # code...
            self::$map = array_merge(self::$map, $class);
        } else {
            self::$map[$class] = $map;
        }
    }
}

//文件导入
function __include_file($file)
{
	return include $file;
}