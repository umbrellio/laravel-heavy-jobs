# Laravel heavy-jobs

Пакет позволяет сохранять большой payload джобки в стороннем хранилище.  


## Установка

__Добавление пакета через composer__  

`composer require umbrellio/laravel-heavy-jobs`

__Миграция настроек пакета.__

`php artisan vendor:publish --tag heavy-jobs-config`

__База данных для хранения payload.__  
Если необходимо использовать БД, сначала нужно запустить команду для создания миграции.

`php artisan heavy-jobs:db-store-table`

После чего выполнить миграцию БД.

`php artisan migrate`


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
