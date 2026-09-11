<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriberController extends Controller
{
    public function index(Request $request)
    {
        $query = NewsletterSubscriber::latest('subscribed_at');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->search . '%')
                  ->orWhere('name', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereIn('status', ['active', 'subscribed']);
            } else {
                $query->where('status', $request->status);
            }
        }

        $subscribers = $query->paginate(25)->withQueryString();
        $total       = NewsletterSubscriber::count();
        $active      = NewsletterSubscriber::active()->count();
        $unsubscribed = NewsletterSubscriber::unsubscribed()->count();

        return view('admin.email.subscribers.index', compact('subscribers', 'total', 'active', 'unsubscribed'));
    }

    public function destroy(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();
        return back()->with('success', "Subscriber '{$subscriber->email}' deleted.");
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->withErrors(['ids' => 'No subscribers selected.']);
        }
        $count = NewsletterSubscriber::whereIn('id', $ids)->delete();
        return back()->with('success', "{$count} subscribers deleted.");
    }

    public function export(): StreamedResponse
    {
        $subscribers = NewsletterSubscriber::orderBy('subscribed_at', 'desc')->get();

        return response()->streamDownload(function () use ($subscribers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Name', 'Email', 'Status', 'Source', 'Subscribed At']);
            foreach ($subscribers as $sub) {
                fputcsv($handle, [
                    $sub->id,
                    $sub->name ?? '',
                    $sub->email,
                    $sub->status,
                    $sub->source ?? 'web',
                    $sub->subscribed_at?->format('Y-m-d H:i'),
                ]);
            }
            fclose($handle);
        }, 'subscribers-' . date('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
