<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Departments PDF</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <h2>Departments List</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Full Name</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($departments as $dept)
                <tr>
                    <td>{{ $dept->name }}</td>
                    <td>{{ $dept->fullname ?? '' }}</td>
                    <td>{{ $dept->description }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
