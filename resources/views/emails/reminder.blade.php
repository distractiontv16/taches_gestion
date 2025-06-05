<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reminder: {{ $reminder->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4a6cf7;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            text-align: center;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Reminder</h2>
    </div>
    <div class="content">
        <h3>{{ $reminder->title }}</h3>
        
        <p><strong>Date:</strong> {{ $reminder->date }}</p>
        <p><strong>Time:</strong> {{ $reminder->time }}</p>
        
        @if($reminder->description)
            <p><strong>Description:</strong></p>
            <p>{{ $reminder->description }}</p>
        @endif
        
        <p>This is an automated reminder sent from your Task Manager application.</p>
    </div>
    <div class="footer">
        <p>© {{ date('Y') }} Task Manager. All rights reserved.</p>
    </div>
</body>
</html> 