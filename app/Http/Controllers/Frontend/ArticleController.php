<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\ShortcodeParser;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function show(string $slug)
    {
        $article = Article::published()
            ->where('slug', $slug)
            ->with(['category', 'author', 'tags', 'seoMeta', 'approvedComments.replies'])
            ->firstOrFail();

        // Geo-fencing & Country Restriction Check (PRD-ADDNEW 5.1)
        $visitorCountry = \App\Services\GeoIpService::getCountryCode();
        if (!$article->isAccessibleInCountry($visitorCountry)) {
            $localizedVariant = $article->getVariantForCountry($visitorCountry);
            $allVariants = $article->allVariants();

            // Sanitize metadata so restricted content body is not leaked into OpenGraph / meta tags
            $restrictedMeta = new \App\Models\Page([
                'title'            => 'Restricted Publication — ' . $article->title,
                'meta_description' => $article->restriction_fallback_message ?: 'This content is not available in your region due to regulatory compliance.',
            ]);

            return response()->view('frontend.restricted', [
                'title'            => $article->title,
                'visitorCountry'   => $visitorCountry,
                'fallbackMessage'  => $article->restriction_fallback_message,
                'localizedVariant' => $localizedVariant,
                'allVariants'      => $allVariants,
                'seoModel'         => $restrictedMeta,
            ], 200);
        }

        // Increment view count
        $article->increment('view_count');

        // Parse content: inject headings anchors & build TOC
        $toc = [];
        $content = $article->content;

        $contentWithAnchors = preg_replace_callback('/<h2(.*?)>(.*?)<\/h2>/i', function ($match) use (&$toc) {
            $headingText = strip_tags($match[2]);
            $anchor = Str::slug($headingText);
            $toc[] = [
                'title' => $headingText,
                'anchor' => $anchor,
            ];
            return '<h2 id="'.$anchor.'" class="scroll-mt-24"'.$match[1].'>'.$match[2].'</h2>';
        }, $content);

        // Parse affiliate shortcodes
        $processedContent = ShortcodeParser::parse($contentWithAnchors);

        // Related Articles
        $relatedArticles = Article::published()
            ->where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->with(['category', 'author'])
            ->latest('published_at')
            ->take(3)
            ->get();

        $seoModel = $article;
        $currentCategory = $article->category;
        $currentCategoryId = $article->category_id;

        return view('frontend.article', compact(
            'article',
            'processedContent',
            'toc',
            'relatedArticles',
            'seoModel',
            'currentCategory',
            'currentCategoryId'
        ));
    }
}
