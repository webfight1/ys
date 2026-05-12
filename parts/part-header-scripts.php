<?php   
if(!defined('ABSPATH')) exit;

$header_scripts = get_field('header_scripts', 'options');

if($header_scripts) echo $header_scripts;
exit;