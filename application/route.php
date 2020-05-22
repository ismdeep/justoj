<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------


use think\Route;

Route::rule('/',              'home/index/index');
Route::rule('/captcha',       'home/captcha/index');
Route::rule('/login',         'home/auth/sign_in');
Route::post('/login/post',    'home/auth/sign_in_post');
Route::rule('/logout',        'home/auth/sign_out');
Route::rule('/register',      'home/auth/sign_up');
Route::rule('/homework',      'home/homework/index');
Route::rule('/status',        'home/status/index');
Route::rule('/status/ceinfo', 'home/status/get_compile_error_info');
Route::rule('/status/langs',  'home/status/show_languages');

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
];
