@extends('layouts.app')

@section('title', 'Редактировать запись')

@section('content')
<div class="card">
    <div class="card-body">
        <h2 class="card-title">Редактирование записи</h2>
        <form action="{{ route('records.update', $record) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Content:</label>
                <input type="text" name="content" value="{{ $record->content }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Status:</label>
                <select name="status" class="form-select">
                    <option value="Allowed" @if($record->status == 'Allowed') selected @endif>Allowed</option>
                    <option value="Prohibited" @if($record->status == 'Prohibited') selected @endif>Prohibited</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a href="{{ route('records.index') }}" class="btn btn-secondary">Назад</a>
        </form>
    </div>
</div>
@endsection
