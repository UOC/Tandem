<?php
//Has to be loaded the config.inc.php to works

if ( defined( 'GOOGLE_ANALYTICS_ID' ) ) {
	echo "<script type=\"text/javascript\">
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', '" . GOOGLE_ANALYTICS_ID . "']);
	_gaq.push(['_trackPageview']);
	
	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
	</script>";

}
include_once __DIR__ . '/../tandemLog.php';
if ( isset( $_SESSION[ USE_WAITING_ROOM ] ) && $_SESSION[ USE_WAITING_ROOM ] == 1
     && defined( 'ENABLE_PUSH_NOTIFICATIONS' )
     && ENABLE_PUSH_NOTIFICATIONS
     && defined( 'PUSH_ALERT_NOTIFICATION_JS' ) ) {
	$user_language      = $_SESSION[ LANG ];//!empty($_REQUEST['locale']) ? $_REQUEST['locale'] : "es_ES";
	$course_register_id = isset( $_SESSION[ COURSE_ID ] ) ? $_SESSION[ COURSE_ID ] : - 1;
	$key                = substr( $user_language, 0, 2 ) . '.course' . $course_register_id;
	$segment_id         = isset( $PUSH_SEGMENTS_ID[ $key ] ) ? $PUSH_SEGMENTS_ID[ $key ] : false;

	?>
    <script type="text/javascript">
        (function (d, t) {
            var g = d.createElement(t),
                s = d.getElementsByTagName(t)[0];
            g.src = "<?php echo PUSH_ALERT_NOTIFICATION_JS?>";
            s.parentNode.insertBefore(g, s);
        }(document, "script"));
        (pushalertbyiw = window.pushalertbyiw || []).push(['onSuccess', callbackOnSuccessRegisterPush]);

        function callbackOnSuccessRegisterPush(result) {
            if (!result.alreadySubscribed) {
                console.log(result.subscriber_id); //will output the user's subscriberId
                console.log(result.alreadySubscribed); // False means user just Subscribed
                $.post('push/register.php', {'subscriber_id': result.subscriber_id}, function (res) {
                    /*if (res.result !== 'ok') {
                        alert("<?php echo $LanguageInstance->get( 'Error in register push notifications' )?>");
                    }*/
                }).fail(function () {
                    // alert("<?php echo $LanguageInstance->get( 'Error in register push notifications' )?>");
                }).always(function () {
                });
            }
        }

        (pushalertbyiw = window.pushalertbyiw || []).push(['onFailure', callbackOnFailureRegisterPush]);

        function callbackOnFailureRegisterPush(result) {
            console.log(result.status); //-1 - blocked, 0 - canceled or 1 - unsubscribed


            //YOUR CODE
        }

        <?php if ($segment_id) { ?>
        (pushalertbyiw = window.pushalertbyiw || []).push(['subscribeToSegment', <?php echo $segment_id?>]); //You can get segment ID from dashboard or via REST API
        <?php } ?>

    </script>
    <!-- End PushAlert -->
<?php }