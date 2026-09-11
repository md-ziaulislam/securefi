<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Setting;
use App\Services\CaptchaService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Store a reader comment on an article.
     */
    public function store(Request $request, string $slug, CaptchaService $captcha)
    {
        $article = Article::published()->where('slug', $slug)->firstOrFail();

        // Spam honeypot validation
        if ($request->filled('company_url')) {
            return redirect(route('article.show', $slug) . '#comments')->with('comment_success', 'Comment submitted successfully.');
        }

        // Captcha validation (if enabled for comment form)
        if ($captcha->isEnabledForForm('comment')) {
            $token = $request->input($captcha->tokenFieldName());
            if (!$captcha->verify($token, $request->ip())) {
                return redirect(route('article.show', $slug) . '#comment-form')
                    ->withInput()
                    ->withErrors(['captcha' => 'Security verification failed. Please complete the CAPTCHA and try again.']);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'website' => 'nullable|url|max:200',
            'content' => 'required|string|min:3|max:2000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $status = Setting::get('comment_moderation', '1') === '1' ? 'pending' : 'approved';
        $isAdminReply = false;
        $userId = null;

        if (auth()->check()) {
            $user = auth()->user();
            $userId = $user->id;
            if ($user->hasRole(['Super Admin', 'Admin', 'Editor', 'Author'])) {
                $status = 'approved';
                $isAdminReply = true;
            }
        }

        Comment::create([
            'article_id' => $article->id,
            'user_id' => $userId,
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => trim($validated['name']),
            'email' => trim($validated['email']),
            'website' => $validated['website'] ?? null,
            'content' => strip_tags(trim($validated['content'])),
            'status' => $status,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_admin_reply' => $isAdminReply,
        ]);

        $msg = $status === 'pending'
            ? 'Thank you! Your comment has been received and is awaiting moderation.'
            : 'Your comment has been posted successfully!';

        return redirect(route('article.show', $slug) . '#comments')->with('comment_success', $msg);
    }
}
