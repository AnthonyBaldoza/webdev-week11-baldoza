<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Filter by JSONB</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 30px auto; padding: 0 15px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #bbb; padding: 6px 10px; text-align: left; }
        th { background: #f0f0f0; }
        pre { background: #f4f4f4; padding: 10px; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>Events with dress_code = {{ $code }}</h1>
    <p>Total matches (containment): {{ $containsCount }}. Showing up to 50 upcoming events.</p>

    <h3>Arrow form: toSql()</h3>
    <pre>{{ $arrowSql }}</pre>
    <pre>{{ $arrowRaw }}</pre>

    <h3>whereJsonContains: toSql()</h3>
    <pre>{{ $containsSql }}</pre>
    <pre>{{ $containsRaw }}</pre>

    <table>
        <tr><th>Title</th><th>Venue</th><th>Dress code</th></tr>
        @foreach ($events as $e)
            <tr><td>{{ $e->title }}</td><td>{{ $e->venue->name }}</td><td>{{ $e->details['dress_code'] }}</td></tr>
        @endforeach
    </table>
</body>
</html>