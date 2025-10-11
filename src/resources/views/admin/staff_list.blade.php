@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff_list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="content__inner">
        <h1 class="ttl">スタッフ一覧</h1>

        <table class="content-list__table">
            <thead class="content-list__table-inner">
                <tr class="content-list__row">
                    <th class="content-list__header">名前</th>
                    <th class="content-list__header">メールアドレス</th>
                    <th class="content-list__header">月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                @if($user->name != 'admin')
                <tr class="content-list__row">
                    <td class="content-list__data">{{ $user->name }}</td>
                    <td class="content-list__data">{{ $user->email }}</td>
                    <td class="content-list__data">
                        <a href="/admin/attendance/staff/{{$user->id}}" class="content-list__link">詳細</a>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
