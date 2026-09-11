<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>429 Rate Limited — SecuroFi.Tech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    {!! \App\Services\ThemeService::renderCssVariables() !!}
    <style>
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(239,68,68,0.4); }
            70% { box-shadow: 0 0 0 12px rgba(239,68,68,0); }
            100% { box-shadow: 0 0 0 0 rgba(239,68,68,0); }
        }
        .pulse-warning { animation: pulse-ring 2s ease-out infinite; }
    </style>
</head>
<body class="bg-[#F8F8F6] text-[#111111] antialiased min-h-screen flex items-center justify-center p-6 font-mono selection:bg-[#111111] selection:text-white">

    <div class="max-w-md w-full bg-white border border-[#111111] p-8 shadow-2xl space-y-6">
        <!-- Status Header -->
        <div class="flex items-center justify-between border-b border-[#111111]/15 pb-4">
            <div class="flex items-center gap-2.5">
                <span class="w-3 h-3 bg-red-500 inline-block pulse-warning"></span>
                <span class="text-xs uppercase tracking-widest font-bold text-[#111111]">SecuroFi.Tech / Throttle</span>
            </div>
            <span class="px-2 py-0.5 bg-red-50 text-red-700 border border-red-300 text-[10px] font-bold uppercase">
                429 Rate Limited
            </span>
        </div>

        <!-- Error Illustration -->
        <div class="flex justify-center py-2">
            <div class="w-20 h-20 border-2 border-[#111111]/20 flex items-center justify-center relative">
                <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 flex items-center justify-center text-white text-[9px] font-bold">⚡</span>
            </div>
        </div>

        <!-- Description -->
        <div class="space-y-3">
            <h1 class="text-2xl font-bold tracking-tight text-[#111111] font-heading">
                Rate Limit Exceeded
            </h1>
            <p class="text-xs text-[#808080] leading-relaxed">
                {{ $exception->getMessage() ?: 'You have exceeded the maximum number of allowed requests within the configured time window. This throttle protects the platform from abuse and ensures fair resource allocation.' }}
            </p>
        </div>

        <!-- Diagnostic Block -->
        <div class="p-3.5 bg-[#F5F1E8]/60 border border-[#111111]/15 space-y-1.5 text-[11px]">
            <div class="flex justify-between">
                <span class="text-[#808080]">Status:</span>
                <span class="font-bold text-red-600">Throttled</span>
            </div>
            <div class="flex justify-between">
                <span class="text-[#808080]">Client IP:</span>
                <span class="font-bold text-[#111111]">{{ request()->ip() }}</span>
            </div>
            @if($retryAfter = request()->headers->get('Retry-After'))
            <div class="flex justify-between">
                <span class="text-[#808080]">Retry After:</span>
                <span class="font-bold text-[#111111]">{{ $retryAfter }} seconds</span>
            </div>
            @endif
            <div class="flex justify-between">
                <span class="text-[#808080]">Timestamp:</span>
                <span class="text-[#111111]">{{ now()->toIso8601String() }}</span>
            </div>
        </div>

        <!-- Countdown Timer -->
        <div class="p-3 bg-red-50 border border-red-200 text-[11px] text-red-800 leading-relaxed text-center">
            <strong>Please wait a moment</strong> before retrying your request. The rate limit will automatically reset.
        </div>

        <!-- Return Actions -->
        <div class="flex items-center gap-3 pt-2">
            <a href="javascript:location.reload()"
               class="flex-1 text-center py-2.5 border border-[#111111] text-[#111111] text-xs uppercase font-bold tracking-wider hover:bg-[#F5F1E8] transition-colors">
                Retry Now
            </a>
            <a href="{{ route('home') }}"
               class="flex-1 text-center py-2.5 bg-[#111111] text-white text-xs uppercase font-bold tracking-wider hover:bg-neutral-800 transition-colors">
                Return Home
            </a>
        </div>
    </div>

</body>
</html>
