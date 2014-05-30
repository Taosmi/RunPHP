<?php $console = \runPHP\Logger::getLog(); ?>
<div id="runPHPConsole" style="background-color:#000000; border-radius:30px 30px 0px 0px; bottom:0px; color:#fff; font-family:Arial; left:10%; position:fixed; width:80%;">
    <div style="clear:both; margin-left:8%;">
        <div style="float:left; padding:10px; text-align:center; width:22%;">
            <span style="color:#3769A0; display:block; font-size:28px; font-weight:bold;"><?php echo $console['time'] ?></span>
            <h3 style="color:#fff; font-size:12px; margin:0px;">Load Time</h3>
        </div>
        <div style="float:left; padding:10px; text-align:center; width:22%;">
            <span style="color:#953FA1; display:block; font-size:28px; font-weight:bold;"><?php echo $console['repo'] ?> Queries</span>
            <h3 style="color:#fff; font-size:12px; margin:0px;">DataBase</h3>
        </div>
        <div style="float:left; padding:10px; text-align:center; width:22%;">
            <span style="color:#D28C00; display:block; font-size:28px; font-weight:bold;"><?php echo $console['memory'] ?></span>
            <h3 style="color:#fff; font-size:12px; margin:0px;">Memory Used</h3>
        </div>
        <div style="float:left; padding:10px; text-align:center; width:22%;">
            <span style="color:#B72F09; display:block; font-size:28px; font-weight:bold;"><?php echo count($console['files']) ?> Files</span>
            <h3 style="color:#fff; font-size:12px; margin:0px;">Included</h3>
        </div>
    </div>
    <div id="PHProConsoleLog" style="border:1px solid #fff; clear:both; height:200px; margin:0 2.5%; overflow:auto; width:95%;">
        <table style="border-collapse:collapse; color:#fff; width:100%;">
        <?php foreach ($console['logs'] as $logItem) { ?>
            <tr style="border-bottom:1px solid #333333;">
            <?php switch ($logItem['level']) {
                case 'repo': ?>
                <td style="background-color:#953FA1 ; padding:5px 0px; text-align:center; vertical-align:top;">Repository</td>
                <td style="color:#fff; padding:5px;"><pre style="margin:0px;"><?php echo $logItem['msg'] ?></pre></td>
                <?php break; case 'error': ?>
                <td style="background-color:#B72F09 ; padding:5px 0px; text-align:center; vertical-align:top;">Error</td>
                <td style="color:#fff; padding:5px;"><?php echo str_replace('\n', '<br/>', $logItem['msg']) ?></td>
                <?php break; case 'debug': ?>
                <td style="background-color:#47740D ; padding:5px 0px; text-align:center; vertical-align:top;">Log</td>
                <td style="color:#fff; padding:5px;"><?php echo str_replace('\n', '<br/>', $logItem['msg']) ?></td>
                <?php break; case 'memory': ?>
                <td style="background-color:#D28C00; padding:5px 0px; text-align:center; vertical-align:top;">Memory</td>
                <td style="color:#fff; padding:5px;"><?php echo $logItem['msg'] ?></td>
                <?php break; case 'system': ?>
                <td style="background-color:#555; padding:5px 0px; text-align:center; vertical-align:top;">System</td>
                <td style="color:#999999; padding:5px;"><?php echo str_replace('\n', '<br/>', $logItem['msg']) ?></td>
                <?php break; case 'time': ?>
                <td style="background-color:#3769A0; padding:5px 0px; text-align:center; vertical-align:top;">Time</td>
                <td style="color:#fff; padding:5px;"><?php echo $logItem['msg'] ?></td>
                <?php break; case 'warning': ?>
                <td style="background-color:#47740D ; padding:5px 0px; text-align:center; vertical-align:top;">Warning</td>
                <td style="color:#fff; padding:5px;"><?php echo str_replace('\n', '<br/>', $logItem['msg']) ?></td>
                <?php break; default: ?>
                <td style="padding:5px 0px; text-align:center; vertical-align:top;"><?php echo $logItem['type'] ?></td>
                <td style="color:#fff; padding:5px;">Unknown type of log.</td>
            <?php } ?>
            </tr>
        <?php } ?>
        </table>
    </div>
    <div style="clear:both; font-size:11px; padding:5px 5%; text-align:right;">runPHP Console</div>
</div>