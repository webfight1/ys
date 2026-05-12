<?php   
if(!defined('ABSPATH')) exit;

$tracking_scripts = get_field('tracking_scripts', 'options');

if($tracking_scripts) echo $tracking_scripts;
exit;