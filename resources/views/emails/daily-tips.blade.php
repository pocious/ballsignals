<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Today's Free Football Tips — BallSignals</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f3f4f6; font-family: 'Helvetica Neue', Arial, sans-serif; color: #111827; }
        .wrapper { max-width: 580px; margin: 32px auto; }
        .card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.07); }

        /* Header */
        .header { background: #0a0f1a; padding: 28px 32px; text-align: center; }
        .logo-icon { display: inline-flex; align-items: center; justify-content: center;
                     width: 44px; height: 44px; background: #22c55e; border-radius: 10px;
                     margin-bottom: 12px; }
        .logo-icon svg { width: 24px; height: 24px; }
        .logo-text { font-size: 22px; font-weight: 900; color: #ffffff; letter-spacing: -0.5px; }
        .logo-text span { color: #4ade80; }
        .date-badge { display: inline-block; margin-top: 8px; background: rgba(34,197,94,0.15);
                      border: 1px solid rgba(34,197,94,0.35); color: #4ade80;
                      font-size: 11px; font-weight: 700; text-transform: uppercase;
                      letter-spacing: 1px; padding: 4px 14px; border-radius: 999px; }

        /* Body */
        .body { padding: 28px 32px; }
        .greeting { font-size: 15px; color: #374151; line-height: 1.6; margin-bottom: 20px; }
        .greeting strong { color: #111827; }

        /* Section heading */
        .section-label { font-size: 10px; font-weight: 800; text-transform: uppercase;
                         letter-spacing: 1.2px; color: #6b7280; margin-bottom: 12px; }
        .divider { height: 1px; background: #f3f4f6; margin: 20px 0; }

        /* Tip card */
        .tip-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px;
                    padding: 14px 16px; margin-bottom: 10px; }
        .tip-teams { font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 4px; }
        .tip-teams .vs { font-weight: 400; color: #9ca3af; margin: 0 4px; }
        .tip-meta { font-size: 11px; color: #9ca3af; margin-bottom: 8px; }
        .tip-meta .dot { margin: 0 5px; }
        .tip-row { display: flex; align-items: center; gap: 8px; }
        .prediction-badge { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d;
                            font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 6px; }
        .odds-badge { background: #f9fafb; border: 1px solid #e5e7eb; color: #374151;
                      font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 6px; }
        .confidence { display: flex; gap: 2px; margin-left: auto; }
        .star-on  { color: #fbbf24; font-size: 12px; }
        .star-off { color: #d1d5db; font-size: 12px; }

        /* Empty state */
        .empty { text-align: center; padding: 32px; color: #9ca3af; font-size: 14px; }

        /* CTA */
        .cta-section { background: #0a0f1a; border-radius: 12px; padding: 20px 24px;
                       text-align: center; margin-top: 20px; }
        .cta-title { font-size: 15px; font-weight: 800; color: #ffffff; margin-bottom: 6px; }
        .cta-sub { font-size: 12px; color: #9ca3af; margin-bottom: 16px; }
        .cta-btn { display: inline-block; background: #22c55e; color: #000000;
                   font-size: 13px; font-weight: 900; padding: 12px 28px; border-radius: 10px;
                   text-decoration: none; letter-spacing: 0.2px; }
        .cta-btn:hover { background: #16a34a; }

        .telegram-btn { display: inline-block; background: #229ED9; color: #ffffff;
                        font-size: 13px; font-weight: 700; padding: 10px 24px;
                        border-radius: 10px; text-decoration: none; margin-top: 10px; }

        /* Footer */
        .footer { padding: 20px 32px; text-align: center; }
        .footer p { font-size: 11px; color: #9ca3af; line-height: 1.6; }
        .footer a { color: #22c55e; text-decoration: none; }
        .disclaimer { font-size: 10px; color: #d1d5db; margin-top: 8px; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">

        {{-- Header --}}
        <div class="header">
            <div class="logo-icon">
                <svg viewBox="0 0 24 24" fill="white">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.93V15h2v1.93c-1.31.09-2 .07-2 0zm4-1.07V14h-2v-2h4v1.86c-.64.37-1.32.69-2 1zm-6 0c-.68-.31-1.36-.63-2-1V12h4v2H9v1.86zM4.07 13H6v-2H4.07c.09-.69.24-1.36.46-2H6V7.14C7.06 6.43 8.24 6 9.5 6c.17 0 .34.01.5.03V9h5V6.03c.16-.02.33-.03.5-.03 1.26 0 2.44.43 3.5 1.14V9h1.47c.22.64.37 1.31.46 2H19v2h1.93c-.09.69-.24 1.36-.46 2H19v1.86c-1.06.71-2.24 1.14-3.5 1.14-.17 0-.34-.01-.5-.03V15H9v1.97c-.16.02-.33.03-.5.03-1.26 0-2.44-.43-3.5-1.14V15H3.53c-.22-.64-.37-1.31-.46-2z"/>
                </svg>
            </div>
            <div class="logo-text">Ball<span>Signals</span></div>
            <div class="date-badge">⚽ {{ now()->format('l, d M Y') }}</div>
        </div>

        {{-- Body --}}
        <div class="body">
            <p class="greeting">
                Hello! Here are today's <strong>free football tips</strong> from our analysts.
                Picks are updated daily — bookmark the site to never miss a tip.
            </p>

            <p class="section-label">Today's Free Tips</p>

            @if($tips->isEmpty())
                <div class="empty">
                    <p>No tips posted yet for today.</p>
                    <p style="margin-top:6px; font-size:12px;">Check back later or visit the site for updates.</p>
                </div>
            @else
                @foreach($tips as $tip)
                <div class="tip-card">
                    <div class="tip-teams">
                        {{ $tip->home_team }} <span class="vs">vs</span> {{ $tip->away_team }}
                    </div>
                    <div class="tip-meta">
                        {{ $tip->match_time->format('g:i A') }}
                        @if($tip->league)
                            <span class="dot">·</span>{{ $tip->league }}
                        @endif
                        @if($tip->country)
                            <span class="dot">·</span>{{ $tip->country }}
                        @endif
                    </div>
                    <div class="tip-row">
                        <span class="prediction-badge">{{ $tip->prediction }}</span>
                        @if($tip->odds)
                            <span class="odds-badge">@ {{ number_format($tip->odds, 2) }}</span>
                        @endif
                        @if($tip->confidence)
                            <div class="confidence">
                                @for($i = 1; $i <= 5; $i++)
                                    <span class="{{ $i <= $tip->confidence ? 'star-on' : 'star-off' }}">★</span>
                                @endfor
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            @endif

            <div class="divider"></div>

            {{-- Premium CTA --}}
            <div class="cta-section">
                <div class="cta-title">Want Premium Tips?</div>
                <div class="cta-sub">High-confidence picks, accumulators & VIP Telegram access from $15/week</div>
                <a href="{{ config('app.url') }}/premium" class="cta-btn">Go Premium →</a>
                <br>
                <a href="https://t.me/ballsigtips" class="telegram-btn">Join Our Telegram</a>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p>
                You're receiving this because you subscribed to daily tips at
                <a href="{{ config('app.url') }}">BallSignals</a>.
            </p>
            <p class="disclaimer">
                18+ · Betting tips are for informational purposes only. We do not guarantee winnings.
                Please gamble responsibly.
            </p>
        </div>

    </div>
</div>
</body>
</html>
