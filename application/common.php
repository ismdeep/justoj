<?php

// 应用公共文件
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use think\Env;

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

function send_email($to_email, $to_nick, $title, $content, $is_html) {
    // 实例化
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->CharSet = 'utf-8';
        $mail->Host = Env::get('email.host');
        $mail->SMTPAuth = true;
        $mail->Username = Env::get('email.username');
        $mail->Password = Env::get('email.password');
        $mail->SMTPSecure = 'ssl';
        $mail->Port = Env::get('email.port');

        //Recipients
        $mail->setFrom(Env::get('email.username'), 'JustOJ 管理员');
        $mail->addAddress($to_email, $to_nick);

        //Content
        $mail->isHTML($is_html);
        $mail->Subject = $title;
        $mail->Body    = $content;
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}