@extends('layouts.app')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/request_list.css') }}">
@endsection

@section('content')
<h1>申請一覧</h1>
<table class="table">
    <thead>
    <tr>
        <th>状態</th>
        <th>名前</th>
        <th>対象日時</th>
        <th>申請理由</th>
        <th>申請日時</th>
        <th>詳細</th>
    </tr>
    </thead>
    <tbody>
    @foreach($requests as $request)
    <tr>
        <td>{{ $request->status }}</td>
        <td>{{ $request->user->name }}</td>
        <td>{{ $request->target_date }}</td>
        <td>{{ $request->reason }}</td>
        <td>{{ $request->created_at->format('Y/m/d') }}</td>
        <td><a href="{{ route('request.show', $request->id) }}">詳細</a></td>
    </tr>
    @endforeach
    </tbody>
</table>
@endsection
