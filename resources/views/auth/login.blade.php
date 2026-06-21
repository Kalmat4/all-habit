@extends('layouts.auth')

@section('title', 'Вход')

@section('content')
    <h4 class="mb-3 text-center">Вход</h4>

    <form method="POST" action="{{ route('login') }}">
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

        <div class="mb-3 form-check">
            <input type="checkbox" id="remember" name="remember" class="form-check-input">
            <label for="remember" class="form-check-label">Запомнить меня</label>
        </div>

        <button type="submit" class="btn btn-dark w-100">Войти</button>

        <div class="text-center mt-3">
            <small>Нет аккаунта? <a href="{{ route('register') }}">Зарегистрироваться</a></small>
        </div>
    </form>
@endsection
