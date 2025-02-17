@extends('layouts.app')

@section('title', 'Создать запись')

@section('content')
<div class="card">
    <div class="card-body">
        <h2 class="card-title">Создание записи</h2>
        <form action="{{ route('records.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Content:</label>
                <input type="text" name="content" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Status:</label>
                <select name="status" class="form-select">
                    <option value="Allowed">Allowed</option>
                    <option value="Prohibited">Prohibited</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Создать</button>
            <a href="{{ route('records.index') }}" class="btn btn-secondary">Назад</a>
        </form>
    </div>
</div>
@endsection
