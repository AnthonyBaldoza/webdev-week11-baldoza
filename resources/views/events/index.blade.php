<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Events</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 30px auto; padding: 0 15px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #bbb; padding: 6px 10px; text-align: left; }
        th { background: #f0f0f0; }
        .count { background: #fff3cd; padding: 8px 12px; border: 1px solid #e0c97a; margin: 12px 0; }
    </style>
</head>
<body>
    <h1>Events ({{ $mode }} version)</h1>
    <div class="count">Queries sent: <strong>{{ $queryCount }}</strong></div>
    <table>
        <tr><th>Title</th><th>Venue</th><th>Participants</th></tr>
        @foreach ($rows as $row)
            <tr><td>{{ $row['title'] }}</td><td>{{ $row['venue'] }}</td><td>{{ $row['participants'] }}</td></tr>
        @endforeach
    </table>
</body>
</html>