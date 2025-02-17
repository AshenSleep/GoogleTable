<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Record;
use App\Observers\RecordObserver;
use App\Services\GoogleSheetsService;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Получаем экземпляр сервиса GoogleSheetsService из контейнера
        $sheetsService = app(GoogleSheetsService::class);
        // Регистрируем Observer для модели Record
        Record::observe(new RecordObserver($sheetsService));
    }
}
