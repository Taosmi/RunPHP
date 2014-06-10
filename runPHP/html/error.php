<!DOCTYPE html>
<html>
<head>
    <title><?php _e('Error', 'system') ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link type="text/css" rel="stylesheet" href="/runPHP/html/error.css"/>
</head>
<body>
    <div class="error">
        <h1><?php _e('Error', 'system') ?></h1>
        <h2><?php echo $exception->getMessage() ?></h2>
        <p>
            <?php _e('There was an error that prevents normal operation of the website.', 'system') ?>
            <br/>
            <?php _e('Please try again later or contact the administrator.', 'system') ?>
        </p>
        <h2><?php _e('Details', 'system') ?></h2>
        <p>
            <?php
                foreach ($exception->data as $key => $value) {
                    echo $key.': '.$value.'<br/>';
                }
                echo __('Program', 'system').': '.$exception->getFile();
            ?>
        </p>
    </div>
</body>
</html>