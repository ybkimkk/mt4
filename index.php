<?php
//echo 234;exit;


//function think_decrypt($data, $key) {
//    $key    = md5($key);
//    $x      = 0;
//    $data   = base64_decode($data);
//    $expire = substr($data, 0, 10);
//    $data   = substr($data, 10);
//    if ($expire > 0 && $expire < time()) {
//        return '';
//    }
//    $len  = strlen($data);
//    $l    = strlen($key);
//    $char = $str = '';
//    for ($i = 0; $i < $len; $i++) {
//        if ($x == $l) {
//            $x = 0;
//        }
//
//        $char .= substr($key, $x, 1);
//        $x++;
//    }
//    for ($i = 0; $i < $len; $i++) {
//        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
//            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
//        } else {
//            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
//        }
//    }
//    return base64_decode($str);
//}
//
//
//print_r(think_decrypt('MDAwMDAwMDAwMIeRhs+Pm7Jih82FzQ','111'));
//exit;
header("location:/admin/");