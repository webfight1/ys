<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<script defer src="https://www.googletagmanager.com/gtag/js?id=AW-944505518"></script>
<script defer>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', 'AW-944505518');
</script>
<?php
$tracking_scripts = get_field('tracking_scripts', 'options');

if ($tracking_scripts) {
    echo $tracking_scripts;
}
exit;
