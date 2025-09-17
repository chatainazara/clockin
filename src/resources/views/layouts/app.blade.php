<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClockInOut</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/common.css') }}">
    <!-- webフォントの追加 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <!-- webフォントの追加終わり -->
    @yield('css')
</head>

<body>
    @php
    $user = auth()->user();
    @endphp

    <header class="header">
        <div class="header__inner">
            <div class="header-utilities">
                <a class="header__link" href="/">
                    <img class="header__logo" src="{{asset('img/logo.svg')}}" alt="ロゴ">
                </a>
                <ul class="header-nav">
                    <!-- ログイン時 -->
                    @if(!is_null($user) && !is_null($user->email_verified_at))
                    <li class="header-nav__item-search">
                        <form class="header-nav__search" action='/' method='post'>
                            @csrf
                            <input class="header-nav__search--window" type="text" name="search" placeholder="何かお探しですか？" value="{{$search}}"/>
                            <button class="header-nav__search--button" type="submit">検索</button>
                        </form>
                    </li>
                    <li class="header-nav__item">
                        <a class="header-nav__link" href="/mypage">マイページ</a>
                    </li>
                    <li class="header-nav__item">
                        <form class="header-nav__logout" action="/logout" method="post">
                            @csrf
                            <button class="header-nav__logout--button">ログアウト</button>
                        </form>
                    </li>
                    <li class="header-nav__item">
                        <form class="header-nav__listing" action="/sell" method="get">
                            @csrf
                            <button class="header-nav__listing--button">出品</button>
                        </form>
                    </li>
                    <!-- ログイン未承認ユーザー -->
                    @elseif(!is_null($user) && is_null($user->email_verified_at))
                    <li class="header-nav__item">
                        <a class="header-nav__link" href="/mypage">マイページ</a>
                    </li>
                    <li class="header-nav__item">
                        <form class="header-nav__logout" action="/logout" method="post">
                            @csrf
                            <button class="header-nav__logout--button">ログアウト</button>
                        </form>
                    </li>
                    <li class="header-nav__item">
                        <form class="header-nav__listing" action="/sell" method="get">
                            @csrf
                            <button class="header-nav__listing--button">出品</button>
                        </form>
                    </li>
                    <!-- ログアウト時 -->
                    @elseif(auth()->guest())
                    <li class="header-nav__item">
                        <a class="header-nav__link" href="/login">マイページ</a>
                    </li>
                    <li class="header-nav__item">
                        <form class="header-nav__logout" action="/login" method="get">
                            @csrf
                            <button class="header-nav__logout--button">ログイン</button>
                        </form>
                    </li>
                    <li class="header-nav__item">
                        <form class="header-nav__listing" action="/login" method="get">
                            @csrf
                            <button class="header-nav__listing--button">出品</button>
                        </form>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>

</html>