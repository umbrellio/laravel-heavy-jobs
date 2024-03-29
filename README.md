# Laravel heavy-jobs

[![Github Status](https://github.com/umbrellio/laravel-heavy-jobs/workflows/CI/badge.svg)](https://github.com/umbrellio/laravel-heavy-jobs/actions)
[![Coverage Status](https://coveralls.io/repos/github/umbrellio/laravel-heavy-jobs/badge.svg?branch=master)](https://coveralls.io/github/umbrellio/laravel-heavy-jobs?branch=master)
[![Latest Stable Version](https://poser.pugx.org/umbrellio/laravel-heavy-jobs/v/stable.png)](https://packagist.org/packages/umbrellio/laravel-heavy-jobs)
[![Total Downloads](https://poser.pugx.org/umbrellio/laravel-heavy-jobs/downloads.png)](https://packagist.org/packages/umbrellio/laravel-heavy-jobs)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/umbrellio/laravel-heavy-jobs/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
[![Build Status](https://scrutinizer-ci.com/g/umbrellio/laravel-heavy-jobs/badges/build.png?b=master)](https://scrutinizer-ci.com/g/umbrellio/laravel-heavy-jobs/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/umbrellio/laravel-heavy-jobs/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/umbrellio/laravel-heavy-jobs/?branch=master)

Пакет позволяет сохранять большой payload джобки в стороннем хранилище.  


## Установка

__Добавление пакета через composer__  

`composer require umbrellio/laravel-heavy-jobs`

__Миграция настроек пакета__

`php artisan vendor:publish --tag heavy-jobs-config`

__Проверка зависимостей__

Пакет работает только с редис-клиентом `php-redis`, соответственно нужно проверить что в `config/database.php` значится что-то вроде

```php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
```

## Documentation

Для того что бы изменить хранилище payload'a джобки, у неё необходимо указать интерфейс `Umbrellio\LaravelHeavyJobs\Jobs\ShouldStorePayload`

```php
use Umbrellio\LaravelHeavyJobs\Jobs\ShouldStorePayload;
use Illuminate\Contracts\Queue\ShouldQueue;

class SimpleJob implements ShouldQueue, ShouldStorePayload 
{
    ...
}
``` 
