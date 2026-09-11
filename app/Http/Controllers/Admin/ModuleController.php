<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdSlot;
use App\Models\AffiliateProduct;
use App\Models\Page;
use App\Models\Setting;
use App\Models\ThemeSetting;
use App\Models\User;
use App\Models\VisitorLog;

class ModuleController extends Controller
{
    public function ads()
    {
        $adSlots = AdSlot::all();
        return view('admin.ads.index', compact('adSlots'));
    }

    public function affiliates()
    {
        $products = AffiliateProduct::all();
        return view('admin.affiliates.index', compact('products'));
    }

    public function seo()
    {
        $settings = Setting::where('group', 'seo')->pluck('value', 'key');
        return view('admin.seo.index', compact('settings'));
    }

    public function pages()
    {
        $pages = Page::all();
        return view('admin.pages.index', compact('pages'));
    }

    public function theme()
    {
        $tokens = ThemeSetting::getAllTokens();
        return view('admin.theme.index', compact('tokens'));
    }

    public function analytics()
    {
        $logs = VisitorLog::latest()->take(50)->get();
        return view('admin.analytics.index', compact('logs'));
    }

    public function users()
    {
        $users = User::with('roles')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function settings()
    {
        $settings = Setting::all()->groupBy('group');
        return view('admin.settings.index', compact('settings'));
    }
}
