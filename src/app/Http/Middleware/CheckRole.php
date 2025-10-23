<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\WorkApplicationController;
// use Illuminate\Support\Facades\Auth;
// use App\Models\User;


class CheckRole
{
    public function handle($request, Closure $next, $role = null)
    {
        $user = auth()->user();
        // 別処理: /stamp_correction_request/list の場合
        if ($request->path()==='stamp_correction_request/list') {
            if ($user->role === 'admin') {
                $controller = app(WorkApplicationController::class);
                $response = $controller->adminIndex($request);
                // Response が null の場合に備えてラップ
                return $response instanceof \Illuminate\Http\Response
                    ? $response
                    : response($response);
            } elseif ($user->role === 'user') {
                $controller = app(WorkApplicationController::class);
                $response = $controller->userIndex($request);
                // Response が null の場合に備えてラップ
                return $response instanceof \Illuminate\Http\Response
                    ? $response
                    : response($response);
            }
            abort(403, 'Unauthorized');
        }
        // --- 通常の role チェック ---
        if ($role && $user->role !== $role) {
            abort(403, 'Unauthorized');
        }
        return $next($request);
    }

}
