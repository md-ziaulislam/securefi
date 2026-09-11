<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Verification — SecuroFi.Tech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {!! \App\Services\ThemeService::renderCssVariables() !!}
</head>
<body class="bg-[#F5F1E8] min-h-[100dvh] flex items-center justify-center p-6 antialiased">
    <div class="w-full max-w-md bg-white border border-[#111111]/20 shadow-sm p-8 fade-in-up">
        <div class="mb-6 border-b border-[#111111]/10 pb-6">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-3 h-3 bg-[#B38B6D]"></span>
                <span class="font-mono text-xs uppercase tracking-widest text-[#808080]">Security Verification</span>
            </div>
            <h1 class="text-2xl font-bold text-[#111111] tracking-tight">Two-Factor Code</h1>
            <p class="text-xs text-[#808080] mt-1 font-mono">Enter the 6-digit TOTP token from Google Authenticator.</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-3 bg-red-50 border border-red-300 text-red-800 text-xs font-mono">
                @foreach ($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('auth.2fa.verify') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label for="code" class="block text-xs font-mono uppercase tracking-wider text-[#111111] mb-2 font-medium">6-Digit Authenticator Token</label>
                <input type="text" id="code" name="code" required autofocus maxlength="6" pattern="[0-9]{6}"
                    class="w-full px-3.5 py-3 text-center tracking-[0.5em] text-2xl font-mono bg-white border border-[#111111]/30 focus:outline-none focus:border-[#111111] focus:ring-1 focus:ring-[#111111]"
                    placeholder="000000">
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-primary w-full text-xs font-mono uppercase tracking-wider py-3">
                    Verify & Access Admin →
                </button>
            </div>
        </form>

        <div class="mt-8 pt-4 border-t border-[#111111]/10 text-center">
            <a href="{{ route('login') }}" class="text-xs text-[#808080] hover:text-[#111111] font-mono transition-colors">
                ← Back to Login
            </a>
        </div>
    </div>
</body>
</html>
