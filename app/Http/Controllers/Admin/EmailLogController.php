<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    public function index(Request $request)
    {
        $query = EmailLog::with('sender')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('recipient_email', 'like', "%{$search}%")
                  ->orWhere('recipient_name', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $logs        = $query->paginate(30)->withQueryString();
        $totalLogs   = EmailLog::count();
        $sentCount   = EmailLog::where('status', 'sent')->count();
        $failedCount = EmailLog::where('status', 'failed')->count();

        return view('admin.email.logs.index', compact(
            'logs',
            'totalLogs',
            'sentCount',
            'failedCount'
        ));
    }

    public function destroy(EmailLog $log)
    {
        $log->delete();
        return back()->with('success', 'Log entry deleted.');
    }

    public function clear(Request $request)
    {
        $type = $request->input('clear_type', 'all');
        if ($type === 'failed') {
            $count = EmailLog::where('status', 'failed')->delete();
            return back()->with('success', "Cleared {$count} failed delivery log(s).");
        }

        $count = EmailLog::count();
        EmailLog::truncate();
        return back()->with('success', "Cleared all {$count} delivery log(s).");
    }
}
