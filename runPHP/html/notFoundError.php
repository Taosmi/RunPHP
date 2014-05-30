<!DOCTYPE html>
<html>
<head>
    <title><?php _e('Page not found', 'system') ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
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
            padding-left: 300px;
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
        <h1><?php _e('Page not found', 'system') ?></h1>
        <p><?php _e('Check the web address for errors or go back to the homepage.', 'system') ?></p>
    </div>
</body>
</html>