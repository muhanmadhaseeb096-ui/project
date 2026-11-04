<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Task Assigned</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 30px; }
        .card { max-width: 540px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; padding: 36px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .logo { font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 24px; }
        .logo span { color: #4f46e5; }
        h1 { font-size: 20px; font-weight: 700; color: #0f172a; margin-top: 0; }
        p { font-size: 14px; line-height: 1.6; color: #475569; }
        .button { display: inline-block; background-color: #4f46e5; color: #ffffff !important; text-decoration: none; padding: 12px 24px; border-radius: 10px; font-weight: 600; font-size: 14px; margin: 20px 0; }
        .meta-box { background-color: #f8fafc; border-radius: 12px; padding: 16px; margin: 16px 0; font-size: 13px; border: 1px solid #f1f5f9; }
        .footer { font-size: 12px; color: #94a3b8; margin-top: 24px; border-top: 1px solid #f1f5f9; pt: 16px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">Collab<span>Task</span></div>
        <h1>New Task Assigned to You</h1>
        <p><strong>{{ $creator?->name ?? 'A team member' }}</strong> has assigned you to a task in <strong>{{ $project->name }}</strong>.</p>

        <div class="meta-box">
            <div style="font-weight: 700; font-size: 15px; color: #0f172a; margin-bottom: 8px;">{{ $task->title }}</div>
            <div><strong>Priority:</strong> {{ $task->priority->label() }}</div>
            <div><strong>Status:</strong> {{ $task->status->label() }}</div>
            @if($task->due_date)
                <div><strong>Target Due Date:</strong> {{ $task->due_date->format('M d, Y') }}</div>
            @endif
        </div>

        <div style="text-align: center;">
            <a href="{{ $taskUrl }}" class="button">View Task on Board</a>
        </div>

        <p class="footer">You received this notification because you are a collaborator on {{ $project->name }}.</p>
    </div>
</body>
</html>

