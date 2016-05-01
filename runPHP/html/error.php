<!DOCTYPE html>
<html>
<head>
    <title><?php _e('Error', 'system') ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link type="text/css" rel="stylesheet" href="/runPHP/statics/error.css"/>
</head>
<body>
    <div class="error">
        <h1><?php echo $error['msg']?></h1>
        <h2>
            <?php _e('There was an error that prevents normal operation of the website.', 'system') ?>
            <br/>
            <?php _e('Please try again later or contact the administrator.', 'system') ?>
        </h2>
        <?php if ($error['data']) { ?>
        <p><?php _e('Details', 'system') ?></p>
        <p>
            <?php foreach ($error['data'] as $key => $value) { ?>
                <?php echo $key ?>: <?php echo $value ?><br/>
            <?php } ?>
        </p>
        <?php } ?>
    </div>
</body>
</html>