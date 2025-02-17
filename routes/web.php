<?php

use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use App\Services\GoogleSheetsService;


Route::get('/', [RecordController::class, 'index'])->name('home');
Route::resource('records', RecordController::class);
Route::post('/records/generate', [RecordController::class, 'generate'])->name('records.generate');
Route::post('/records/clear', [RecordController::class, 'clear'])->name('records.clear');

Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
Route::post('/settings', [SettingsController::class, 'store'])->name('settings.store');



Route::get('/export', function (GoogleSheetsService $service) {
    $service->exportToGoogleSheets();
    return 'Данные экспортированы!';
});
    


Route::get('/fetch/{count?}', function ($count = null) {
    $output = Artisan::call('google:fetch', ['count' => $count]);
    return nl2br(Artisan::output());
});
