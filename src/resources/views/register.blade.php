@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="register-form__content">
    <div class="register-form__heading">
        <h1 class="register-form__title">会員登録</h1>
    </div>
    <form class="form" action="/register" method="post" novalidate>
        @csrf
        <div class="form__group">
            <div class="form__group-title">
                <h2 class="form__label--item">ユーザー名</h2>
            </div>
            <div class="form__group-content">
                <input class="form__input--text" type="text" name="name" value="{{ old('name') }}" />
                <div class="form__error">
                    @error('name')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </div>
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
                <h2 class="form__label--item">パスワード</h2>
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
        <div class="form__group">
            <div class="form__group-title">
                <h2 class="form__label--item">確認用パスワード</h2>
            </div>
            <div class="form__group-content">
                <input class="form__input--text" type="password" name="password_confirmation" />
            </div>
        </div>
        <div class="form__button">
            <button class="form__button-submit" type="submit">登録する</button>
        </div>
    </form>
    <div class="login__link">
        <a class="login__button-submit" href="/login">ログインはこちら</a>
    </div>
</div>
@endsection