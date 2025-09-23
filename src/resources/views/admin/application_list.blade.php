@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/application_list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="content__inner">
        <h1 class="ttl">申請一覧</h1>

        <div class="content-list__tabs">
            <a href="/stamp_correction_request/list?status=pending"
                class="content-list__tab {{ $status === 'pending' ? 'content-list__tab--active' : '' }}">
                承認待ち
            </a>
            <a href="/stamp_correction_request/list?status=approved"
                class="content-list__tab {{ $status === 'approved' ? 'content-list__tab--active' : '' }}">
                承認済み
            </a>
        </div>

        <table class="content-list__table">
            <thead>
                <tr>
                    <th class="content-list__header">状態</th>
                    <th class="content-list__header">名前</th>
                    <th class="content-list__header">対象日時</th>
                    <th class="content-list__header">申請理由</th>
                    <th class="content-list__header">申請日時</th>
                    <th class="content-list__header">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($applications as $app)
                <tr class="content-list__row">
                    <td class="content-list__data">{{ $app->approve_at ? '承認済み' : '承認待ち' }}</td>
                    <td class="content-list__data">{{ $app->work->user->name }}</td>
                    <td class="content-list__data">{{ \Carbon\Carbon::parse($app->work->work_date)->format('Y/m/d') }}</td>
                    <td class="content-list__data">{{ $app->reason }}</td>
                    <td class="content-list__data">{{ \Carbon\Carbon::parse($app->created_at)->format('Y/m/d') }}</td>
                    <td class="content-list__data">
                        <a href="{{ url('/stamp_correction_request/'.$app->id) }}" class="content-list__link">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
