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
                <a class="header__link" href="/attendance">
                    <img class="header__logo" src="{{asset('img/logo.svg')}}" alt="ロゴ">
                </a>
                <ul class="header-nav">
                    <!-- 管理者ログイン時ログイン時 -->
                    @if(!is_null($user) && !is_null($user->email_verified_at) && $user->role === 'admin' )
                    <li class="header-nav__item">
                        <a class="header-nav__link" href="/attendance">勤怠</a>
                    </li>
                    <li class="header-nav__item">
                        <a class="header-nav__link" href="/admin/attendance/list">勤怠一覧</a>
                    </li>
                    <li class="header-nav__item">
                        <a class="header-nav__link" href="/stamp_correction_request/list">申請</a>
                    </li>
                    <li class="header-nav__item">
                        <form class="header-nav__logout" action="/logout" method="post">
                            @csrf
                            <button class="header-nav__logout--button">ログアウト</button>
                        </form>
                    </li>
                    <!-- 一般ユーザーログイン時 -->
                    @elseif(!is_null($user) && !is_null($user->email_verified_at) && $user->role === 'user')
                    <li class="header-nav__item">
                        <a class="header-nav__link" href="/attendance">勤怠</a>
                    </li>
                    <li class="header-nav__item">
                        <a class="header-nav__link" href="/attendance/list">勤怠一覧</a>
                    </li>
                    <li class="header-nav__item">
                        <a class="header-nav__link" href="/stamp_correction_request/list">申請</a>
                    </li>
                    <li class="header-nav__item">
                        <form class="header-nav__logout" action="/logout" method="post">
                            @csrf
                            <button class="header-nav__logout--button">ログアウト</button>
                        </form>
                    </li>
                    <!-- ログイン未承認ユーザー -->
                    @elseif(!is_null($user) && is_null($user->email_verified_at))
                    <li class="header-nav__item">
                        <form class="header-nav__logout" action="/logout" method="post">
                            @csrf
                            <button class="header-nav__logout--button">ログアウト</button>
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