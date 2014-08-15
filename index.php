<?php
/**
* A simple script for converting the latest gfwlist to dnsmasq.conf.
* By Rysle
* Aug 6, 2014
*/

// TODO:
// nicer image diff
// visit stats
// extra online edit

require_once("GFWList.class.php");

// output function execution log
$GLOBALS['cfg_debug'] = isparamtrue("debug");

// output invalid lines that were filtered
$GLOBALS['cfg_debug_invalid'] = isparamtrue("debug_invalid");

// output valid lines reserved
$GLOBALS['cfg_debug_valid'] = isparamtrue("debug_valid");

// output log while adding valid lines to an array
$GLOBALS['cfg_debug_addtoarray'] = isparamtrue("debug_addtoarray");

// output what's inside an array
$GLOBALS['cfg_debug_printarray'] = isparamtrue("debug_printarray");

// output mail message
$GLOBALS['cfg_debug_mail'] = isparamtrue("debug_mail");

// echo base64 encoded dnsmasq.conf into this file
$GLOBALS['cfg_action_get'] = isparamtrue("get");

// echo md5 checksum of dnsmasq.conf into this file
$GLOBALS['cfg_action_md5'] = isparamtrue("md5");

// force update
$GLOBALS['cfg_action_force'] = isparamtrue("force");

$gfwlist = new GFWList();
$gfwlist->get();

?>
