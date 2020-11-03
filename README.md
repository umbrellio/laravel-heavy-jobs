# Laravel heavy-jobs

Пакет позволяет сохранять большой payload джобки в стороннем хранилище.  


## Установка

__Добавление пакета через composer__  

`composer require umbrellio/laravel-heavy-jobs`

__Миграция настроек пакета__

`php artisan vendor:publish --tag heavy-jobs-config`

__Проверка зависимостей__

Пакет работает только с редис-клиентом `php-redis`, соответственно нужно проверить что в `config/database.php` 
значится что-то вроде

```
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
```

## Documentation

Для того что бы изменить хранилище payload'a джобки, у неё необходимо указать интерфейс 
`Umbrellio\LaravelHeavyJobs\Jobs\ShouldStorePayload`

```
use Umbrellio\LaravelHeavyJobs\Jobs\ShouldStorePayload;
use Illuminate\Contracts\Queue\ShouldQueue;

class SimpleJob implements ShouldQueue, ShouldStorePayload 
{
    ...
}
``` 
