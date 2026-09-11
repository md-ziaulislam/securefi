<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — SecuroFi.Tech Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {!! \App\Services\ThemeService::renderCssVariables() !!}
</head>
<body class="bg-[#F5F1E8] min-h-[100dvh] flex items-center justify-center p-6 antialiased">
    <div class="w-full max-w-md bg-white border border-[#111111]/20 shadow-sm p-8 fade-in-up">
        <div class="mb-8 border-b border-[#111111]/10 pb-6">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-3 h-3 bg-[#111111]"></span>
                <span class="font-mono text-xs uppercase tracking-widest text-[#808080]">Management Gateway</span>
            </div>
            <h1 class="text-2xl font-bold text-[#111111] tracking-tight">SecuroFi.Tech</h1>
            <p class="text-xs text-[#808080] mt-1 font-mono">Sign in to manage articles, monetization, and system telemetry.</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-3 bg-red-50 border border-red-300 text-red-800 text-xs font-mono">
                @foreach ($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('login.submit') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-2 font-medium">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-3.5 py-2.5 bg-white border border-[#111111]/30 text-sm focus:outline-none focus:border-[#111111] focus:ring-1 focus:ring-[#111111] font-mono transition-colors"
                    placeholder="admin@securofi.tech">
            </div>

            <div>
                <label for="password" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-2 font-medium">Password</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-3.5 py-2.5 bg-white border border-[#111111]/30 text-sm focus:outline-none focus:border-[#111111] focus:ring-1 focus:ring-[#111111] font-mono transition-colors"
                    placeholder="••••••••">
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded-none border-[#111111] text-[#111111] focus:ring-0">
                    <span class="text-xs text-[#808080] font-mono">Remember Session</span>
                </label>
                <span class="text-[11px] font-mono text-[#808080]">Protected by 2FA & Rate Limit</span>
            </div>

            <div class="pt-3">
                <button type="submit" class="btn-primary w-full text-xs font-mono uppercase tracking-wider py-3">
                    Authenticate Session →
                </button>
            </div>
        </form>

        <div class="mt-8 pt-4 border-t border-[#111111]/10 text-center">
            <a href="{{ route('home') }}" class="text-xs text-[#808080] hover:text-[#111111] font-mono transition-colors">
                ← Return to Public Site
            </a>
        </div>
    </div>
</body>
</html>
