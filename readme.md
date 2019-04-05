Простой класс обработки ошибок в PHP.
=====================

Класс отлавливает ошибки с помощью функций:
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
use Programulin\ErrorHandler;

$handler = new ErrorHandler();

$callback = function($message){
	// Здесь можно записать ошибку в логи (например, с помощью Monolog) и вывести html-шаблон
	var_dump($message);
};

// Регистрируем наш обработчик ошибок
$handler->register($callback);
```

Сокращённый вариант:

```php
use Programulin\ErrorHandler;

(new ErrorHandler())->register(function($message){
	var_dump($message);
});
```

Дополнительные методы:

```php
// Аналог ini_set('display_errors', 'off');
$handler->setDisplayErrors('off');

// Аналог error_reporting(E_ALL);
$handler->setErrorReporting(E_ALL);

// Перечисляем уровни ошибок, которые наш обработчик должен игнорировать
$handler->disallow([E_NOTICE, E_STRICT]);
```

Сокращённый вариант:

```php
use Programulin\ErrorHandler;

(new ErrorHandler())->register(function($message){
	var_dump($message);
})
	->setDisplayErrors('off')
	->setErrorReporting(E_ALL)
	->disallow([E_NOTICE, E_STRICT]);
```

Особенности
-----------------------------------

Не фатальные ошибки (кроме вызванных с подавлением @, а также относящихся к уровням, переданным в метод disallow) превращаются в исключения. Это значит, что после отлова ошибки дальнейшее выполнение скрипта не происходит:

```php
use Programulin\ErrorHandler;

(new ErrorHandler())->register(function($message){
	var_dump($message);
});

// Поскольку $title не определена, эта строка вызовет ошибку E_NOTICE, которая превратится в исключение
echo $title;

// Эта строка кода уже не будет выполнена
echo 'test';
```

Чтобы продолжить выполнение скрипта, передайте E_NOTICE в метод disallow, либо используйте try-catch:

```php
use Programulin\ErrorHandler;

(new ErrorHandler())->register(function($message){
	var_dump($message);
});

try {
	echo $title;
} catch(\Exception $e) {
	echo 'Ошибка отловлена';
}

// Теперь эта строка будет выполнена
echo 'test';
```