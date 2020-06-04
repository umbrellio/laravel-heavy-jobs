# Laravel heavy-jobs

Пакет позволяет сохранять большой payload джобки в стороннем хранилище.  


## Установка

__Добавление пакета через composer__  

`composer require umbrellio/laravel-heavy-jobs`

__Миграция настроек пакета.__

`php artisan vendor:publish --tag heavy-jobs-config`

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
