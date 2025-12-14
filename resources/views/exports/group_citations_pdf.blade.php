<!DOCTYPE html>
<html>
<head>
    <title>Group Citations</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
    </style>
</head>
<body>
    <h2>Group Citations</h2>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Full Name</th>
                <th>Unit</th>
                <th>Designation</th>
                <th>Kingschat Handle</th>
                <th>Phone Number</th>
                <th>Group Name</th>
                <th>Period From</th>
                <th>Period To</th>
                <th>Citation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupCitations as $citation)
            <tr>
                <td>{{ $citation->title }}</td>
                <td>{{ $citation->name }}</td>
                <td>{{ $citation->unit }}</td>
                <td>{{ $citation->designation }}</td>
                <td>{{ $citation->kingschat }}</td>
                <td>{{ $citation->phone }}</td>
                <td>{{ $citation->group_name }}</td>
                <td>{{ $citation->period_from }}</td>
                <td>{{ $citation->period_to }}</td>
                <td>{{ $citation->description }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
