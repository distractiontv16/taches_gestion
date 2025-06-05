<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tâche non validée: {{ $task->title }}</title>
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
            background-color: #dc3545;
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
        .alert {
            background-color: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #ffeeba;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>RAPPEL URGENT: Tâche non validée</h2>
    </div>
    <div class="content">
        <div class="alert">
            <strong>⚠️ TÂCHE EN RETARD!</strong> Cette tâche a dépassé sa date d'échéance de plus de 30 minutes sans être validée.
        </div>

        <h3>{{ $task->title }}</h3>

        <p><strong>📅 Date d'échéance:</strong> {{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y à H:i') }}</p>
        <p><strong>⏰ Retard:</strong> {{ \Carbon\Carbon::parse($task->due_date)->diffForHumans() }}</p>
        <p><strong>🔥 Priorité:</strong> {{ ucfirst($task->priority) }}</p>

        @if($task->is_auto_generated && $task->routine)
            <p><strong>🔄 Tâche automatique:</strong> Générée par la routine "{{ $task->routine->title }}"</p>
        @endif
        
        @if($task->description)
            <p><strong>Description:</strong></p>
            <p>{{ $task->description }}</p>
        @endif
        
        <p>Veuillez valider cette tâche dès que possible ou mettre à jour son statut si elle est en cours de réalisation.</p>
        
        <a href="{{ url('/tasks/' . $task->id) }}" class="btn">Voir la tâche</a>
    </div>
    <div class="footer">
        <p>© {{ date('Y') }} Task Manager. Tous droits réservés.</p>
    </div>
</body>
</html> 