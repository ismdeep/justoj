<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/13
 * Time: 10:37 PM
 */

namespace app\group\controller;


use app\api\model\GroupJoinModel;
use app\api\model\GroupTaskModel;
use app\api\model\SolutionModel;
use app\api\model\SourceCodeModel;
use app\api\model\UserModel;
use app\extra\controller\GroupBaseController;
use app\extra\util\PasswordUtil;
use think\Request;

class Members extends GroupBaseController
{


    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->assign('nav', 'members');
    }

    /**
     * 班级成员页面
     */
    public function index()
    {
        $members = GroupJoinModel::all(['group_id' => $this->group->id]);
        foreach ($members as $member) {
            $member->realname = UserModel::get(['user_id' => $member->user_id])->realname;
        }
        $this->assign('members', $members);
        return view($this->theme_root . '/group-members');
    }

    /**
     * Download member source code
     *
     * @param string $user_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function download_member_source_code($user_id = '')
    {
        intercept(!$this->is_group_manager && $user_id != $this->loginuser->user_id, 'YOU DONT HAVE PERMISSION FOR THIS OPERATION.');
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
