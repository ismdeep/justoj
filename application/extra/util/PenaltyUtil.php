<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/15
 * Time: 9:52 AM
 */

namespace app\extra\util;


class PenaltyUtil {
    static function penalty_int_2_text($val) {
        $s = $val % 60;
        $val -= $s;
        $val /= 60;
        $m = $val % 60;
        $val -= $m;
        $val /= 60;
        $h = $val;
        if (0 == $h) return $m . ":" . $s;
        return $h . ":" . $m . ":" . $s;
    }
}