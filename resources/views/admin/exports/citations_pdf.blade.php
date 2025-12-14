<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Citations PDF</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 5px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h2>Citations Report</h2>
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Full Name</th>
                <th>Unit</th>
                <th>Group</th>
                <th>Designation</th>
                <th>Kingschat</th>
                <th>Phone</th>
                <th>Citation</th>
                <th>Period From</th>
                <th>Period To</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($citations as $citation)
                <tr>
                    <td>{{ $citation->title }}</td>
                    <td>{{ $citation->name }}</td>
                    <td>{{ $citation->unit }}</td>
                    <td>{{ $citation->group_name }}</td>
                    <td>{{ $citation->designation }}</td>
                    <td>{{ $citation->kingschat }}</td>
                    <td>{{ $citation->phone }}</td>
                    <td>{{ $citation->description }}</td>
                    <td>{{ $citation->period_from }}</td>
                    <td>{{ $citation->period_to }}</td>
                    <td>{{ $citation->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
