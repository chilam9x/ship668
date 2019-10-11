        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="X-UA-Compatible" content="ie=edge">
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
            <title>Ngay Luôn</title>

        </head>

        <body>
            <div class='row'>
                @foreach($qrcode as $q)
                <div class='col'>
                    <img
                        src=" data:image/png;base64, {{ base64_encode(QrCode::format('png')->size(130)->generate($q)) }} ">
                    <span>{{$q}}</span>
                </div>
                @endforeach
            </div>
        </body>
        <style>
.row {
    width: 100%;
    height: 100%;
}

.col {
    display: inline-grid;
    border: 2px solid;
    margin: -2px;
}

span {
    width: 100%;
    text-align: center;
    font-size: 12;
    margin-bottom: 1em
}
        </style>
        <script type="text/javascript">
$(document).ready(function() {
    window.print();
});
        </script>

        </html>