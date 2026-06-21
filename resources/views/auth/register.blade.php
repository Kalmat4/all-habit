@extends('layouts.auth')

@section('title', 'Регистрация')

@section('content')
    <h4 class="mb-3 text-center">Регистрация</h4>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Имя</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}"
                   class="form-control @error('name') is-invalid @enderror"
                   autofocus required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="pin" class="form-label">Пин-код</label>
            <input type="password" id="pin" name="pin" inputmode="numeric"
                   class="form-control @error('pin') is-invalid @enderror"
                   required>
            @error('pin')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="pin_confirmation" class="form-label">Повторите пин-код</label>
            <input type="password" id="pin_confirmation" name="pin_confirmation" inputmode="numeric"
                   class="form-control" required>
        </div>

        <button type="submit" class="btn btn-dark w-100">Создать аккаунт</button>

        <div class="text-center mt-3">
            <small>Уже есть аккаунт? <a href="{{ route('login') }}">Войти</a></small>
        </div>
    </form>
@endsection
