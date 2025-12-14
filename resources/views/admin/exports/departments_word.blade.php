<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Departments Word Export</title>
</head>
<body>
    <h2>Departments List</h2>
    <table border="1" cellpadding="5" cellspacing="0">
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
