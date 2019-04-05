Простая библиотека обработки ошибок в PHP.
=====================

Ошибки отлавливаются с помощью функций:
+ set_error_handler
+ set_exception_handler
+ register_shutdown_function

Обработать их (записать в логи, вывести html-шаблон и т.п.) можно в callback-функции, которую нужно передать в метод register.

Установка
-----------------------------------

```
composer require programulin/errors-handler
```

Примеры
-----------------------------------

Базовое использование:

```php
use Programulin\ErrorHandler\ErrorHandler;

$handler = new ErrorHandler();

$handler->register(function($message, $exception) {
	var_dump($message);
	var_dump($exception);
});
```

$message содержит текст ошибки, $exception - исключение (или null), из которого вы можете получить trace.

Дополнительные методы:

```php
// Аналог ini_set('display_errors', 'off');
$handler->setDisplayErrors('off');

// Аналог error_reporting(E_ALL);
$handler->setErrorReporting(E_ALL);

// Перечисляем уровни ошибок, которые наш обработчик должен игнорировать
$handler->disallow([E_NOTICE, E_STRICT]);

// Превращает не фатальные ошибки в исключения
$handler->throwErrors(true);
```

Сокращённый вариант:

```php
(new ErrorHandler())->register(function($message, $exception) {
	var_dump($message);
	var_dump($exception);
})
	->setDisplayErrors('off')
	->setErrorReporting(E_ALL)
	->disallow([E_STRICT])
	->throwErrors(true);
```

Пример использования с библиотекой Monolog:

```php
require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Programulin\ErrorHandler\ErrorHandler;

$logger = new Logger('errors');
$logger->pushHandler(new StreamHandler('errors.log'));

(new ErrorHandler())->register(function($message, $exception) use ($logger) {
	$logger->error($message, [
		'exception' => $exception,
		'uri' => $_SERVER['REQUEST_URI']
	]);
})
	->setDisplayErrors('off')
	->setErrorReporting(E_ALL)
	->disallow([E_STRICT])
	->throwErrors(true);
```

Особенности
-----------------------------------

При установке throwErrors в true не фатальные ошибки превратятся в исключения (кроме вызванных с подавлением @, а также относящихся к уровням, переданным в метод disallow). Это значит, что после отлова ошибки дальнейшее выполнение скрипта не произойдёт:

```php
(new ErrorHandler())->register(function($message){
	var_dump($message);
})->throwErrors(true);

// Поскольку $title не определена, эта строка вызовет ошибку E_NOTICE, которая превратится в исключение
echo $title;

// Эта строка кода уже не будет выполнена
echo 'test';
```

Чтобы продолжить выполнение скрипта, можно использовать try-catch:

```php
(new ErrorHandler())->register(function($message){
	var_dump($message);
})->throwErrors(true);

try {
	echo $title;
} catch(\Exception $e) {
	echo 'Ошибка отловлена';
}

// Теперь эта строка будет выполнена
echo 'test';
```
