<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search events</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 30px auto; padding: 0 15px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #bbb; padding: 6px 10px; text-align: left; }
        th { background: #f0f0f0; }
        pre { background: #f4f4f4; padding: 10px; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>Search events</h1>
    <form method="GET" action="{{ route('events.search') }}">
        <input type="text" name="q" value="{{ $q }}" placeholder="e.g. telescope">
        <button type="submit">Search</button>
    </form>

    <h3>SQL: toSql()</h3>
    <pre>{{ $sql }}</pre>
    <pre>{{ $raw }}</pre>

    <p>{{ $events->count() }} result(s), max 50.</p>
    <table>
        <tr><th>Title</th><th>Venue</th><th>Description</th></tr>
        @foreach ($events as $e)
            <tr><td>{{ $e->title }}</td><td>{{ $e->venue->name }}</td><td>{{ $e->description }}</td></tr>
        @endforeach
    </table>
</body>
</html>