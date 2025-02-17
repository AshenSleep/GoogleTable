<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ClearValuesRequest;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use App\Models\Record;
use GuzzleHttp\Client as GuzzleClient;

class GoogleSheetsService
{
    /**
     * @var Client Google API клиент.
     */
    protected Client $client;
    
    /**
     * @var Sheets Сервис для работы с Google Sheets API.
     */
    protected Sheets $service;
    
    /**
     * @var string Идентификатор Google таблицы (извлекается из настроек, введённых через интерфейс).
     */
    protected string $spreadsheetId;

    /**
     * Конструктор сервиса.
     *
     * Загружает настройки из базы, создаёт Google API клиент с указанием кастомного пути к SSL-сертификату,
     * и инициализирует сервис Google Sheets.
     *
     * @throws Exception Если в настройках не указан идентификатор таблицы.
     */
    public function __construct()
    {
        // Загружаем значение 'spreadsheet_id' из базы (пользователь вводит его через интерфейс)
        $storedValue = Setting::value('spreadsheet_id');
        if (empty($storedValue)) {
            throw new Exception("Не указан ID Google Sheets в настройках.");
        }
        // Если пользователь ввёл полный URL, извлекаем из него идентификатор таблицы.
        $this->spreadsheetId = $this->extractSpreadsheetId($storedValue);

        // Создаем Guzzle HTTP-клиент с указанием пути к SSL-сертификату на диске D:
        $guzzleClient = new GuzzleClient([
            'verify' => "D:\\xampp\\php\\extras\\ssl\\cacert.pem"
        ]);

        // Создаем Google API клиент и задаем ему путь к файлу учетных данных
        $this->client = new Client();
        $this->client->setAuthConfig(storage_path('credentials/google-sheets.json'));
        $this->client->addScope(Sheets::SPREADSHEETS);
        // Передаем кастомный Guzzle клиент для корректной работы SSL
        $this->client->setHttpClient($guzzleClient);

        // Инициализируем сервис Google Sheets
        $this->service = new Sheets($this->client);
    }

    /**
     * Извлекает идентификатор таблицы из введённого значения.
     *
     * Если пользователь ввёл полный URL вида:
     *   https://docs.google.com/spreadsheets/d/1ABCdEfGhIJkLMnO1234567890/edit#gid=0
     * метод вернет: 1ABCdEfGhIJkLMnO1234567890
     *
     * Если введён просто идентификатор, он возвращается без изменений.
     *
     * @param string $input URL или идентификатор таблицы.
     * @return string
     * @throws Exception Если не удаётся извлечь идентификатор из URL.
     */
    private function extractSpreadsheetId(string $input): string
    {
        // Если введен URL, пробуем извлечь идентификатор с помощью регулярного выражения.
        if (filter_var($input, FILTER_VALIDATE_URL)) {
            if (preg_match('/\\/d\\/([a-zA-Z0-9\\-_]+)/', $input, $matches)) {
                return $matches[1];
            } else {
                throw new Exception("Не удалось извлечь идентификатор таблицы из URL.");
            }
        }
        // Иначе считаем, что это уже идентификатор.
        return $input;
    }

    /**
     * Экспорт данных в Google Sheets.
     *
     * Реализует экспорт только записей со статусом Allowed (используется локальный скоуп в модели Record),
     * а также сохраняет комментарии из дополнительного столбца, чтобы они не затирались при обновлении.
     *
     * Логика работы:
     *  - Получаем записи из базы (только Allowed).
     *  - Получаем существующие данные из Google Sheets (диапазон A1:D1000), где столбец D хранит комментарии.
     *  - Формируем ассоциативный массив, сопоставляющий ID записи (приведенный к строке) с существующим комментарием.
     *  - Формируем новый массив данных с заголовками и для каждой записи: [ID, Content, Status, Comment].
     *  - Очищаем диапазон Google Sheets, чтобы удалить старые данные.
     *  - Обновляем Google Sheet, сохраняя комментарии.
     *
     * @param string $range Диапазон ячеек для обновления (по умолчанию 'A1').
     * @return bool
     * @throws Exception При ошибке обновления Google Sheets.
     */
    public function exportToGoogleSheets(string $range = 'A1'): bool
    {
        try {
            // Получаем записи со статусом Allowed (используем локальный скоуп allowed() в модели Record)
            $records = Record::allowed()->get(['id', 'content', 'status']);
            
            // Определяем заголовки (выводятся все поля модели + комментарий)
            $header = ['ID', 'Content', 'Status', 'Comment'];
            
            // Получаем текущие данные из Google Sheets, включая столбцы A-D.
            $existingData = $this->fetchFromGoogleSheets('A1:D1000');
            
            // Формируем ассоциативный массив с комментариями: [record_id (string) => comment]
            $existingComments = [];
            if (!empty($existingData) && count($existingData) > 1) {
                // Пропускаем первую строку с заголовками
                foreach (array_slice($existingData, 1) as $row) {
                    if (isset($row[0])) {
                        $recordId = (string)$row[0];
                        $existingComments[$recordId] = $row[3] ?? '';
                    }
                }
            }
            
            // Формируем новый массив данных для экспорта: заголовок + данные записей
            $newData = [];
            $newData[] = $header;
            foreach ($records as $record) {
                $id = (string)$record->id;
                // Сохраняем комментарий, если он существует, иначе оставляем пустую строку.
                $comment = $existingComments[$id] ?? '';
                $newData[] = [$id, $record->content, $record->status, $comment];
            }
            
            // Очищаем диапазон в Google Sheets (A1:Z1000), чтобы удалить старые данные.
            $clearRequest = new Sheets\ClearValuesRequest();
            $this->service->spreadsheets_values->clear($this->spreadsheetId, 'A1:Z1000', $clearRequest);
            
            // Готовим данные для обновления Google Sheets.
            $body = new Sheets\ValueRange([
                'values' => $newData
            ]);
            $params = ['valueInputOption' => 'RAW'];
            
            // Обновляем данные, начиная с A1.
            $this->service->spreadsheets_values->update(
                $this->spreadsheetId,
                $range,
                $body,
                $params
            );
            
            Log::info("Данные успешно экспортированы в Google Sheets.");
            return true;
        } catch (Exception $e) {
            Log::error("Ошибка при экспорте в Google Sheets: " . $e->getMessage());
            throw new Exception("Ошибка при экспорте в Google Sheets: " . $e->getMessage());
        }
    }

    /**
     * Получение данных из Google Sheets.
     *
     * Используется для получения существующих данных, чтобы сохранить комментарии при обновлении.
     *
     * @param string $range Диапазон для чтения (по умолчанию 'A1:Z1000').
     * @return array Двумерный массив значений из Google Sheets.
     * @throws Exception При ошибке получения данных.
     */
    public function fetchFromGoogleSheets(string $range = 'A1:Z1000'): array
    {
        try {
            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
            return $response->getValues() ?? [];
        } catch (Exception $e) {
            throw new Exception("Ошибка при получении данных из Google Sheets: " . $e->getMessage());
        }
    }
}
