<?php

use think\Route;

Route::rule('/',                       'home/index/index');
Route::rule('/captcha',                'home/captcha/index');
Route::rule('/login',                  'home/auth/sign_in');
Route::post('/login/post',             'home/auth/sign_in_post');
Route::rule('/logout',                 'home/auth/sign_out');
Route::rule('/register',               'home/auth/sign_up');
Route::rule('/homework',               'home/homework/index');
Route::rule('/status',                 'home/status/index');
Route::rule('/status/ceinfo',          'home/status/get_compile_error_info');
Route::rule('/status/langs',           'home/status/show_languages');
Route::rule('/paste',                  'home/paste/index');
Route::rule('/profile',                'home/profile/index');
Route::rule('/profile/changepassword', 'home/profile/change_password');
Route::rule('/profile/edit',           'home/profile/edit_my_profile');
Route::rule('/rank',                   'home/rank/index');
Route::rule('/problems/:id',           'home/problem/get_problem_detail');
Route::rule('/problems',               'home/problem/get_problem_list');
Route::rule('/users/:user_id',         'home/user/get_user_detail');
Route::rule('/groups',                 'home/group/get_group_list');


return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
];
