<?php

    require_once("config.php");

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
        if ($GLOBALS['cfg_debug']) {
            echo "==getcontent: " . $url . "<br/>";
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $filecontent = curl_exec($curl);
        curl_close($curl);
        if ($GLOBALS['cfg_debug']) {
            echo "==getcontent: done." . "<br/>";
        }
        return $filecontent;
    }

    function savefile(& $content, $filename) {
        if ($GLOBALS['cfg_debug']) {
            echo "==savefile: " . $filename . "<br/>";
        }
        $myfile = fopen($filename, "w") or die("Unable to open file!");
        fwrite($myfile, $content);
        if ($GLOBALS['cfg_debug']) {
            echo "==savefile: done." . "<br/>";
        }
        fclose($myfile);
    }

    function loadfile($filename) {
        if ($GLOBALS['cfg_debug']) {
            echo "==loadfile: " . $filename . "<br/>";
        }

        if (file_exists($filename)) {
            $myfile = fopen($filename, "r");
            $filecontent = fread($myfile, filesize($filename));
        } else {
            $myfile = fopen($filename, "x");
            $filecontent = "";
        }
        if ($GLOBALS['cfg_debug']) {
            echo "==loadfile: done." . "<br/>";
        }
        fclose($myfile);
        return $filecontent;
    }

    function addtoarray(& $myarray, $newvalue) {
        if ($GLOBALS['cfg_debug_addtoarray']) {
            echo "==addtoarray: ";
        }
        foreach ($myarray as $value) {
            if ($newvalue == $value) {
                if ($GLOBALS['cfg_debug_addtoarray']) {
                    echo "EXISTS: " . $newvalue . "<br/>";
                }
                return false;
            }
        }
        if ($GLOBALS['cfg_debug_addtoarray']) {
            echo "NEW: " . $newvalue . "<br/>";
        }
        $myarray[] = $newvalue;
        return true;
    }

    function printarray(& $myarray) {
        if ($GLOBALS['cfg_debug_printarray']) {
            echo "==printarray" . "<br/>";
            foreach ($myarray as $value) {
                echo $value . "<br/>";
            }
        }
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
        if ($GLOBALS['cfg_debug']) {
            echo "==compareDiff" . "<br/>";
        }
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

            if ($GLOBALS['cfg_debug']) {
                echo "==deleted lines: " . "<br/>";
                printarray($deleted);
                echo "==added lines: " . "<br/>";
                printarray($added);
            }
            return true;
        } else {
            if ($GLOBALS['cfg_debug']) {
                echo "==no difference" . "<br/>";
            }
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
