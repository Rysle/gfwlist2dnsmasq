<?php

    require_once("config.php");

    // output function execution log
    $GLOBALS['cfg_debug'] = isparamtrue("debug");

    // output log while adding valid lines to an array
    $GLOBALS['cfg_debug_addtoarray'] = isparamtrue("debug_addtoarray");

    // output what's inside an array
    $GLOBALS['cfg_debug_printarray'] = isparamtrue("debug_printarray");

    function debug($log) {
        if ($GLOBALS['cfg_debug']) {
            echo $log . "<br/>";
        }
    }

    function debug_addtoarray($log) {
        if ($GLOBALS['cfg_debug_addtoarray']) {
            debug($log);
        }
    }

    function debug_printarray(& $myarray) {
        if ($GLOBALS['cfg_debug_printarray']) {
            debug("==printarray");
            foreach ($myarray as $value) {
                debug($value);
            }
        }
    }

    function isparamtrue($paramname) {
        if (isset($_REQUEST[$paramname]) && $_REQUEST[$paramname] == "1") {
            return true;
        } else {
            return false;
        }
    }

    function settimezone() {
        if(version_compare(PHP_VERSION, '5.1.0', '>=')) {
            date_default_timezone_set($GLOBALS['cfg_common_timezone']);
        } else {
            putenv("TZ=" . $GLOBALS['cfg_common_timezone']);
        }
    }

    function getcontent($url) {
        // use curl to download a file, both http/https supported
        debug("==getcontent: " . $url);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $filecontent = curl_exec($curl);
        curl_close($curl);
        debug("==getcontent: done.");
        return $filecontent;
    }

    function savefile(& $content, $filename) {
        debug("==savefile: " . $filename);
        $myfile = fopen($filename, "w") or die("Unable to open file!");
        fwrite($myfile, $content);
        debug("==savefile: done.");
        fclose($myfile);
    }

    function loadfile($filename) {
        debug("==loadfile: " . $filename);
        if (file_exists($filename)) {
            $myfile = fopen($filename, "r");
            $filecontent = fread($myfile, filesize($filename));
        } else {
            $myfile = fopen($filename, "x");
            $filecontent = "";
        }
        debug("==loadfile: done.");
        fclose($myfile);
        return $filecontent;
    }

    function addtoarray(& $myarray, $newvalue) {
        debug_addtoarray("==addtoarray: ");
        foreach ($myarray as $value) {
            if ($newvalue == $value) {
                debug_addtoarray("EXISTS: " . $newvalue);
                return false;
            }
        }
        debug_addtoarray("NEW: " . $newvalue);
        $myarray[] = $newvalue;
        return true;
    }

    function printarraytofile(& $myarray, $filename) {
        $content = "";
        foreach ($myarray as $value) {
            $content = $content . $value . "\n";
        }
        savefile($content, $filename);
        return $content;
    }

    function text2jpg($content, $line, $imagepath) {
        $textsize = $GLOBALS['cfg_image_textsize'];
        $lineheight = $GLOBALS['cfg_image_lineheight'];
        $defaultwidth = $GLOBALS['cfg_image_defaultwidth'];
        $defaultquality = $GLOBALS['cfg_image_defaultquality'];
        $leftmargin = $GLOBALS['cfg_image_leftmargin'];
        $topmargin = $GLOBALS['cfg_image_topmargin'];
        $font = $GLOBALS['cfg_image_font'];

        $width = $defaultwidth;
        $height = $lineheight * $line;
        $quality = $defaultquality;
        $im = imagecreate($width, $height);
        $textcolor = imagecolorallocate($im, 0, 0, 0);
        $bgcolor = imagecolorallocate($im, 255, 255, 255);

        imagefilledrectangle($im, 0, 0, $width, $height, $bgcolor);
        imagettftext($im, $textsize, 0, $leftmargin, $topmargin, $textcolor, $font, $content);
        imagejpeg($im, $imagepath, $quality);
        imagedestroy($im);
    }

    function comparediff($file1, $file2, $diffpath) {
        debug("==compareDiff");
        $content1 = loadfile($file1);
        $content2 = loadfile($file2);
        $content1_array = explode("\n", $content1);
        $content2_array = explode("\n", $content2);
        $deleted = array_diff($content1_array, $content2_array);
        $added = array_diff($content2_array, $content1_array);

        if (sizeof($deleted) > 0 || sizeof($added) > 0) {
            $diffcontent = "#Deleted: \n";
            $diffcontent = $diffcontent . implode("\n", $deleted);
            $diffcontent = $diffcontent . "\n\n#Added: \n";
            $diffcontent = $diffcontent . implode("\n", $added);
            savefile($diffcontent, $diffpath);

            debug("==deleted lines: ");
            debug_printarray($deleted);
            debug("==added lines: ");
            debug_printarray($added);
            return true;
        } else {
            debug("==no difference");
            return false;
        }
    }

    function diff2image($diffpath) {
        $content = loadfile($diffpath);
        $content_array = explode("\n", $content);
        $imagepath = $diffpath . ".jpg";
        text2jpg($content, sizeof($content_array), $imagepath);
        return $imagepath;
    }

    function getfileurl($filepath) {
        $url = "http://" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . "/" . $filepath;
        return $url;
    }

    function sendmail($from, $replyto, $to, $subject, $message) {
        $headers = array (
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset="UTF-8";',
            'Content-Transfer-Encoding: base64',
            'From: ' . $from,
            'Reply-To: ' . $replyto,
            'Return-Path: ' . $replyto,
            'X-Mailer: PHP Script',
        );
        // split the string to smaller chunks to match RFC 2045 semantics
        $message = chunk_split(base64_encode($message), 70, "\r\n");
        mail($to, $subject, "", implode("\r\n", $headers) . "\r\n" . $message);
    }

?>
