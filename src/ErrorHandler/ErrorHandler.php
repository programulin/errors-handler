<?php
namespace Programulin\ErrorHandler;

use Programulin\ErrorHandler\ErrorException;

class ErrorHandler
{
	protected $callback;
	protected $disallowed_levels = [];
	protected $throw_errors;

	public function register($callback)
	{
		$this->callback = $callback;

		set_error_handler([$this, 'errorsHandler']);
		set_exception_handler([$this, 'exceptionsHandler']);
		register_shutdown_function([$this, 'fatalsHandler']);

		return $this;
	}

	public function disallow(array $levels)
	{
		$this->disallowed_levels = $levels;

		return $this;
	}

	public function throwErrors($throw_errors)
	{
		$this->throw_errors = (bool) $throw_errors;
	}

	public function errorsHandler($errno, $message, $file, $line)
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

		$error = "$message in $file:$line";

		if($this->throw_errors)
			throw new ErrorException($error);
		else
			$this->runCallback($error);
	}

	public function exceptionsHandler($e)
	{
		if($e instanceof ErrorException)
			$message = $e->getMessage();
		else
			$message = "Exception: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}";

		$this->runCallback($message, $e);
	}

	public function fatalsHandler()
	{
		$error = error_get_last();

		if($error)
		{
			if(!$this->isHandlingAllowed($error['type']))
				return;

			$message = "Fatal: {$error['message']} in {$error['file']}:{$error['line']}";
			$this->runCallback($message);
		}
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

	protected function runCallback($message, $exception = null)
	{
		if($this->callback)
		{
			$callback = $this->callback;
			$callback($message, $exception);
		}
	}
}
