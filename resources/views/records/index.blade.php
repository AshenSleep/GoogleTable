@extends('layouts.app')

@section('title', 'Records List')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Records</h1>
    <div>
        <!-- Кнопка для перехода на страницу настроек -->
        <a href="{{ url('/settings') }}" class="btn btn-info">Настройки</a>
        <!-- Кнопка для создания записи -->
        <a href="{{ route('records.create') }}" class="btn btn-primary">Создать запись</a>
        <!-- Форма для генерации 1000 строк -->
        <form action="{{ route('records.generate') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success">Сгенерировать 1000 строк</button>
        </form>
        <!-- Форма для очистки таблицы -->
        <form action="{{ route('records.clear') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-danger">Очистить таблицу</button>
        </form>
        <!-- Новая кнопка для экспорта данных через AJAX -->
        <button type="button" id="exportButton" class="btn btn-dark">Экспорт данных</button>
    </div>
</div>

<table class="table table-striped table-bordered">
    <thead class="table-dark">
        <tr>
            <th>ID</th>
            <th>Content</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($records as $record)
            <tr>
                <td>{{ $record->id }}</td>
                <td>{{ $record->content }}</td>
                <td>
                    <span class="badge {{ $record->status == 'Allowed' ? 'bg-success' : 'bg-danger' }}">
                        {{ $record->status }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('records.edit', $record) }}" class="btn btn-warning btn-sm">Редактировать</a>
                    <form action="{{ route('records.destroy', $record) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<!-- Скрипт для обработки нажатия кнопки экспорта -->
<script>
    document.getElementById('exportButton').addEventListener('click', function(){
        // Выполняем AJAX-запрос на маршрут /export
        fetch("{{ url('/export') }}")
            .then(response => response.text())
            .then(data => {
                // Выводим ответ в виде alert
                alert(data);
            })
            .catch(error => {
                console.error('Ошибка экспорта:', error);
                alert('Ошибка экспорта данных.');
            });
    });
</script>
@endsection
