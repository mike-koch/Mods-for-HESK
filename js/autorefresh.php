<?php
define('MINIMUM_REFRESH_THRESHOLD_IN_MILLISECONDS', 1000);
if ($_SESSION['autorefresh'] > MINIMUM_REFRESH_THRESHOLD_IN_MILLISECONDS) { ?>
    (function(){
     setTimeout("location.reload(true);",<?php echo $_SESSION['autorefresh']/1000; ?>);
    })();
<?php } ?>