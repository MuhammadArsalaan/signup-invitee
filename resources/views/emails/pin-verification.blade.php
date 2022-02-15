<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <h1>{{$details['title']}}</h1>
    <p>{{$details['pin']}}</p>
    <br>
    <br>

    <a href="{{url('api/verifypin/'.$details['email'])}}">Verify</a>
    <br>
    Thanks
</body>
</html>