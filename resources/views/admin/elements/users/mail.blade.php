<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css"
          integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
</head>
<body>
<div class="row">
    <H1>
        HI {{$name}}!
    </H1>
</div>
<div class="row">
    @if(isset($update) && $update == true)
        <p><b>Your account has been updated</b></p>
    @else
        <p><b>Your account has been created</b></p>
    @endif
    <p><b>Username: {{$uuid}}</b></p><br/>
    <p><b>Password: {{$password}}</b></p><br/><br/>
    <i>thank you!</i>
</div>
</body>
</html>