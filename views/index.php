<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Home</title>
</head>

<body>
    Home Page <br>
    <form action="/transactions" method="post" enctype="multipart/form-data">
        file: <input type="file" name="csv_file" accept=".csv,.xlsx">
        <button type="submit">Submit</button>
    </form>
</body>

</html>