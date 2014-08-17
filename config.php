<?php

// output function execution log
$cfg_debug = false;
// output log while adding valid lines to an array
$cfg_debug_addtoarray = false;
// output what's inside an array
$cfg_debug_printarray = false;
// save log to file, no matter it will appear on screen or not.
$cfg_debug_savelog = true;

$cfg_common_timezone = "Asia/Shanghai";

$cfg_image_textsize = 14;
$cfg_image_lineheight = 28;
$cfg_image_defaultwidth = 320;
$cfg_image_defaultquality = 80;
$cfg_image_leftmargin = 10;
$cfg_image_topmargin = 20;
$cfg_image_font = "./data/arial.ttf";

$cfg_mail_from = "noreply<noreply@yourdomain.com>";
$cfg_mail_replyto = "webmaster<webmaster@yourdomain.com>";
$cfg_mail_to = "yourmail@yourdomain.com";

$cfg_mail_data_tag_datetimenow = "#date_time_now#";
$cfg_mail_data_tag_isforceupdate = "#is_force_update#";
$cfg_mail_data_tag_willnotecho = "#will_not_echo#";
$cfg_mail_data_tag_debugvalid = "#debug_valid#";
$cfg_mail_data_tag_debuginvalid = "#debug_invalid#";
$cfg_mail_data_tag_debugmail = "#debug_mail#";
$cfg_mail_data_tag_debugsavelog = "#debug_save_log#";
$cfg_mail_data_tag_servermd5 = "#server_md5#";
$cfg_mail_data_tag_clientmd5 = "#client_md5#";
$cfg_mail_data_tag_diffimgurl = "#diff_img_url#";
$cfg_mail_data_tag_diffimgdata = "#diff_img_data#";
$cfg_mail_data_tag_debuglogurl = "#debug_log_url#";
$cfg_mail_data_tag_debuglogdata = "#debug_log_data#";

$cfg_mail_subject = "[LIST] List Updated! (" . $cfg_mail_data_tag_datetimenow . ")";
$cfg_mail_message = "
<html>
<head><title>" . $cfg_mail_subject . "</title></head>
<body>
<h2>List Update Notify</h2>
<p>The list you subscribed is updated. Please check the info below.</p>
<ul>
<li>Update Time: " . $cfg_mail_data_tag_datetimenow . "</li>
<li>Is Force Upate: " . $cfg_mail_data_tag_isforceupdate . "</li>
<li>Will Not Echo: " . $cfg_mail_data_tag_willnotecho . "</li>
</ul>
<h3>Debug</h3>
<hr>
<ul>
<li>Debug Valid: " . $cfg_mail_data_tag_debugvalid . "</li>
<li>Debug Invalid: " . $cfg_mail_data_tag_debuginvalid . "</li>
<li>Debug Mail: " . $cfg_mail_data_tag_debugmail . "</li>
<li>Debug Save Log: " . $cfg_mail_data_tag_debugsavelog . "</li>
</ul>
<h3>Hash</h3>
<hr>
<ul>
<li>Server Dnsmasq.conf MD5: " . $cfg_mail_data_tag_servermd5 . "</li>
<li>Client Dnsmasq.conf MD5: " . $cfg_mail_data_tag_clientmd5 . "</li>
</ul>
<h3>Diff</h3>
<hr>
<p>Url: <a href='" . $cfg_mail_data_tag_diffimgurl . "'>" . $cfg_mail_data_tag_diffimgurl . "</a></p>
<p><a href='" . $cfg_mail_data_tag_diffimgurl . "'><img src='data:image/jpeg;base64," . $cfg_mail_data_tag_diffimgdata . "'></a></p>
<h3>Log</h3>
<hr>
<p>Url: <a href='" . $cfg_mail_data_tag_debuglogurl . "'>" . $cfg_mail_data_tag_debuglogurl . "</a></p>
<p>" . $cfg_mail_data_tag_debuglogdata . "</p>
</body>
</html>";

$cfg_gfwlist_url = "https://autoproxy-gfwlist.googlecode.com/svn/trunk/gfwlist.txt";
$cfg_gfwlist_file = "gfwlist.txt";
$cfg_gfwlist_domain_file = "gfwlist_domain.txt";
$cfg_gfwlist_domain_extra_file = "./data/gfwlist_domain_extra.txt";

$cfg_dnsmasq_dnsserver = "8.8.8.8";
$cfg_dnsmasq_ipsetname = "vpn";
$cfg_dnsmasq_template = "./data/dnsmasq.template";
$cfg_dnsmasq_file = "dnsmasq.conf";

$cfg_update_log_file = "lastupdate.txt";
$cfg_update_interval = 60 * 60 * 6;

$cfg_action_get = false;
$cfg_action_md5 = false;

?>
