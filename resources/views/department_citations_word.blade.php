<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Department Citations</title>
</head>
<body>
    <h2>Department Citations</h2>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Full Name</th>
                <th>Unit</th>
                <th>Department</th>
                <th>Designation</th>
                <th>Kingschat</th>
                <th>Phone</th>
                <th>Citation</th>
                <th>Period</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($citations as $citation)
                <tr>
                    <td>{{ $citation->id }}</td>
                    <td>{{ $citation->title ?? 'N/A' }}</td>
                    <td>{{ $citation->fullname ?? 'N/A' }}</td>
                    <td>{{ $citation->unit ?? 'N/A' }}</td>
                    <td>{{ $citation->department ?? 'N/A' }}</td>
                    <td>{{ $citation->designation ?? 'N/A' }}</td>
                    <td>{{ $citation->kingschat ?? 'N/A' }}</td>
                    <td>{{ $citation->phone ?? 'N/A' }}</td>
                    <td>{{ $citation->citation ?? 'N/A' }}</td>
                    <td>{{ $citation->period ?? 'N/A' }}</td>
                    <td>{{ $citation->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
