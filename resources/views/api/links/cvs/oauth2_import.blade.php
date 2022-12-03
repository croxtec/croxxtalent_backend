<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oauth2 - Croxx Talent</title>
    <link rel="shortcut icon" href="{{ mix('images/logoicon.png') }}" type="image/x-icon">
    
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans&display=swap" rel="stylesheet"> 
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@500&display=swap" rel="stylesheet"> 
    <link href="https://fonts.googleapis.com/css2?family=Bree+Serif&display=swap" rel="stylesheet"> 
    <link href="https://fonts.googleapis.com/css2?family=Changa&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <style>
        body{
            font-family: 'Nunito Sans', sans-serif;
        }
        #header {
            margin-top: 10vh;
        }
    </style>
</head>
<body>

    <div class="container">
        <section id="header">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <a href="{{ app_url() }}">
                        <img src="{{ mix('images/logo.png') }}" alt="Croxx Talent" width="200">
                    </a>
                </div>
            </div>
        </section>
        <section id="content">
            <div class="row">
                <div class="col-lg-6 offset-lg-3">
                    <hr />
                    @if($data_retrieved)
                        <div class="alert alert-success">
                            <h5>Connected.</h5>
                        </div>
                    @else
                        <div class="alert alert-danger">
                            @if ($error_message)
                                <h5>{{ $error_message }}</h5>
                            @else
                                <h5>Could not connect to the Oauth Provider.</h5>
                            @endif                            
                        </div>
                    @endif

                    <div class="mt-6 text-center">
                        This window will close automatically in few seconds. 
                        <br><br>
                        <button class="btn btn-secondary" onclick="closeWindow()">Close Now</button>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script src="{{ mix('js/app.js') }}"></script>
    <script type="text/javascript">
        function closeWindow() {
            window.close();
        }
        // auto close after 10 secs
        setInterval(() => {
            closeWindow();
        }, 10000);
    </script>
</body>
</html>