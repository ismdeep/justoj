<?php
/**
 * Created by PhpStorm.
 * User: L. Jiang <l.jiang.1024@gmail.com>
 * Date: 2018/5/6
 * Time: 22:31
 */

namespace app\extra\util;


class PasswordUtil {
    static public function check_password($password, $saved) {
        $svd = base64_decode($saved);
        $salt = substr($svd, 20);
        $hash = base64_encode(sha1(md5($password) . $salt, true) . $salt);
        if (strcmp($hash, $saved) == 0) return true;
        else return false;
    }

    static public function gen_password($password) {
        $password = md5($password);
        $salt = sha1(rand());
        $salt = substr($salt, 0, 4);
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
        return $hash;
    }

    static public function random_string($base, $length) {
        $ans = '';
        for ($i = 0; $i < $length; $i++) {
            $ans .= $base[mt_rand(0, strlen($base) - 1)];
        }
        return $ans;
    }
}