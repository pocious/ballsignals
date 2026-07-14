<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your BallSignals VIP Subscription is Expiring</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f3f4f6; font-family: 'Helvetica Neue', Arial, sans-serif; color: #111827; }
        .wrapper { max-width: 580px; margin: 32px auto; }
        .card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.07); }
        .header { background: #0a0f1a; padding: 28px 32px; text-align: center; }
        .logo-text { font-size: 22px; font-weight: 900; color: #ffffff; letter-spacing: -0.5px; }
        .logo-text span { color: #4ade80; }
        .badge { display: inline-block; margin-top: 10px; background: rgba(234,179,8,0.15); border: 1px solid rgba(234,179,8,0.4); color: #fbbf24; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 4px 14px; border-radius: 999px; }
        .body { padding: 32px; }
        .greeting { font-size: 15px; color: #374151; line-height: 1.7; margin-bottom: 24px; }
        .alert-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; }
        .alert-box p { font-size: 14px; color: #92400e; line-height: 1.6; }
        .alert-box strong { color: #78350f; }
        .plan-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; }
        .plan-card .label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 6px; }
        .plan-card .value { font-size: 15px; font-weight: 700; color: #111827; }
        .plan-card .sub-value { font-size: 13px; color: #6b7280; margin-top: 2px; }
        .btn { display: block; text-align: center; background: #16a34a; color: #ffffff; text-decoration: none; padding: 14px 24px; border-radius: 10px; font-size: 15px; font-weight: 700; margin-bottom: 12px; }
        .btn-outline { display: block; text-align: center; background: transparent; color: #16a34a; text-decoration: none; padding: 12px 24px; border-radius: 10px; font-size: 14px; font-weight: 600; border: 1.5px solid #16a34a; margin-bottom: 24px; }
        .divider { border: none; border-top: 1px solid #f3f4f6; margin: 24px 0; }
        .footer { text-align: center; padding: 20px 32px 28px; }
        .footer p { font-size: 12px; color: #9ca3af; line-height: 1.7; }
        .footer a { color: #6b7280; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">

        <div class="header">
            <div class="logo-text">Ball<span>Signals</span></div>
            <div class="badge">⏳ Subscription Expiring</div>
        </div>

        <div class="body">
            <p class="greeting">
                Hi <strong>{{ $sub->name }}</strong>,<br><br>
                Your BallSignals VIP subscription is expiring in
                <strong>{{ $daysLeft }} day{{ $daysLeft > 1 ? 's' : '' }}</strong>.
                Renew now to keep uninterrupted access to our premium tips and daily accumulators.
            </p>

            <div class="alert-box">
                <p>
                    @if($daysLeft <= 1)
                        <strong>⚠️ Today is your last day!</strong> Your access expires tonight.
                        Renew immediately to avoid missing tomorrow's tips.
                    @else
                        <strong>Heads up!</strong> Your premium access expires on
                        <strong>{{ $sub->expires_at->format('l, d M Y') }}</strong>.
                        Renew before then to stay uninterrupted.
                    @endif
                </p>
            </div>

            <div class="plan-card">
                <div class="label">Current Plan</div>
                <div class="value">{{ $sub->plan_label }} — {{ $sub->plan_price }}</div>
                <div class="sub-value">Expires {{ $sub->expires_at->format('d M Y \a\t g:i A') }}</div>
            </div>

            <a href="{{ $renewUrl }}" class="btn">Renew My Subscription →</a>
            <a href="{{ config('app.url') }}/premium" class="btn-outline">Choose a Different Plan</a>

            <hr class="divider">

            <p style="font-size:13px;color:#6b7280;line-height:1.7;">
                As a VIP member you get access to our highest-confidence daily premium tips,
                accumulator slips, and early alerts — all backed by our data-driven ML prediction engine.
                Don't let your edge slip away.
            </p>
        </div>

        <div class="footer">
            <p>
                You're receiving this because you have an active subscription with
                <a href="{{ config('app.url') }}">BallSignals</a>.<br>
                Questions? Reply to this email or visit our
                <a href="{{ config('app.url') }}/contact">contact page</a>.
            </p>
        </div>

    </div>
</div>
</body>
</html>
