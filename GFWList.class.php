<?php

require_once("common.php");

class GFWList {

    var $debug_valid;
    var $debug_invalid;
    var $debug_mail;

    var $debug_save_log;
    var $debug_log_file;

    var $action_param_force;
    var $action_param_not_echo;

    var $gfwlist_url;
    var $gfwlist_file;
    var $gfwlist_domain_file;
    var $gfwlist_domain_extra_file;

    var $dnsmasq_dnsserver;
    var $dnsmasq_ipsetname;
    var $dnsmasq_template;
    var $dnsmasq_file;

    var $update_log_file;
    var $update_interval;

    var $date_time_now;
    var $date_of_today;
    var $date_of_yesterday;

    var $dir_today;
    var $dir_yesterday;

    var $target_dir;
    var $target_gfwlist_file;
    var $target_gfwlist_domain_file;
    var $target_dnsmasq_file;

    var $diff_txt_file;
    var $diff_img_file;

    var $url_target_gfwlist_file;
    var $url_target_gfwlist_domain_file;
    var $url_target_dnsmasq_file;
    var $url_diff_txt_file;
    var $url_diff_img_file;
    var $url_debug_log_file;

    var $content_gfwlist;
    var $content_domains;
    var $content_domains_extra;
    var $content_dnsmasq_conf;

    var $time_update_start;
    var $time_update_finish;
    var $time_last_update_time;

    var $update_now;
    var $update_success;
    var $update_has_diff;

    var $server_dnsmasq_conf_md5;
    var $client_dnsmasq_conf_md5;

    function __construct() {
        settimezone();

        // output invalid lines that were filtered
        $this->debug_valid = isparamtrue("debug_invalid");

        // output valid lines reserved
        $this->debug_invalid = isparamtrue("debug_valid");

        // output mail message
        $this->debug_mail = isparamtrue("debug_mail");

        // force update
        $this->action_param_force = isparamtrue("force");

        // don't echo the content
        $this->action_param_not_echo = isparamtrue("not_echo");

        $this->client_dnsmasq_conf_md5 = getparam("clientmd5");

        /* Configs */
        $this->gfwlist_url = $GLOBALS['cfg_gfwlist_url'];
        $this->gfwlist_file = $GLOBALS['cfg_gfwlist_file'];
        $this->gfwlist_domain_file = $GLOBALS['cfg_gfwlist_domain_file'];
        $this->gfwlist_domain_extra_file = $GLOBALS['cfg_gfwlist_domain_extra_file'];

        $this->dnsmasq_dnsserver = $GLOBALS['cfg_dnsmasq_dnsserver'];
        $this->dnsmasq_ipsetname = $GLOBALS['cfg_dnsmasq_ipsetname'];
        $this->dnsmasq_template = $GLOBALS['cfg_dnsmasq_template'];
        $this->dnsmasq_file = $GLOBALS['cfg_dnsmasq_file'];

        $this->update_log_file = $GLOBALS['cfg_update_log_file'];
        $this->update_interval = $GLOBALS['cfg_update_interval'];

        $this->debug_save_log = $GLOBALS['cfg_debug_savelog'];

        /* Date Time */
        $this->date_time_now = date("YmdHis");
        $this->date_of_today = date("Ymd");
        $this->date_of_yesterday = date("Ymd", strtotime("-1 days"));

        /* Dir and Files */
        $this->dir_today = "./" . $this->date_of_today . "/";
        $this->dir_yesterday  = "./" . $this->date_of_yesterday . "/";

        $this->target_dir = $this->dir_today;
        $this->target_gfwlist_file = $this->target_dir . $this->gfwlist_file;
        $this->target_gfwlist_domain_file = $this->target_dir . $this->gfwlist_domain_file;
        $this->target_dnsmasq_file = $this->target_dir . $this->dnsmasq_file;

        $this->diff_txt_file = $this->target_dir . "diff_" . $this->date_time_now . ".txt";
        $this->diff_img_file = "";

        $this->debug_log_file = $this->target_dir . "log_" . $this->date_time_now . ".txt";

        $this->url_target_gfwlist_file = "";
        $this->url_target_gfwlist_domain_file = "";
        $this->url_target_dnsmasq_file = "";
        $this->url_diff_txt_file = "";
        $this->url_diff_img_file = "";
        $this->url_debug_log_file = "";

    }

    function GFWList() {
        $this->__construct();
    }

    function debug_valid($log) {
        if ($this->debug_valid) {
            debug($log);
        }
    }

    function debug_invalid($log) {
        if ($this->debug_invalid) {
            debug($log);
        }
    }

    function debug_mail($log) {
        if ($this->debug_mail) {
            debug($log);
        }
    }

    function getGFWContent($url, $outputfilename) {
        // download gfwlist and decode it
        debug("==getGFWContent");
        $content = base64_decode(getcontent($url));
        savefile($content, $outputfilename);
        return $content;
    }

    function processGFWContent(& $content, $outputfilename) {
        // process gfwlist
        debug("==processGFWContent");
        $lines = explode("\n", $content);
        $domains = array();
        $domaincount = 0;
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            } else {
                $firstchar = substr($line, 0, 1);
                if ($firstchar == '[') {
                    // session header, ignore
                    $this->debug_invalid($line);
                    continue;
                } else if ($firstchar == '!') {
                    // comments, ignore
                    $this->debug_invalid($line);
                    continue;
                } else if ($firstchar == '@') {
                    // white list, ignore
                    $this->debug_invalid($line);
                    continue;
                } else if (filter_var($line, FILTER_VALIDATE_IP)) {
                    // ip address, ignore
                    $this->debug_invalid($line);
                    continue;
                } else {
                    // reg matching, remove prefix
                    $firsttwochars = substr($line, 0, 2);
                    if ($firsttwochars == '||') {
                        $line = substr($line, 2);
                    }

                    $firstchar = substr($line, 0, 1);
                    if ($firstchar == '|') {
                        // exact matching, remove prefix
                        $line = substr($line, 1);
                    } else if ($firstchar == '.') {
                        // subdomain matching, remove prefix
                        $line = substr($line, 1);
                    }

                    // remove scheme
                    $line = str_ireplace("http://", '', $line);
                    $line = str_ireplace("https://", '', $line);

                    // postfix keyword matching, just remove anything after *
                    $posofchar = stripos($line, '*');
                    if ($posofchar > 0) {
                        $line = substr($line, 0, $posofchar);
                    }

                    // stop at first slash, keep the hostname
                    $posofchar = stripos($line, '/');
                    if ($posofchar > 0) {
                        $line = substr($line, 0, $posofchar);
                    }

                    // stop at first slash(url-encoded), keep the hostname
                    $posofchar = stripos($line, '%');
                    if ($posofchar > 0) {
                        $line = substr($line, 0, $posofchar);
                    }

                    $firstchar = substr($line, 0, 1);
                    $lastchar = substr($line, -1, 1);
                    $invalid = false;
                    if ($firstchar == '[') {
                        // just to make it same with former judgement...
                        $this->debug_invalid($line);
                        continue;
                    } else if ($firstchar == '!') {
                        // special lines like "||!--isaacmao.com"
                        $this->debug_invalid($line);
                        continue;
                    } else if ($firstchar == '@') {
                        // just to make it same with former judgement...
                        $this->debug_invalid($line);
                        continue;
                    } else if ($firstchar == '/') {
                        // reg expression or special lines like "/search?q=cache"
                        $this->debug_invalid($line);
                        continue;
                    } else if ($firstchar == '%') {
                      // url-encoded, special lines like "%2Fsearch%3Fq%3Dcache"
                        $this->debug_invalid($line);
                        continue;
                    }

                    if ($lastchar == '.') {
                        // end with a dot, like "google."
                        $this->debug_invalid($line);
                        continue;
                    }

                    $posofchar = stripos($line, '.');
                    if (!$posofchar > 0) {
                        // has no dot, like "google"
                        $this->debug_invalid($line);
                        continue;
                    }

                    if ($firstchar == '*') {
                        // still start with *, subdomain matching, keep the top domain
                        $line = substr($line, 1);
                    }

                    $firstchar = substr($line, 0, 1);
                    if ($firstchar == '.') {
                        // still start with a dot, subdomain matching, keep the top domain
                        $line = substr($line, 1);
                    }

                    if (!filter_var('http://' . $line, FILTER_VALIDATE_URL)) {
                        // most of the time, we have got the domain now, so check if it is a valid url
                        $this->debug_invalid($line);
                        continue;
                    }

                    if (addtoarray($domains, $line)) {
                        $domaincount++;
                    }

                    $this->debug_valid($line);
                }
            }
        }
        printarraytofile($domains, $outputfilename);
        debug_printarray($domains);
        debug("==processGFWContent: done.");
        return $domains;
    }

    function appendArrayToDnsmasqConf($arrayname, &$array, &$dnsmasqconf, $dnsserver, $ipsetname) {
        $dnsmasqconf = $dnsmasqconf . "\n#" . $arrayname . "\n";
        $domaincount = 0;
        foreach ($array as $domain) {
            if (empty($domain)) {
                continue;
            }
            $dnsmasqconf = $dnsmasqconf . "server=/" . $domain . "/" . $dnsserver . "\n";
            $dnsmasqconf = $dnsmasqconf . "ipset=/" . $domain . "/" . $ipsetname . "\n";
            $domaincount++;
        }
        $dnsmasqconf = $dnsmasqconf . "#" . $arrayname . " Total: " . $domaincount . "\n";
        debug("==appendArrayToDnsmasqConf: " . $arrayname . ", domaincount: " . $domaincount);
    }

    function generateDnsmasqConf(&$domains, $dnsserver, $ipsetname, $templatefile, $extracontentfile, $outputfilename) {
        debug("==generateDnsmasqConf");
        $template = loadfile($templatefile);
        $dnsmasqconf = $template;
        $this->appendArrayToDnsmasqConf("GFWList Domains", $domains, $dnsmasqconf, $dnsserver, $ipsetname);

        $content_extra = loadfile($extracontentfile);
        $domains_extra = explode("\n", $content_extra);
        $this->content_domains_extra = $domains_extra;
        $this->appendArrayToDnsmasqConf("Extra Domains", $domains_extra, $dnsmasqconf, $dnsserver, $ipsetname);

        savefile($dnsmasqconf, $outputfilename);
        debug("==generateDnsmasqConf: done.");
        return $dnsmasqconf;
    }

    function shouldUpdateNow($currentTime, $lastUpdateTime, $updateinterval) {
        debug("==lastUpdateTime: " . $lastUpdateTime);
        debug("==currentTime: " . $currentTime);
        if (!file_exists($this->target_dir)) {
            // if we come to a new day, ignore updateinterval.
            mkdir($this->target_dir);
            $lastUpdateTime = 0;
            debug("==newDay");
        }

        if ($this->action_param_force) {
            // force update
            $lastUpdateTime = 0;
            debug("==forceUpdate");
        }

        if (($currentTime - $lastUpdateTime) > $updateinterval) {
            debug("==shouldUpdateNow: true");
            return true;
        } else {
            debug("==shouldUpdateNow: false");
            return false;
        }
    }

    function saveLastUpdateTime($lastUpdateTime, $lastUpdateFile) {
        savefile($lastUpdateTime, $lastUpdateFile);
    }

    function sendListUpdateMail() {
        debug("==sendlistupdatemail: " . $this->url_diff_img_file);

        $from = $GLOBALS['cfg_mail_from'];
        $replyto = $GLOBALS['cfg_mail_replyto'];
        $to = $GLOBALS['cfg_mail_to'];
        $subject = $GLOBALS['cfg_mail_subject'];
        $message = $GLOBALS['cfg_mail_message'];

        $mail_data_tag_datetimenow = $GLOBALS['cfg_mail_data_tag_datetimenow'];
        $mail_data_tag_isforceupdate = $GLOBALS['cfg_mail_data_tag_isforceupdate'];
        $mail_data_tag_willnotecho = $GLOBALS['cfg_mail_data_tag_willnotecho'];
        $mail_data_tag_debugvalid = $GLOBALS['cfg_mail_data_tag_debugvalid'];
        $mail_data_tag_debuginvalid = $GLOBALS['cfg_mail_data_tag_debuginvalid'];
        $mail_data_tag_debugmail = $GLOBALS['cfg_mail_data_tag_debugmail'];
        $mail_data_tag_debugsavelog = $GLOBALS['cfg_mail_data_tag_debugsavelog'];
        $mail_data_tag_servermd5 = $GLOBALS['cfg_mail_data_tag_servermd5'];
        $mail_data_tag_clientmd5 = $GLOBALS['cfg_mail_data_tag_clientmd5'];
        $mail_data_tag_diffimgurl = $GLOBALS['cfg_mail_data_tag_diffimgurl'];
        $mail_data_tag_diffimgdata = $GLOBALS['cfg_mail_data_tag_diffimgdata'];
        $mail_data_tag_debuglogurl = $GLOBALS['cfg_mail_data_tag_debuglogurl'];
        $mail_data_tag_debuglogdata = $GLOBALS['cfg_mail_data_tag_debuglogdata'];

        $image_raw = file_get_contents($this->diff_img_file);
        $image_base64 = base64_encode($image_raw);
        $debug_log = get_debug_log();
        $debug_log = str_replace("\n", "<br/>", $debug_log);

        $subject = str_ireplace($mail_data_tag_datetimenow, $this->date_time_now, $subject);

        $message = str_ireplace($mail_data_tag_datetimenow, $this->date_time_now, $message);
        $message = str_ireplace($mail_data_tag_isforceupdate, var_export($this->action_param_force, true), $message);
        $message = str_ireplace($mail_data_tag_willnotecho, var_export($this->action_param_not_echo, true), $message);
        $message = str_ireplace($mail_data_tag_debugvalid, var_export($this->debug_valid, true), $message);
        $message = str_ireplace($mail_data_tag_debuginvalid, var_export($this->debug_invalid, true), $message);
        $message = str_ireplace($mail_data_tag_debugmail, var_export($this->debug_mail, true), $message);
        $message = str_ireplace($mail_data_tag_debugsavelog, var_export($this->debug_save_log, true), $message);
        $message = str_ireplace($mail_data_tag_servermd5, $this->server_dnsmasq_conf_md5, $message);
        $message = str_ireplace($mail_data_tag_clientmd5, $this->client_dnsmasq_conf_md5, $message);
        $message = str_ireplace($mail_data_tag_diffimgurl, $this->url_diff_img_file, $message);
        $message = str_ireplace($mail_data_tag_diffimgdata, $image_base64, $message);
        $message = str_ireplace($mail_data_tag_debuglogurl, $this->url_debug_log_file, $message);
        $message = str_ireplace($mail_data_tag_debuglogdata, $debug_log, $message);

        sendmail($from, $replyto, $to, $subject, $message);
        $this->debug_mail($message);
    }

    function get() {
        $this->time_update_start = time();
        $this->time_last_update_time = loadfile($this->update_log_file);
        $this->update_now = $this->shouldUpdateNow($this->time_update_start, $this->time_last_update_time, $this->update_interval);
        $this->update_success = false;

        if ($this->update_now) {
            $this->content_gfwlist = $this->getGFWContent($this->gfwlist_url, $this->target_gfwlist_file);
            $this->content_domains = $this->processGFWContent($this->content_gfwlist, $this->target_gfwlist_domain_file);
            $this->content_dnsmasq_conf = $this->generateDnsmasqConf($this->content_domains, $this->dnsmasq_dnsserver,
                $this->dnsmasq_ipsetname, $this->dnsmasq_template, $this->gfwlist_domain_extra_file, $this->target_dnsmasq_file);
            if (sizeof($this->content_domains) > 0) {
                $this->update_success = true;
                debug("==updateSuccess");
            } else {
                debug("==updateFailed");
            }
        }

        if ($this->update_success) {
            $this->update_has_diff = comparediff($this->dir_yesterday . $this->gfwlist_domain_file, $this->dir_today . $this->gfwlist_domain_file, $this->diff_txt_file);
            if ($this->update_has_diff) {
                debug("==updateHasDiff");
                $this->diff_img_file = diff2image($this->diff_txt_file);
                $this->url_target_gfwlist_file = getfileurl($this->target_gfwlist_file);
                $this->url_target_gfwlist_domain_file = getfileurl($this->target_gfwlist_domain_file);
                $this->url_target_dnsmasq_file = getfileurl($this->target_dnsmasq_file);
                $this->url_diff_txt_file = getfileurl($this->diff_txt_file);
                $this->url_diff_img_file = getfileurl($this->diff_img_file);
                $this->url_debug_log_file = getfileurl($this->debug_log_file);
            } else {
                debug("==updateHasNoDiff");
            }
            $this->saveLastUpdateTime($this->time_update_start, $this->update_log_file);
        } else {
            $this->content_dnsmasq_conf = loadfile($this->target_dnsmasq_file);
        }

        $this->server_dnsmasq_conf_md5 = md5($this->content_dnsmasq_conf);
        debug("==serverDnsmasqConfMD5: " . $this->server_dnsmasq_conf_md5);
        debug("==clientDnsmasqConfMD5: " . $this->client_dnsmasq_conf_md5);
        if ($this->update_has_diff) {
            $this->sendListUpdateMail();
        }

        $this->time_update_finish = time();
        debug("==consumes: " . ($this->time_update_finish - $this->time_update_start) . "s");
        if ($this->debug_save_log) {
            save_debug_log($this->debug_log_file);
        }
        if (!$this->action_param_not_echo) {
            echo base64_encode($this->content_dnsmasq_conf);
        }
    }

    function md5() {
        $this->content_dnsmasq_conf = loadfile($this->target_dnsmasq_file);
        echo md5($this->content_dnsmasq_conf);
    }
}
?>
