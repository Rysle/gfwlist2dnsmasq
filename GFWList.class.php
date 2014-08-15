<?php

require_once("common.php");

class GFWList {

    function __construct() {

    }

    function GFWList() {
        $this->__construct();
    }

    function getGFWContent($url, $outputfilename) {
        // download gfwlist and decode it
        if ($GLOBALS['cfg_debug']) {
            echo "==getGFWContent" . "<br/>";
        }
        $content = base64_decode(getcontent($url));
        savefile($content, $outputfilename);
        return $content;
    }

    function processGFWContent(& $content, $outputfilename) {
        // process gfwlist
        if ($GLOBALS['cfg_debug']) {
            echo "==processGFWContent" . "<br/>";
        }
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
                      if ($GLOBALS['cfg_debug_invalid']) {
                          echo $line . "<br/>";
                      }
                      continue;
                  } else if ($firstchar == '!') {
                      // comments, ignore
                      if ($GLOBALS['cfg_debug_invalid']) {
                          echo $line . "<br/>";
                      }
                      continue;
                  } else if ($firstchar == '@') {
                      // white list, ignore
                      if ($GLOBALS['cfg_debug_invalid']) {
                          echo $line . "<br/>";
                      }
                      continue;
                  } else if (filter_var($line, FILTER_VALIDATE_IP)) {
                      // ip address, ignore
                      if ($GLOBALS['cfg_debug_invalid']) {
                          echo $line . "<br/>";
                      }
                      continue;
                  } else {
                      // reg matching, remove prefix
                      $firsttwochars = substr($line, 0, 2);
                      if ($firsttwochars == '||') {
                          $line = substr($line, 2);
                      }

                      $firstchar = substr($line, 0, 1);
                      // exact matching, remove prefix
                      if ($firstchar == '|') {
                          $line = substr($line, 1);
                      // subdomain matching, remove prefix
                      } else if ($firstchar == '.') {
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
                          if ($GLOBALS['cfg_debug_invalid']) {
                              echo $line . "<br/>";
                          }
                          continue;
                      } else if ($firstchar == '!') {
                          // special lines like "||!--isaacmao.com"
                          if ($GLOBALS['cfg_debug_invalid']) {
                              echo $line . "<br/>";
                          }
                          continue;
                      } else if ($firstchar == '@') {
                          // just to make it same with former judgement...
                          if ($GLOBALS['cfg_debug_invalid']) {
                              echo $line . "<br/>";
                          }
                          continue;
                      } else if ($firstchar == '/') {
                          // reg expression or special lines like "/search?q=cache"
                          if ($GLOBALS['cfg_debug_invalid']) {
                              echo $line . "<br/>";
                          }
                          continue;
                      } else if ($firstchar == '%') {
                          // url-encoded, special lines like "%2Fsearch%3Fq%3Dcache"
                          if ($GLOBALS['cfg_debug_invalid']) {
                              echo $line . "<br/>";
                          }
                          continue;
                      }

                      if ($lastchar == '.') {
                          // end with a dot, like "google."
                          if ($GLOBALS['cfg_debug_invalid']) {
                              echo $line . "<br/>";
                          }
                          continue;
                      }

                      $posofchar = stripos($line, '.');
                      if (!$posofchar > 0) {
                          // has no dot, like "google"
                          if ($GLOBALS['cfg_debug_invalid']) {
                              echo $line . "<br/>";
                          }
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
                          if ($GLOBALS['cfg_debug_invalid']) {
                              echo $line . "<br/>";
                          }
                          continue;
                      }

                      if (addtoarray($domains, $line)) {
                          $domaincount++;
                      }

                      if ($GLOBALS['cfg_debug_valid']) {
                          echo $line . "<br/>";
                      }
                  }
              }
          }
          printarray($domains);
          printarraytofile($domains, $outputfilename);
          if ($GLOBALS['cfg_debug']) {
              echo "==processGFWContent: done." . "<br/>";
        }
          return $domains;
    }

    function generateDnsmasqConf(& $domains, $dnsserver, $ipsetname, $templatefile, $extracontentfile, $outputfilename) {
        if ($GLOBALS['cfg_debug']) {
            echo "==generateDnsmasqConf" . "<br/>";
        }
        $template = loadfile($templatefile);
        $dnsmasqconf = $template;
        $dnsmasqconf = $dnsmasqconf . "\n#GFWList Domains" . "\n";
        $domaincount = 0;
        foreach ($domains as $domain) {
            if (empty($domain)) {
                continue;
            }
            $dnsmasqconf = $dnsmasqconf . "server=/" . $domain . "/" . $dnsserver . "\n";
            $dnsmasqconf = $dnsmasqconf . "ipset=/" . $domain . "/" . $ipsetname . "\n";
            $domaincount++;
        }
        $dnsmasqconf = $dnsmasqconf . "#GFWList Domains Total: " . $domaincount . "\n";
        $dnsmasqconf = $dnsmasqconf . "\n#Extra Domains" . "\n";

        $content_extra = loadfile($extracontentfile);
        $domains_extra = explode("\n", $content_extra);
        $domaincount = 0;
        foreach ($domains_extra as $domain) {
            if (empty($domain)) {
                continue;
            }
            $dnsmasqconf = $dnsmasqconf . "server=/" . $domain . "/" . $dnsserver . "\n";
            $dnsmasqconf = $dnsmasqconf . "ipset=/" . $domain . "/" . $ipsetname . "\n";
            $domaincount++;
        }
        $dnsmasqconf = $dnsmasqconf . "#Extra Domains Total: " . $domaincount . "\n";
        $dnsmasqconf = $dnsmasqconf . "#Last Update Time: " . date("YmdHis") . "\n";

        savefile($dnsmasqconf, $outputfilename);
        if ($GLOBALS['cfg_debug']) {
            echo "==generateDnsmasqConf: done." . "<br/>";
        }
        return $dnsmasqconf;
    }

    function shouldUpdateNow($lastupdatefile, $updateinterval) {
        $lastUpdateTime = loadfile($lastupdatefile);
        $currentTime = time();

        if ($GLOBALS['cfg_action_md5']) {
            // do not update
            $lastUpdateTime = $currentTime;
        } else if ($GLOBALS['cfg_action_force']) {
            // force update
            $lastUpdateTime = 0;
        }

        if (($currentTime - $lastUpdateTime) > $updateinterval) {
            if ($GLOBALS['cfg_debug']) {
                echo "==shouldUpdateNow: true" . "<br/>";
            }
            return true;
        } else {
            if ($GLOBALS['cfg_debug']) {
                echo "==shouldUpdateNow: false" . "<br/>";
            }
            return false;
        }
    }

    function saveLastUpdateTime($lastUpdateTime, $lastUpdateFile) {
        savefile($lastUpdateTime, $lastUpdateFile);
    }

    function sendListUpdateMail($imagepath, $imageurl) {
        if ($GLOBALS['cfg_debug']) {
            echo "==sendlistupdatemail: " . $imageurl . "<br/>";
        }
        $image_raw = file_get_contents($imagepath);
        $image_base64 = base64_encode($image_raw);
        $from = $GLOBALS['cfg_mail_from'];
        $replyto = $GLOBALS['cfg_mail_replyto'];
        $to = $GLOBALS['cfg_mail_to'];
        $subject = $GLOBALS['cfg_mail_subject'];
        $message = $GLOBALS['cfg_mail_message'];
        $message = str_ireplace("#imageurl#", $imageurl, $message);
        $message = str_ireplace("#image_base64#", $image_base64, $message);

        sendmail($from, $replyto, $to, $subject, $message);
        if ($GLOBALS['cfg_debug_mail']) {
            echo $message . "<br/>";
        }
    }

    function get() {
        settimezone();
        $start = time();

        /* Configs */
        $gfwlist_url = $GLOBALS['cfg_gfwlist_url'];
        $gfwlist_file = $GLOBALS['cfg_gfwlist_file'];
        $gfwlist_domain_file = $GLOBALS['cfg_gfwlist_domain_file'];
        $gfwlist_domain_extra_file = $GLOBALS['cfg_gfwlist_domain_extra_file'];

        $dnsmasq_dnsserver = $GLOBALS['cfg_dnsmasq_dnsserver'];
        $dnsmasq_ipsetname = $GLOBALS['cfg_dnsmasq_ipsetname'];
        $dnsmasq_template = $GLOBALS['cfg_dnsmasq_template'];
        $dnsmasq_file = $GLOBALS['cfg_dnsmasq_file'];

        $update_log_file = $GLOBALS['cfg_update_log_file'];
        $update_interval = $GLOBALS['cfg_update_interval'];

        /* Files to create */
        $today_dir = "./" . date("Ymd") . "/";
        $yesterday_dir  = "./" . date("Ymd", strtotime("-1 days")) . "/";
        $target_dir = $today_dir;

        $shouldupdate = $this->shouldUpdateNow($update_log_file, $update_interval);

        if (!file_exists($target_dir)) {
            // if we come to a new day, ignore updateinterval.
            $shouldupdate = true;
            mkdir($target_dir);
        }

        $target_gfwlist_file = $target_dir . $gfwlist_file;
        $target_gfwlist_domain_file = $target_dir . $gfwlist_domain_file;
        $target_dnsmasq_file = $target_dir . $dnsmasq_file;
        $dnsmasq_conf = "";
        $updatesuccess = false;

        if ($shouldupdate) {
            $gfwlist_content = $this->getGFWContent($gfwlist_url, $target_gfwlist_file);
            $domains = $this->processGFWContent($gfwlist_content, $target_gfwlist_domain_file);
            $dnsmasq_conf = $this->generateDnsmasqConf($domains, $dnsmasq_dnsserver, $dnsmasq_ipsetname, $dnsmasq_template, $gfwlist_domain_extra_file, $target_dnsmasq_file);
            if (sizeof($domains) > 0) {
                $updatesuccess = true;
            }
        }

        if ($updatesuccess) {
            $diffpath = $target_dir . "diff_" . date("YmdHis") . ".txt";
            $hasdiff = comparediff($yesterday_dir . $gfwlist_domain_file, $today_dir . $gfwlist_domain_file, $diffpath);
            if ($hasdiff) {
                $imagepath = diff2image($diffpath);
                $imageurl = getfileurl($imagepath);
                $this->sendListUpdateMail($imagepath, $imageurl);
            }
            $this->saveLastUpdateTime(time(), $update_log_file);
        } else {
            $dnsmasq_conf = loadfile($target_dnsmasq_file);
        }

        if ($GLOBALS['cfg_debug']) {
            echo "==consumes: " . (time() - $start) . "s" . "<br/>";
        }

        if ($GLOBALS['cfg_action_get']) {
            echo base64_encode($dnsmasq_conf);
        } else if ($GLOBALS['cfg_action_md5']) {
            echo md5($dnsmasq_conf);
        }
    }
}
?>
