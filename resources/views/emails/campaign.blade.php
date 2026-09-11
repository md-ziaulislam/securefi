<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $emailSubject }}</title>
<style>
  body { margin:0; padding:0; background:#f5f5f5; font-family: 'Inter', Arial, sans-serif; color:#111111; }
  .wrapper { max-width:640px; margin:0 auto; background:#ffffff; }
  .header { background:#111111; padding:28px 40px; text-align:left; }
  .header-logo { color:#ffffff; font-size:18px; font-weight:700; letter-spacing:-0.5px; text-decoration:none; }
  .content { padding:40px; line-height:1.7; font-size:15px; color:#333; }
  .content h2 { font-size:22px; font-weight:700; color:#111111; margin-top:0; }
  .footer { background:#f8f8f6; border-top:1px solid #e5e5e5; padding:24px 40px; text-align:center; font-size:12px; color:#808080; }
  .footer a { color:#808080; text-decoration:underline; }
  a { color:#111111; }
  table { width:100%; border-collapse:collapse; }
  td, th { padding:10px 12px; border:1px solid #e5e5e5; text-align:left; font-size:14px; }
  th { background:#f8f8f6; font-weight:600; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <a href="{{ config('app.url') }}" class="header-logo">
      {{ \App\Models\Setting::get('site_name', 'SecuroFi.Tech') }}
    </a>
  </div>
  <div class="content">
    {!! $renderedBody !!}
  </div>
  <div class="footer">
    <p>
      @if(isset($subscriber) && $subscriber)
        You are receiving this email because you subscribed to
        <a href="{{ config('app.url') }}">{{ \App\Models\Setting::get('site_name', 'SecuroFi.Tech') }}</a>.
      @else
        This message was transmitted directly from
        <a href="{{ config('app.url') }}">{{ \App\Models\Setting::get('site_name', 'SecuroFi.Tech') }}</a>.
      @endif
    </p>
    <p>
      @if(isset($subscriber) && $subscriber)
        <a href="{{ $subscriber->unsubscribeUrl() }}">Unsubscribe</a> &nbsp;|&nbsp;
      @endif
      <a href="{{ config('app.url') }}">Visit Website</a>
    </p>
    <p>&copy; {{ date('Y') }} {{ \App\Models\Setting::get('site_name', 'SecuroFi.Tech') }}. All rights reserved.</p>
  </div>
</div>
</body>
</html>
