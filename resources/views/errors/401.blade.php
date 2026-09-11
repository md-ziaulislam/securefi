<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>401 Unauthorized — SecuroFi.Tech</title>
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
                <span class="text-xs uppercase tracking-widest font-bold text-[#111111]">SecuroFi.Tech / Auth</span>
            </div>
            <span class="px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-300 text-[10px] font-bold uppercase">
                401 Unauthorized
            </span>
        </div>

        <!-- Error Illustration -->
        <div class="flex justify-center py-2">
            <div class="w-20 h-20 border-2 border-[#111111]/20 flex items-center justify-center relative">
                <svg class="w-10 h-10 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span class="absolute -top-2 -right-2 w-5 h-5 bg-rose-600 flex items-center justify-center">
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </span>
            </div>
        </div>

        <!-- Description -->
        <div class="space-y-3">
            <h1 class="text-2xl font-bold tracking-tight text-[#111111] font-heading">
                Authentication Required
            </h1>
            <p class="text-xs text-[#808080] leading-relaxed">
                {{ $exception->getMessage() ?: 'Valid authentication credentials are required to access this resource. Please sign in with an authorized account to proceed.' }}
            </p>
        </div>

        <!-- Diagnostic Block -->
        <div class="p-3.5 bg-[#F5F1E8]/60 border border-[#111111]/15 space-y-1.5 text-[11px]">
            <div class="flex justify-between">
                <span class="text-[#808080]">Session Status:</span>
                <span class="font-bold text-rose-600">Unauthenticated</span>
            </div>
            <div class="flex justify-between">
                <span class="text-[#808080]">Requested Path:</span>
                <span class="font-bold text-[#111111] truncate max-w-[200px]">{{ request()->getRequestUri() }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-[#808080]">Timestamp:</span>
                <span class="text-[#111111]">{{ now()->toIso8601String() }}</span>
            </div>
        </div>

        <!-- Return Actions -->
        <div class="flex items-center gap-3 pt-2">
            <a href="{{ route('home') }}"
               class="flex-1 text-center py-2.5 border border-[#111111] text-[#111111] text-xs uppercase font-bold tracking-wider hover:bg-[#F5F1E8] transition-colors">
                Return Home
            </a>
            <a href="{{ route('login') }}"
               class="flex-1 text-center py-2.5 bg-[#111111] text-white text-xs uppercase font-bold tracking-wider hover:bg-neutral-800 transition-colors">
                Sign In
            </a>
        </div>
    </div>

</body>
</html>
