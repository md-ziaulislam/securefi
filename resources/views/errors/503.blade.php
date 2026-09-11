<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 Maintenance Mode — SecuroFi.Tech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            background: #111111; color: #F8F8F6;
            min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 1.5rem; -webkit-font-smoothing: antialiased;
        }
        @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .spin-slow { animation: rotate 8s linear infinite; }
        @keyframes breathe {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }
        .breathe { animation: breathe 3s ease-in-out infinite; }
        @keyframes progress {
            0% { width: 0; }
            50% { width: 70%; }
            100% { width: 100%; }
        }
        .progress-bar {
            height: 2px;
            background: #22c55e;
            animation: progress 4s ease-in-out infinite;
        }
    </style>
</head>
<body>

    <div style="max-width:28rem; width:100%; border:1px solid rgba(248,248,246,.2); padding:2rem; background:rgba(248,248,246,.05); backdrop-filter:blur(10px);">
        <!-- Status Header -->
        <div style="display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid rgba(248,248,246,.15); padding-bottom:1rem; margin-bottom:1.5rem;">
            <div style="display:flex; align-items:center; gap:0.625rem;">
                <span class="breathe" style="width:0.75rem; height:0.75rem; background:#22c55e; display:inline-block; border-radius:50%;"></span>
                <span style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.1em; font-weight:700; color:#F8F8F6;">SecuroFi.Tech</span>
            </div>
            <span style="padding:0.125rem 0.5rem; background:rgba(34,197,94,.15); color:#22c55e; border:1px solid rgba(34,197,94,.3); font-size:10px; font-weight:700; text-transform:uppercase;">
                Maintenance
            </span>
        </div>

        <!-- Animated Gear -->
        <div style="display:flex; justify-content:center; padding:1rem 0 1.5rem;">
            <div style="position:relative;">
                <svg class="spin-slow" style="width:4rem; height:4rem; color:rgba(248,248,246,.3);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>

        <!-- Description -->
        <div style="margin-bottom:1.5rem; text-align:center;">
            <h1 style="font-size:1.5rem; font-weight:700; letter-spacing:-0.025em; color:#F8F8F6; margin-bottom:0.75rem; font-family:'Inter','Helvetica Neue',sans-serif;">
                Under Maintenance
            </h1>
            <p style="font-size:0.75rem; color:rgba(248,248,246,.5); line-height:1.75; max-width:22rem; margin:0 auto;">
                {{ $exception->getMessage() ?: 'SecuroFi.Tech is currently undergoing scheduled maintenance and platform upgrades. We are deploying critical updates to improve security, performance, and reliability.' }}
            </p>
        </div>

        <!-- Progress Indicator -->
        <div style="margin-bottom:1.5rem;">
            <div style="background:rgba(248,248,246,.1); height:2px; overflow:hidden;">
                <div class="progress-bar"></div>
            </div>
            <p style="font-size:10px; text-transform:uppercase; letter-spacing:0.1em; color:rgba(248,248,246,.35); margin-top:0.5rem; text-align:center;">
                Deploying updates...
            </p>
        </div>

        <!-- Status Info -->
        <div style="padding:0.875rem; border:1px solid rgba(248,248,246,.1); background:rgba(248,248,246,.03); margin-bottom:1.5rem;">
            <div style="display:flex; justify-content:space-between; font-size:11px; margin-bottom:0.375rem;">
                <span style="color:rgba(248,248,246,.4);">Platform:</span>
                <span style="font-weight:700; color:#F8F8F6;">SecuroFi.Tech</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:11px; margin-bottom:0.375rem;">
                <span style="color:rgba(248,248,246,.4);">Status:</span>
                <span style="font-weight:700; color:#22c55e;">Deploying</span>
            </div>
            <div style="display:flex; justify-content:space-between; font-size:11px;">
                <span style="color:rgba(248,248,246,.4);">Downtime Started:</span>
                <span style="color:#F8F8F6;">{{ now()->toIso8601String() }}</span>
            </div>
        </div>

        <!-- Action -->
        <div style="text-align:center;">
            <a href="javascript:location.reload()"
               style="display:inline-block; padding:0.625rem 2rem; border:1px solid rgba(248,248,246,.3); color:#F8F8F6; font-size:0.75rem; text-transform:uppercase; font-weight:700; letter-spacing:0.05em; text-decoration:none; transition:all 0.2s;"
               onmouseover="this.style.background='rgba(248,248,246,.1)'; this.style.borderColor='rgba(248,248,246,.5)'"
               onmouseout="this.style.background='transparent'; this.style.borderColor='rgba(248,248,246,.3)'">
                Check Again
            </a>
        </div>
    </div>

</body>
</html>
