<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleSheetsService;

class FetchGoogleSheetsData extends Command
{
    protected $signature = 'google:fetch {count?}';
    protected $description = 'Получает данные из Google Sheets и выводит в консоль';

    public function handle(GoogleSheetsService $sheetsService)
    {
        $this->info('Загрузка данных из Google Sheets...');
        $data = $sheetsService->fetchFromGoogleSheets();

        $count = $this->argument('count') ?? count($data);
        $progressBar = $this->output->createProgressBar($count);
        
        foreach (array_slice($data, 0, $count) as $row) {
            $this->info("ID: {$row[0]} | Content: {$row[1]} | Status: {$row[2]}");
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\n Данные успешно загружены!");
    }
}
