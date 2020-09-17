<?php


namespace app\group\controller;


use app\api\model\GroupJoinModel;
use app\api\model\GroupTaskModel;
use app\api\model\SolutionModel;
use app\api\model\SourceCodeModel;
use app\api\model\UserModel;
use app\extra\util\PasswordUtil;
use app\group\common\GroupBaseController;

class Member extends GroupBaseController {

    public function show_group_members() {
        $this->assign('nav', 'members');

        $members = GroupJoinModel::all(['group_id' => $this->group->id]);
        foreach ($members as $member) {
            $member->realname = UserModel::get(['user_id' => $member->user_id])->realname;
        }

        // 判断当前用户是否为此班级管理员
        $is_group_manager = false;
        if ($this->login_user && $this->group->owner_id == $this->login_user->user_id) $is_group_manager = true;
        $this->assign('is_group_manager', $is_group_manager);


        $this->assign('members', $members);
        $this->assign('group', $this->group);
        return view($this->theme_root . '/group-members');
    }


    /**
     * Download member source code
     *
     * @param string $user_id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function download_member_source_code($user_id = '') {
        $this->assign('nav', 'tasks');

        // 判断当前用户是否为此班级管理员
        $is_group_manager = false;
        if ($this->login_user && $this->group->owner_id == $this->login_user->user_id) $is_group_manager = true;
        $this->assign('is_group_manager', $is_group_manager);

        intercept(!$is_group_manager && $user_id != $this->login_user->user_id, 'YOU DONT HAVE PERMISSION FOR THIS OPERATION.');
        intercept('' == $user_id, 'error');
        $user = (new UserModel())->where('user_id', $user_id)->find();
        intercept(null == $user, "User not found.");



        $langs = config('langs');
        $langs_map = [];
        foreach ($langs as $lang) {
            $langs_map[$lang['id']] = $lang;
        }

        $result_code = array(
            'pending',
            'rejuding',
            'compiling',
            'running',
            'accepted',
            'presentation_error',
            'wrong_answer',
            'time_limited_error',
            'memory_limited_error',
            'output_limited_error',
            'runtime_error',
            'compile_error',
            'co',
            'tr',
            'submit_ok'
        );


        $member = (new GroupJoinModel())
            ->where('group_id', $this->group->id)
            ->where('user_id', $user_id)
            ->find();

        intercept(null == $member, 'User not found in this group.');


        $cache_id = PasswordUtil::random_string("0123456789abcdef", 32);

        $cache_file_path = '/opt/justoj-data-cache/' . $cache_id . ".zip";
        $octet_file_name = $user_id . "_" . $user->realname . "_" . $this->group->name . ".zip";

        $zip = new \ZipArchive();
        $zip->open($cache_file_path, \ZIPARCHIVE::CREATE);

        /* 获取班级作业列表 */
        $group_tasks = (new GroupTaskModel())
            ->where('group_id', $this->group->id)
            ->select();

        $empty_flag = true;
        foreach ($group_tasks as $group_task) {
            $solutions = (new SolutionModel())
                ->where('user_id', $user_id)
                ->where('contest_id', $group_task->contest_id)
                ->select();
            foreach ($solutions as $solution) {
                $source_code = (new SourceCodeModel())
                    ->where('solution_id', $solution->solution_id)->find();
                $zip->addFromString($group_task->contest_id . "_" . $group_task->title . "_" . $solution->solution_id . "_" . $result_code[$solution->result] . "." . $langs_map[$solution->language]['suffix'], $source_code->source);
                $empty_flag = false;
            }
        }

        if ($empty_flag) {
            $zip->addFromString('EMPTY', 'EMPTY');
        }

        $zip->close();
        header('Content-Type: application/octet-stream');
        header('Accept-Ranges: bytes');
        header('Accept-Length: ' . filesize($cache_file_path));
        header('Content-Disposition: attachment; filename=' . $octet_file_name);
        ob_clean();
        flush();


        $filesize = filesize($cache_file_path);
        //设置分流
        $buffer = 1024;
        //来个文件字节计数器
        $count = 0;
        $fp = fopen($cache_file_path, 'r');//只读方式打开
        while (!feof($fp) && ($filesize - $count > 0)) {
            $data = fread($fp, $buffer);
            echo $data;//传数据给浏览器端
        }
        fclose($fp);
    }
}