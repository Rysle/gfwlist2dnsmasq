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

// echo base64 encoded dnsmasq.conf into this file
$GLOBALS['cfg_action_get'] = isparamtrue("get");

// echo md5 checksum of dnsmasq.conf into this file
$GLOBALS['cfg_action_md5'] = isparamtrue("md5");

$gfwlist = new GFWList();

if ($GLOBALS['cfg_action_get']) {
    $gfwlist->get();
} else if ($GLOBALS['cfg_action_md5']) {
    $gfwlist->md5();
}


?>
