<?php
/**************************************************
  Coppermine 1.5.x Plugin - JSONFeed
  **************************************************/

if (!defined('IN_COPPERMINE')) {
	die('Not in Coppermine...');
}

// Add plugin_install action
$thisplugin->add_action('plugin_install','jsonfeed_install');

// Add plugin_uninstall action
$thisplugin->add_action('plugin_uninstall','jsonfeed_uninstall');

// Install
function jsonfeed_install()
{
	return true;
}


// Unnstall and drop settings table
function jsonfeed_uninstall()
{
	return true;
}
