<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Project Invitation</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 30px; }
        .card { max-width: 540px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; padding: 36px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .logo { font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 24px; }
        .logo span { color: #4f46e5; }
        h1 { font-size: 22px; font-weight: 700; color: #0f172a; margin-top: 0; }
        p { font-size: 14px; line-height: 1.6; color: #475569; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; background-color: #eef2ff; color: #4338ca; }
        .button { display: inline-block; background-color: #4f46e5; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 10px; font-weight: 600; font-size: 14px; margin: 20px 0; }
        .footer { font-size: 12px; color: #94a3b8; margin-top: 24px; border-top: 1px solid #f1f5f9; pt: 16px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">Collab<span>Task</span></div>
        <h1>You're invited to collaborate!</h1>
        <p><strong>{{ $inviter?->name ?? 'A teammate' }}</strong> has invited you to join the <strong>{{ $project->name }}</strong> project workspace as a <span class="badge">{{ $role->label() }}</span>.</p>

        @if($customMessage)
            <div style="background-color: #f8fafc; border-left: 3px solid #4f46e5; padding: 12px; margin: 16px 0; font-style: italic; color: #334155;">
                "{{ $customMessage }}"
            </div>
        @endif

        <p>Click the button below to review and accept your invitation:</p>
        <div style="text-align: center;">
            <a href="{{ $acceptUrl }}" class="button">Accept Invitation & Join Project</a>
        </div>

        <p class="footer">This invitation link will expire on {{ $expiresAt->format('M d, Y') }}. If you were not expecting this invitation, you can safely ignore this email.</p>
    </div>
</body>
</html>

