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
$cfg_mail_subject = "[LIST] List Updated!";
$cfg_mail_message = "
<html>
<body>
Hello, the list you subscribed is updated: <br/>
<a href='#imageurl#'>
<img src='data:image/jpeg;base64,#image_base64#'>
</a>
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
