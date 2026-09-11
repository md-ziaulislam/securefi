<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden — SecuroFi.Tech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    {!! \App\Services\ThemeService::renderCssVariables() !!}
</head>
<body class="bg-[#F8F8F6] text-[#111111] antialiased min-h-screen flex items-center justify-center p-6 font-mono selection:bg-[#111111] selection:text-white">

    <div class="max-w-md w-full bg-white border border-[#111111] p-8 shadow-2xl space-y-6">
        <!-- Status Header -->
        <div class="flex items-center justify-between border-b border-[#111111]/15 pb-4">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 bg-rose-600 inline-block"></span>
                <span class="text-xs uppercase tracking-widest font-bold text-[#111111]">SecuroFi.Tech / RBAC</span>
            </div>
            <span class="px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-300 text-[10px] font-bold uppercase">
                403 Access Denied
            </span>
        </div>

        <!-- Description -->
        <div class="space-y-3">
            <h1 class="text-2xl font-bold tracking-tight text-[#111111] font-heading">
                Unauthorized Request
            </h1>
            <p class="text-xs text-[#808080] leading-relaxed">
                {{ $exception->getMessage() ?: 'Your authenticated account credentials lack the cryptographic privileges required to access or modify this control resource.' }}
            </p>
        </div>

        <!-- Diagnostic Block -->
        <div class="p-3.5 bg-[#F5F1E8]/60 border border-[#111111]/15 space-y-1.5 text-[11px]">
            <div class="flex justify-between">
                <span class="text-[#808080]">Authenticated:</span>
                <span class="font-bold text-[#111111]">{{ auth()->check() ? auth()->user()->name : 'Guest' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-[#808080]">Active Role:</span>
                <span class="font-bold text-[#111111]">{{ auth()->check() && auth()->user()->roles->count() ? auth()->user()->roles->pluck('name')->implode(', ') : 'None' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-[#808080]">Timestamp:</span>
                <span class="text-[#111111]">{{ now()->toIso8601String() }}</span>
            </div>
        </div>

        <!-- Return Actions -->
        <div class="flex items-center gap-3 pt-2">
            @auth
                @if(auth()->user()->hasAnyRole(['Super Admin', 'super-admin', 'Admin', 'Editor', 'Author']))
                    <a href="{{ route('admin.dashboard') }}"
                       class="flex-1 text-center py-2.5 bg-[#111111] text-white text-xs uppercase font-bold tracking-wider hover:bg-neutral-800 transition-colors">
                        Return to Dashboard
                    </a>
                @else
                    <a href="{{ route('home') }}"
                       class="flex-1 text-center py-2.5 bg-[#111111] text-white text-xs uppercase font-bold tracking-wider hover:bg-neutral-800 transition-colors">
                        Return to Home
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}"
                   class="flex-1 text-center py-2.5 bg-[#111111] text-white text-xs uppercase font-bold tracking-wider hover:bg-neutral-800 transition-colors">
                    Sign In
                </a>
            @endauth
        </div>
    </div>

</body>
</html>
