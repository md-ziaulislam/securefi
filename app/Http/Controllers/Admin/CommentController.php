<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $search = $request->query('q');

        $query = Comment::with(['article', 'user', 'parent'])->latest('id');

        if ($status !== 'all' && in_array($status, ['pending', 'approved', 'spam', 'rejected'])) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $comments = $query->paginate(20)->withQueryString();

        // Counter badges
        $totalCount = Comment::count();
        $pendingCount = Comment::where('status', 'pending')->count();
        $approvedCount = Comment::where('status', 'approved')->count();
        $spamCount = Comment::where('status', 'spam')->count();

        return view('admin.comments.index', compact(
            'comments',
            'status',
            'search',
            'totalCount',
            'pendingCount',
            'approvedCount',
            'spamCount'
        ));
    }

    public function approve(Comment $comment)
    {
        $comment->update(['status' => 'approved']);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($comment)
            ->log("Approved comment #{$comment->id} by {$comment->name}");

        return back()->with('success', "Comment by '{$comment->name}' has been approved.");
    }

    public function reject(Comment $comment)
    {
        $comment->update(['status' => 'rejected']);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($comment)
            ->log("Rejected comment #{$comment->id} by {$comment->name}");

        return back()->with('success', "Comment has been marked as rejected.");
    }

    public function markSpam(Comment $comment)
    {
        $comment->update(['status' => 'spam']);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($comment)
            ->log("Flagged comment #{$comment->id} as spam");

        return back()->with('success', "Comment marked as spam.");
    }

    public function destroy(Comment $comment)
    {
        $authorName = $comment->name;
        $comment->delete();

        activity()
            ->causedBy(auth()->user())
            ->log("Deleted comment by {$authorName}");

        return back()->with('success', "Comment successfully deleted.");
    }

    public function reply(Request $request, Comment $comment)
    {
        $request->validate([
            'content' => 'required|string|min:2|max:2000',
        ]);

        $user = auth()->user();

        Comment::create([
            'article_id' => $comment->article_id,
            'user_id' => $user->id,
            'parent_id' => $comment->id,
            'name' => $user->name . ' (Staff)',
            'email' => $user->email,
            'content' => strip_tags(trim($request->input('content'))),
            'status' => 'approved',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_admin_reply' => true,
        ]);

        activity()
            ->causedBy($user)
            ->performedOn($comment)
            ->log("Posted official staff reply to comment #{$comment->id}");

        return back()->with('success', 'Staff reply published successfully.');
    }
}
