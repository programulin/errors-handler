<?php
namespace Programulin;

use Exception;

class ErrorHandler
{
	protected $callback;
	protected $disallowed_levels = [];

	public function register($callback)
	{
		$this->callback = $callback;

		set_error_handler([$this, 'errorToExceptionHandler']);
		set_exception_handler([$this, 'exceptionHandler']);
		register_shutdown_function([$this, 'fatalHandler']);
		
		return $this;
	}

	public function disallow(array $levels)
	{
		$this->disallowed_levels = $levels;

		return $this;
	}
	
	public function errorToExceptionHandler($errno, $message, $file, $line)
	{
		/*
			error_reporting() возвращает 0, если ошибка была вызвана с подавлением оператором @
			Не возвращаем false, чтобы ошибка не перенеслась в обработчик фатальных ошибок
		*/
		if(error_reporting() === 0)
			return;

		// Передаём ошибку стандартному обработчику PHP
		if(!$this->isHandlingAllowed($errno))
			return false;

		throw new Exception("$message in $file on line $line");
	}

	public function fatalHandler()
	{	
		$error = error_get_last();

		if($error)
		{
			if(!$this->isHandlingAllowed($error['type']))
				return;

			$message = "Fatal: {$error['message']} in {$error['file']} on line {$error['line']}";
			$this->runCallback($message);
		}
	}

	public function exceptionHandler($e)
	{
		$this->runCallback((string) $e);
	}

	public function setErrorReporting($level)
	{
		error_reporting($level);

		return $this;
	}

	public function setDisplayErrors($status)
	{
		ini_set('display_errors', $status);

		return $this;
	}

	protected function isHandlingAllowed($level)
	{
		return !in_array($level, $this->disallowed_levels);
	}
	
	protected function runCallback($message)
	{
		if($this->callback)
		{
			$callback = $this->callback;
			$callback($message);
		}
	}
}