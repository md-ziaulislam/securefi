<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found — SecuroFi.Tech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    {!! \App\Services\ThemeService::renderCssVariables() !!}
    <style>
        @keyframes glitch {
            0%, 100% { transform: translate(0); }
            20% { transform: translate(-2px, 2px); }
            40% { transform: translate(2px, -2px); }
            60% { transform: translate(-1px, -1px); }
            80% { transform: translate(1px, 1px); }
        }
        .glitch-text { animation: glitch 3s ease-in-out infinite; }
        @keyframes scan { 0% { top: -100%; } 100% { top: 100%; } }
        .scanline::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(transparent 50%, rgba(17,17,17,0.03) 50%);
            background-size: 100% 4px;
            pointer-events: none;
        }
    </style>
</head>
<body class="bg-[#F8F8F6] text-[#111111] antialiased min-h-screen flex items-center justify-center p-6 font-mono selection:bg-[#111111] selection:text-white">

    <div class="max-w-md w-full bg-white border border-[#111111] p-8 shadow-2xl space-y-6 relative scanline">
        <!-- Status Header -->
        <div class="flex items-center justify-between border-b border-[#111111]/15 pb-4">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 bg-[#111111] inline-block"></span>
                <span class="text-xs uppercase tracking-widest font-bold text-[#111111]">SecuroFi.Tech / Router</span>
            </div>
            <span class="px-2 py-0.5 bg-neutral-100 text-neutral-700 border border-neutral-300 text-[10px] font-bold uppercase">
                404 Not Found
            </span>
        </div>

        <!-- Large 404 Display -->
        <div class="flex justify-center py-4">
            <div class="relative">
                <span class="text-7xl font-black tracking-tighter text-[#111111]/10 glitch-text select-none">404</span>
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-12 h-12 text-[#111111]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="space-y-3">
            <h1 class="text-2xl font-bold tracking-tight text-[#111111] font-heading">
                Resource Not Found
            </h1>
            <p class="text-xs text-[#808080] leading-relaxed">
                {{ $exception->getMessage() ?: 'The requested URI could not be resolved to any known route, article, page, or resource within the SecuroFi.Tech platform. It may have been moved, deleted, or never existed.' }}
            </p>
        </div>

        <!-- Diagnostic Block -->
        <div class="p-3.5 bg-[#F5F1E8]/60 border border-[#111111]/15 space-y-1.5 text-[11px]">
            <div class="flex justify-between">
                <span class="text-[#808080]">Requested URI:</span>
                <span class="font-bold text-[#111111] truncate max-w-[200px]">{{ request()->getRequestUri() }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-[#808080]">Method:</span>
                <span class="font-bold text-[#111111]">{{ request()->method() }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-[#808080]">Referrer:</span>
                <span class="text-[#111111] truncate max-w-[200px]">{{ request()->headers->get('referer', 'Direct Access') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-[#808080]">Timestamp:</span>
                <span class="text-[#111111]">{{ now()->toIso8601String() }}</span>
            </div>
        </div>

        <!-- Suggestions -->
        <div class="border-t border-[#111111]/10 pt-4 space-y-2">
            <p class="text-[10px] uppercase tracking-widest text-[#808080] font-semibold">Try These Instead</p>
            <div class="grid grid-cols-3 gap-2 text-[11px]">
                <a href="{{ route('home') }}" class="text-center py-2 border border-[#111111]/15 hover:bg-[#F5F1E8] transition-colors text-[#111111]">Home</a>
                <a href="{{ route('search', ['q' => '']) }}" class="text-center py-2 border border-[#111111]/15 hover:bg-[#F5F1E8] transition-colors text-[#111111]">Search</a>
                <a href="{{ route('dev.info') }}" class="text-center py-2 border border-[#111111]/15 hover:bg-[#F5F1E8] transition-colors text-[#111111]">Dev Info</a>
            </div>
        </div>

        <!-- Return Actions -->
        <div class="flex items-center gap-3 pt-2">
            <a href="javascript:history.back()"
               class="flex-1 text-center py-2.5 border border-[#111111] text-[#111111] text-xs uppercase font-bold tracking-wider hover:bg-[#F5F1E8] transition-colors">
                Go Back
            </a>
            <a href="{{ route('home') }}"
               class="flex-1 text-center py-2.5 bg-[#111111] text-white text-xs uppercase font-bold tracking-wider hover:bg-neutral-800 transition-colors">
                Return Home
            </a>
        </div>
    </div>

</body>
</html>
