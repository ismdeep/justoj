<?php

use think\Route;

Route::rule('/',                         'home/index/index');
Route::rule('/captcha',                  'home/captcha/index');
Route::rule('/login',                    'home/auth/sign_in');
Route::post('/login/post',               'home/auth/sign_in_post');
Route::rule('/logout',                   'home/auth/sign_out');
Route::rule('/register',                 'home/auth/sign_up');
Route::rule('/homework',                 'home/homework/index');

Route::rule('/status/langs',             'home/status/show_languages');
Route::rule('/status',                   'home/status/index');

Route::rule('/solutions/:solution_id/ceinfo',     'home/solution/get_compile_error_info');
Route::rule('/solutions/:solution_id/table-part', 'home/solution/show_table_part');
Route::rule('/solutions/:solution_id',            'home/solution/show_detail');


Route::rule('/pastes/:id',             'home/paste/show_paste_detail');
Route::rule('/paste',                    'home/paste/index');

Route::rule('/profile/email/change',          'home/profile/change_email');
Route::rule('/profile/email/change/json',     'home/profile/change_email_json');
Route::rule('/profile/email/verify/verify_code/json',     'home/profile/verify_email_code_json');
Route::rule('/profile/email/verify/send_code/json',     'home/profile/send_verify_code_json');
Route::rule('/profile/email/verify',     'home/profile/verify_email');
Route::rule('/profile/changepassword',   'home/profile/change_password');
Route::rule('/profile/edit',             'home/profile/edit_my_profile');
Route::rule('/profile',                  'home/profile/index');

Route::rule('/rank',                     'home/rank/index');
Route::rule('/problems/:id/rejudge',     'home/problem/show_rejudge_page');
Route::rule('/problems/:id/recent_solutions','home/problem/get_problem_detail_recent_solution_part');
Route::rule('/problems/:id',             'home/problem/get_problem_detail');
Route::rule('/problems',                 'home/problem/get_problem_list');
Route::rule('/users/:user_id',           'home/user/get_user_detail');

Route::rule('/groups/:id/notifications',            'group/notification/show_group_notifications');
Route::rule('/groups/:id/members/:user_id/source_codes/download', 'group/member/download_member_source_code');
Route::rule('/groups/:id/members',                  'group/member/show_group_members');
Route::rule('/groups/:id/tasks/create/json',        'group/task/create_homework_page_json');
Route::rule('/groups/:id/tasks/create',             'group/task/create_homework_page');
Route::rule('/groups/:id/tasks/copy/group/json',    'group/task/copy_homeworks_from_group_json');
Route::rule('/groups/:id/tasks/copy/group',         'group/task/copy_homeworks_from_group');
Route::rule('/groups/:id/tasks/copy/homework/json', 'group/task/copy_homework_from_homework_json');
Route::rule('/groups/:id/tasks/copy/homework',      'group/task/copy_homework_from_homework');
Route::rule('/groups/:id/tasks',                    'group/task/show_group_tasks');
Route::rule('/groups/:id/join',                     'group/index/show_group_join_page');
Route::rule('/groups/:id',                          'group/index/show_group_detail');
Route::rule('/groups',                              'home/group/get_group_list');

Route::rule('/contests/:id/rank/xls',      'contest/rank/export_xls');
Route::rule('/contests/:id/rank',          'contest/rank/show_rank_page');
Route::rule('/contests/:id/status',        'contest/status/show_status_list');
Route::rule('/contests/:id/enroll',        'contest/enroll/show_contest_enroll_page');

Route::rule('/contests/:id/problems/:pid/recent_solutions', 'contest/problem/show_problem_recent_solutions_part');
Route::rule('/contests/:id/problems/:pid', 'contest/problem/show_problem_detail');
Route::rule('/contests/:id',               'contest/index/show_contest_home_page');
Route::rule('/contests',                   'home/contest/get_contest_list');

/* 找回密码页面 */
Route::rule('/forgot-password/reset-password/:uuid', 'home/forgot_password/reset_password');
Route::rule('/forgot-password/send-email/json', 'home/forgot_password/send_email_json');
Route::rule('/forgot-password/set-password/json', 'home/forgot_password/set_password_json');
Route::rule('/forgot-password',                 'home/forgot_password/index');

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
];
