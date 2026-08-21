<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display a read-only paginated listing of system audit logs.
     */
    public function index(Request $request)
    {
        if (! $request->user()?->isAdmin()) {
            abort(403);
        }

        $query = AuditLog::with('user');

        if ($request->filled('action')) {
            $query->where('action', 'like', '%'.$request->input('action').'%');
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', 'like', '%'.$request->input('auditable_type').'%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get();

        return view('admin.audit-logs.index', compact('logs', 'users'));
    }
}
