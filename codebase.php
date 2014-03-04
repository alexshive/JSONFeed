<?php
/**************************************************
  Coppermine 1.5.x Plugin - JSONFeed
  **************************************************/

if (!defined('IN_COPPERMINE')) {
	die('Not in Coppermine...');
}

// Add plugin_install action
$thisplugin->add_action('plugin_install','xfd_install');

// Add plugin_uninstall action
$thisplugin->add_action('plugin_uninstall','xfd_uninstall');

// Install
function xfd_install()
{
	return true;
}


// Unnstall and drop settings table
function xfd_uninstall()
{
	return true;
}
