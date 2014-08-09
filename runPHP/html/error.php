<!DOCTYPE html>
<html>
<head>
    <title><?php _e('Error', 'system') ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link type="text/css" rel="stylesheet" href="/runPHP/html/error.css"/>
    <style>
        @font-face {
            font-family: 'SegoeUINormal';
            src: url('/webapps/taosmi.es/static/fonts/segoe-ui-72888-webfont.eot');
            src: url('/webapps/taosmi.es/static/fonts/segoe-ui-72888-webfont.eot?#iefix') format('embedded-opentype'),
            url('/webapps/taosmi.es/static/fonts/segoe-ui-72888-webfont.woff') format('woff'),
            url('/webapps/taosmi.es/static/fonts/segoe-ui-72888-webfont.ttf') format('truetype'),
            url('/webapps/taosmi.es/static/fonts/segoe-ui-72888-webfont.svg#SegoeUINormal') format('svg');
            font-weight: normal;
            font-style: normal;
        }

        .error {
            background: url('/runPHP/html/error.png') no-repeat;
            color: #999;
            font-family: SegoeUINormal;
            font-weight: lighter;
            margin-left: 33%;
            margin-top: 5%;
            min-height: 326px;
            padding-left: 150px;
        }
        .error h1 {
            color: #666;
            margin: 0px;
            padding-top: 12%;
        }
        .error h2 {
            font-weight: lighter;
            margin: 0px;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="error">
        <h1>
            <?php _e('There was an error that prevents normal operation of the website.', 'system') ?>
            <br/>
            <?php _e('Please try again later or contact the administrator.', 'system') ?>
        </h1>
        <h2><?php _e('Details', 'system') ?></h2>
        <p><?php echo $exception->getMessage() ?></p>
        <p>
            <?php foreach ($exception->data as $key => $value) { ?>
                <?php echo $key ?>: <?php echo $value ?><br/>
            <?php } ?>
        </p>
    </div>
</body>
</html>