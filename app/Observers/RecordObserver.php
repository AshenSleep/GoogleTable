<?php

namespace App\Observers;

use App\Models\Record;
use App\Services\GoogleSheetsService;
use Illuminate\Support\Facades\Log;

class RecordObserver
{
    /**
     * @var GoogleSheetsService Сервис для работы с Google Sheets.
     */
    protected GoogleSheetsService $sheetsService;

    /**
     * Конструктор Observer, внедрение зависимости GoogleSheetsService.
     *
     * @param GoogleSheetsService $sheetsService
     */
    public function __construct(GoogleSheetsService $sheetsService)
    {
        $this->sheetsService = $sheetsService;
    }

    /**
     * Обработчик события удаления записи.
     *
     * После удаления записи вызывается экспорт данных в Google Sheets,
     * что гарантирует удаление соответствующей строки в таблице.
     *
     * @param Record $record
     */
    public function deleted(Record $record)
    {
        Log::info("RecordObserver: Запись (ID: {$record->id}) удалена. Запускаем обновление Google Sheets.");

        try {
            // Вызываем метод экспорта для перезаписи данных в Google Sheets
            $this->sheetsService->exportToGoogleSheets();
            Log::info("RecordObserver: Google Sheets успешно обновлены после удаления записи.");
        } catch (\Exception $e) {
            Log::error("RecordObserver: Ошибка при обновлении Google Sheets: " . $e->getMessage());
        }
    }
}
