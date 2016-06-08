<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

    <title>Socialpack Login</title>

    <!-- Bootstrap core CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <!-- <link href="starter-template.css" rel="stylesheet"> -->


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<nav class="navbar navbar-default">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">Socialpack With Facebook And Twitter</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse" style="float: right;">
            <ul class="nav navbar-nav">
                {{--<li class="active"><a href="#">Home</a></li>--}}
                {{--<li><a href="#about">About</a></li>--}}
                {{--<li><a href="" onclick="logout();">Logout</a></li>--}}
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>

<div class="container">


    <div class="page-header">
        <h1><i class="glyphicon glyphicon-plus"></i> Twitter Credentials </h1>
    </div>


    {{--@include('error')--}}
    <div class="row">
        <div class="col-md-12">
            {!! Form::open(array('url' => 'socialpacks/twitter', 'method' => 'POST')) !!}
            {{--<form action="{{ route('.store') }}" method="POST">--}}
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="form-group ">
                    <label for="title-field">Consumer Key</label>
                    <input type="text" id="title-field" name="twkey" class="form-control"/>

                </div>
                <div class="form-group">
                    <label for="details-field">Consumer Secret</label>
                    <input type="text" id="details-field" name="twsecret" class="form-control"/>

                </div>
                <div class="form-group ">
                    <label for="taskfor-field">Callback Url must be: http://yourdomain.com/callbackTwitter</label>
                    {{--<input type="text" id="taskfor-field" name="twcallback" class="form-control"/>--}}

                </div>
                <div class="well well-sm">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            {{--</form>--}}
            {{ Form::close() }}

        </div>
    </div>

    <div class="page-header">
        <h1><i class="glyphicon glyphicon-plus"></i> Facebook Credentials </h1>
    </div>

    <div class="row">
        <div class="col-md-12">
            {!! Form::open(array('url' => 'loginFacebook', 'method' => 'POST')) !!}
            {{--<form action="{{ route('.store') }}" method="POST">--}}
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <div class="form-group ">
                <label for="title-field">Application ID</label>
                <input type="text" id="title-field" name="fbkey" class="form-control"/>

            </div>
            <div class="form-group">
                <label for="details-field">App secret</label>
                <input type="text" id="details-field" name="fbsecret" class="form-control"/>

            </div>
            {{--<div class="form-group ">--}}
                {{--<label for="taskfor-field">Callback Url must be: http://yourdomain.com/callbacktw</label>--}}
                {{--<input type="text" id="taskfor-field" name="twcallback" class="form-control"/>--}}

            {{--</div>--}}
            <div class="well well-sm">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
            {{--</form>--}}
            {{ Form::close() }}

        </div>
    </div>
</div><!-- /.container -->

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<!-- <script src="../../assets/js/ie10-viewport-bug-workaround.js"></script> -->

</body>
</html>