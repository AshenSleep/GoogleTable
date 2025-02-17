<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleSheetsService;

class ExportToGoogleSheets extends Command
{
    protected $signature = 'export:google-sheets';
    protected $description = 'Экспорт данных в Google Sheets';

    public function handle(GoogleSheetsService $sheetsService)
    {
        $this->info('⏳ Экспорт данных в Google Sheets...');

        try {
            $sheetsService->exportToGoogleSheets();
            $this->info(' Данные успешно выгружены!');
        } catch (\Exception $e) {
            $this->error(' Ошибка при экспорте: ' . $e->getMessage());
        }
    }
}
