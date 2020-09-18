<?php

// 应用公共文件

/**
 * intercept while $flag is true
 *
 * @param $flag
 * @param $msg
 */
function intercept ($flag, $msg) {
    if ($flag) {
        echo $msg;
        die();
    }
}

/**
 * intercept while $flag is true and return json text
 *
 * @param $flag
 * @param $msg
 */
function intercept_json ($flag, $msg) {
    if ($flag) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'msg' => $msg]);
        die();
    }
}


function subtext($text, $length)
{
    if(mb_strlen($text, 'utf8') > $length) {
        return mb_substr($text, 0, $length, 'utf8').'...';
    } else {
        return $text;
    }

}


function paste_allowed_langs() {
    return [
        'text' => 'Plain Text',
        'c' => 'C',
        'cpp' => 'C++',
        'java' => 'Java',
        'py2' => 'Python2',
        'py3' => 'Python3',
        'js' => 'JavaScript',
        'clisp' => 'Common Lisp',
        'bash' => 'Bash',
        'sql' => 'SQL'
    ];
}

function datetime_human_valid($datetime_str) {
    $patten = "/^\d{4}[\-](0?[1-9]|1[012])[\-](0?[1-9]|[12][0-9]|3[01])(\s+(0?[0-9]|1[0-9]|2[0-3])\:(0?[0-9]|[1-5][0-9])\:(0?[0-9]|[1-5][0-9]))?$/";
    return preg_match($patten, $datetime_str);
}