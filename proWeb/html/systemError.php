<!DOCTYPE html>
<html>
<head>
    <title><?php _e('Page not found', 'system') ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link type="text/css" rel="stylesheet" href="/proWeb/html/error.css"/>
</head>
<body>
    <div class="error">
        <h1><?php _e('Error', 'system') ?></h1>
        <p>
            <?php _e('There was an error that prevents normal operation of the website.', 'system') ?>
            <br/>
            <?php _e('Please try again later or contact the administrator.', 'system') ?>
        </p>
        <h2><?php _e('Details', 'system') ?></h2>
        <p>
            #<?php echo $exception->code ?>:
            <?php echo $exception->getMessage() ?>
        </p>
        <p>
            <?php
                _e('Extra data:', 'system');
                echo '<br/>';
                foreach ($exception->data as $key => $value) {
                    echo $key.': '.$value.'<br/>';
                }
                _e('Program: ', 'system');
                echo $exception->getFile();
            ?>
        </p>
    </div>
</body>
</html>