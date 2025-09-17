@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify_email.css') }}">
@endsection

@section('content')
        <div class="content">
            <p class="content__text">
                登録していただいたメールアドレスに認証メールを送付しました。
            </br>メール認証を完了してください。
            </p>

                <a class="content__link" href="http://localhost:8025/">認証はこちらから</a>

            <form class="content__form" method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button class="content__form--button" type="submit">認証メールを再送する</button>
            </form>
        </div>
@endsection