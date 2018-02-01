<?php
namespace think;

use think\exception\ErrorException;

class Error
{
	/**
	 * 注册异常处理
	 * @access public
	 * @return void
	 */
	public static function register()
	{
		//设置错误级别
		error_reporting(E_ALL);

		//设置错误处理函数
		set_error_handler([__CLASS__, 'appError']);

		//设置异常处理函数
		set_exception_handler([__CLASS__, 'appException']);

		//设置php执行终止时执行的函数
		register_shutdown_function([__CLASS__, 'appShutdown']);
	}

	/**
	 * 错误处理函数
	 * @access public
	 * @param integer $errno      错误编号
	 * @param integer $errstr     详细错误信息
	 * @param string  $errfile    错误的文件
	 * @param integer $errline    出错的行号
	 * @param array   $errcontext 出错上下文
	 * @return void
	 * @throws ErrorException 
	 */
	public static function appError($errno, $errstr, $errfile='', $errline=0, $errcontext=[])
	{
		$exception = new ErrorException($errno, $errstr, $errfile, $errline, $errcontext);

        // 符合异常处理的则将错误信息托管至 think\exception\ErrorException
		if (error_reporting() & $error) {
			throw $exception;
		}

		self::getExtensionHandler()->report($exception);
	}

	/**
	 * 获取异常处理实例
	 * @access public
	 * @return Handle
	 */
	public static function getExtensionHandler()
	{
		static $handle;

		if (!$handle) {

			//异常处理handle
			$class = Config::get('exception_handle');

			if ($class && is_string($class) && class_exists($class) && is_subclass_of($class, "\\think\\exception\\Handle")) {

				$handle = new $class;
			} else {

				$handle = new Handle;
			}
		}

		return $handle;
	}
}