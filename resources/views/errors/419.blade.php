<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 Session Expired — SecuroFi.Tech</title>
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
                <span class="w-3 h-3 bg-violet-600 inline-block"></span>
                <span class="text-xs uppercase tracking-widest font-bold text-[#111111]">SecuroFi.Tech / Session</span>
            </div>
            <span class="px-2 py-0.5 bg-violet-50 text-violet-700 border border-violet-300 text-[10px] font-bold uppercase">
                419 Expired
            </span>
        </div>

        <!-- Error Illustration -->
        <div class="flex justify-center py-2">
            <div class="w-20 h-20 border-2 border-[#111111]/20 flex items-center justify-center relative">
                <svg class="w-10 h-10 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="absolute -top-2 -right-2 w-5 h-5 bg-violet-600 flex items-center justify-center">
                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4m0 4h.01"/>
                    </svg>
                </span>
            </div>
        </div>

        <!-- Description -->
        <div class="space-y-3">
            <h1 class="text-2xl font-bold tracking-tight text-[#111111] font-heading">
                Session Expired
            </h1>
            <p class="text-xs text-[#808080] leading-relaxed">
                {{ $exception->getMessage() ?: 'Your session security token (CSRF) has expired due to inactivity. This is a protection mechanism against cross-site request forgery. Please refresh the page and try your action again.' }}
            </p>
        </div>

        <!-- Diagnostic Block -->
        <div class="p-3.5 bg-[#F5F1E8]/60 border border-[#111111]/15 space-y-1.5 text-[11px]">
            <div class="flex justify-between">
                <span class="text-[#808080]">Token Status:</span>
                <span class="font-bold text-violet-600">Expired / Invalid</span>
            </div>
            <div class="flex justify-between">
                <span class="text-[#808080]">Security Layer:</span>
                <span class="font-bold text-[#111111]">CSRF Protection</span>
            </div>
            <div class="flex justify-between">
                <span class="text-[#808080]">Timestamp:</span>
                <span class="text-[#111111]">{{ now()->toIso8601String() }}</span>
            </div>
        </div>

        <!-- Help Text -->
        <div class="p-3 bg-violet-50 border border-violet-200 text-[11px] text-violet-800 leading-relaxed">
            <strong>Quick Fix:</strong> Refresh this page or go back to the previous form. Your session will automatically renew with a fresh security token.
        </div>

        <!-- Return Actions -->
        <div class="flex items-center gap-3 pt-2">
            <a href="javascript:location.reload()"
               class="flex-1 text-center py-2.5 border border-[#111111] text-[#111111] text-xs uppercase font-bold tracking-wider hover:bg-[#F5F1E8] transition-colors">
                Refresh Page
            </a>
            <a href="javascript:history.back()"
               class="flex-1 text-center py-2.5 bg-[#111111] text-white text-xs uppercase font-bold tracking-wider hover:bg-neutral-800 transition-colors">
                Go Back
            </a>
        </div>
    </div>

</body>
</html>
