@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Настройки</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('settings.store') }}" method="POST">


        @csrf
        <div class="mb-3">
            <label for="spreadsheet_id" class="form-label">Google Spreadsheet ID:</label>
            <input type="text" id="spreadsheet_id" name="spreadsheet_id" class="form-control" 
                   value="{{ $setting->spreadsheet_id ?? '' }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Сохранить</button>
        <a href="{{ route('home') }}" class="btn btn-secondary">Назад</a>

    </form>
</div>
@endsection
