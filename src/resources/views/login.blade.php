@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="login-form__content">
    <div class="login-form__heading">
        <h1 class="login-form__title">ログイン</h1>
    </div>
    <form class="form" action="/login" method="post" novalidate>
        @csrf
        <div class="form__group">
            <div class="form__group-title">
                <h2 class="form__label--item">メールアドレス</h2>
            </div>
            <div class="form__group-content">
                <input class="form__input--text" type="email" name="email" value="{{ old('email') }}" />
                <div class="form__error">
                    @error('email')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>
        <div class="form__group">
            <div class="form__group-title">
                <h2 class="form__label--item">パスワード</>
            </div>
            <div class="form__group-content">
                <input class="form__input--text" type="password" name="password" />
                <div class="form__error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>
        <div class="form__button">
            <button class="form__button-submit" type="submit">ログインする</button>
        </div>
    </form>
    <div class="register__link">
        <a class="register__button-submit" href="/register">会員登録はこちら</a>
    </div>
</div>
@endsection