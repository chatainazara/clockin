<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkApplication;

class WorkApplicationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $query = WorkApplication::with(['work.user'])
            ->orderBy('created_at', 'desc');

        if ($status === 'pending') {
            $query->whereNull('approve_at');
        } elseif ($status === 'approved') {
            $query->whereNotNull('approve_at');
        }

        $applications = $query->get();

        return view('admin.application_list', compact('applications', 'status'));
    }
}
