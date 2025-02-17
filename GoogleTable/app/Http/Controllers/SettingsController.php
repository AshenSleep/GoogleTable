<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Setting;

class SettingsController extends Controller
{
    /**
     * Отображение страницы настроек.
     */
    public function index()
    {
        $setting = Setting::first(); // Получаем первую запись (если она есть)
        return view('settings.index', compact('setting'));
    }

    /**
     * Сохранение ссылки на Google Таблицу.
     */
    public function store(Request $request)
    {
        $request->validate([
            'spreadsheet_id' => 'required|string',
        ]);

        $spreadsheetId = $request->input('spreadsheet_id');

        $setting = Setting::first();

        if ($setting) {
            $setting->update(['spreadsheet_id' => $spreadsheetId]);
        } else {
            Setting::create(['spreadsheet_id' => $spreadsheetId]);
        }

        return redirect()->route('settings.index')->with('success', 'Настройки сохранены.');
    }
}
