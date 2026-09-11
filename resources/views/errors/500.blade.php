<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 Server Error — SecuroFi.Tech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* Inline critical styles in case Vite is unavailable during 500 */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            background: #F8F8F6; color: #111111;
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 1.5rem; -webkit-font-smoothing: antialiased;
        }
        @keyframes flicker {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.85; }
            75% { opacity: 0.95; }
        }
        .flicker { animation: flicker 4s ease-in-out infinite; }
        @keyframes crash-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        .crash-card { animation: crash-pulse 6s ease-in-out infinite; }
    </style>
</head>
<body>

    <div class="crash-card" style="max-width:28rem; width:100%; background:white; border:1px solid #111111; padding:2rem; box-shadow:0 25px 50px -12px rgba(0,0,0,.25);">
        <!-- Status Header -->
        <div style="display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid rgba(17,17,17,.15); padding-bottom:1rem; margin-bottom:1.5rem;">
            <div style="display:flex; align-items:center; gap:0.625rem;">
                <span style="width:0.75rem; height:0.75rem; background:#dc2626; display:inline-block;"></span>
                <span style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.1em; font-weight:700; color:#111111;">SecuroFi.Tech / System</span>
            </div>
            <span style="padding:0.125rem 0.5rem; background:#fef2f2; color:#b91c1c; border:1px solid #fca5a5; font-size:10px; font-weight:700; text-transform:uppercase;">
                500 Fatal
            </span>
        </div>

        <!-- Error Illustration -->
        <div style="display:flex; justify-content:center; padding:0.5rem 0 1rem;" class="flicker">
            <div style="width:5rem; height:5rem; border:2px solid rgba(17,17,17,.2); display:flex; align-items:center; justify-content:center; position:relative;">
                <svg style="width:2.5rem; height:2.5rem; color:#dc2626;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span style="position:absolute; top:-0.5rem; right:-0.5rem; width:1.25rem; height:1.25rem; background:#dc2626; display:flex; align-items:center; justify-content:center;">
                    <svg style="width:0.75rem; height:0.75rem; color:white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </span>
            </div>
        </div>

        <!-- Description -->
        <div style="margin-bottom:1.5rem;">
            <h1 style="font-size:1.5rem; font-weight:700; letter-spacing:-0.025em; color:#111111; margin-bottom:0.75rem; font-family:'Inter','Helvetica Neue',sans-serif;">
                Internal Server Error
            </h1>
            <p style="font-size:0.75rem; color:#808080; line-height:1.75;">
                A critical server-side exception occurred while processing your request. The engineering team has been notified. This incident has been logged for analysis and resolution.
            </p>
        </div>

        <!-- Diagnostic Block -->
        <div style="padding:0.875rem; background:rgba(245,241,232,.6); border:1px solid rgba(17,17,17,.15); margin-bottom:1.5rem;">
            <div style="display:flex; justify-content:space-between; font-size:11px; margin-bottom:0.375rem;">
                <span style="color:#808080;">Status Code:</span>
                <span style="font-weight:700; color:#dc2626;">500 — Internal Server Error</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:11px; margin-bottom:0.375rem;">
                <span style="color:#808080;">Environment:</span>
                <span style="font-weight:700; color:#111111;">{{ app()->environment() }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:11px; margin-bottom:0.375rem;">
                <span style="color:#808080;">PHP Version:</span>
                <span style="font-weight:700; color:#111111;">{{ phpversion() }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:11px;">
                <span style="color:#808080;">Timestamp:</span>
                <span style="color:#111111;">{{ now()->toIso8601String() }}</span>
            </div>
        </div>

        <!-- Return Actions -->
        <div style="display:flex; gap:0.75rem; padding-top:0.5rem;">
            <a href="javascript:location.reload()"
               style="flex:1; text-align:center; padding:0.625rem; border:1px solid #111111; color:#111111; font-size:0.75rem; text-transform:uppercase; font-weight:700; letter-spacing:0.05em; text-decoration:none; transition:background 0.2s;"
               onmouseover="this.style.background='#F5F1E8'" onmouseout="this.style.background='transparent'">
                Retry
            </a>
            <a href="/"
               style="flex:1; text-align:center; padding:0.625rem; background:#111111; color:white; font-size:0.75rem; text-transform:uppercase; font-weight:700; letter-spacing:0.05em; text-decoration:none; transition:background 0.2s;"
               onmouseover="this.style.background='#333'" onmouseout="this.style.background='#111111'">
                Return Home
            </a>
        </div>
    </div>

</body>
</html>
