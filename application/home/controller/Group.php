<?php


namespace app\home\controller;


use app\api\model\ContestModel;
use app\api\model\GroupAnnounceModel;
use app\api\model\GroupJoinModel;
use app\api\model\GroupModel;
use app\api\model\GroupTaskModel;
use app\api\model\SolutionModel;
use app\api\model\SourceCodeModel;
use app\api\model\UserModel;
use app\extra\controller\UserBaseController;
use app\extra\util\PasswordUtil;
use think\Db;
use think\Request;

class Group extends UserBaseController {

    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->assign('nav', 'groups');
    }

    /**
     * 所有group分页
     *
     * @param string $keyword 关键字搜索
     * @param string $filter 筛选，空为所有班级，1我创建的班级，2我加入的班级
     *
     * @return \think\response\View
     * @throws \think\exception\DbException
     */
    public function get_group_list($keyword = '', $filter = '') {
        $groups = new GroupModel();
        $groups = $groups->where('deleted', 0);

        if ($keyword) {
            $groups = $groups->where('name', 'like', "%{$keyword}%");
        }

        if (in_array($filter, [1,2]) && !$this->loginuser) {
            $this->redirect('/login?redirect=' . urlencode('/groups'));
        }

        switch ($filter) {
            case 1:
                $groups = $groups->where(['owner_id' => $this->loginuser->user_id]);
                break;
            case 2:
                $groups = $groups->where('id', 'in', function($query){
                    $query->table('group_join')
                        ->where([
                            'user_id' => $this->loginuser->user_id,
                            'deleted' => 0
                        ])->field('group_id');
                });
                break;
        }

        $groups = $groups->order('id', 'desc')->paginate(10);
        if ($this->loginuser) {
            foreach ($groups as $group) $group->loginuser_group_join = GroupJoinModel::get(['user_id' => $this->loginuser->user_id, 'group_id' => $group->id]);
        } else {
            foreach ($groups as $group) $group->loginuser_group_join = null;
        }

        $groups->appends(['keyword' => $keyword, 'filter' => $filter]);
        $this->assign('keyword', htmlspecialchars($keyword));
        $this->assign('groups', $groups);
        $this->assign('filter', $filter);
        return view($this->theme_root . '/groups');
    }

    public function show_group_detail($id) {
        $this->assign('nav', 'home');
        // 判断是否登录，如果没有登录直接跳转到登录页面
        if (!$this->loginuser) $this->redirect('/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));

        // 获取group信息
        $group = GroupModel::get(['id' => $id]);
        intercept($group == null, 'NOT EXISTS');
        intercept($group->deleted == 1, 'DELETED');
        $this->assign('group', $group);

        return view($this->theme_root . '/group');
    }

    public function show_group_notifications($id) {
        $this->assign('nav', 'notifications');

        // 获取group信息
        $group = GroupModel::get(['id' => $id]);
        intercept($group == null, 'NOT EXISTS');
        intercept($group->deleted == 1, 'DELETED');

        // 获取当前班级之公告
        $notifications = GroupAnnounceModel::all(['group_id' => $group->id]);
        $this->assign('notifications', $notifications);
        $this->assign('group', $group);
        return view($this->theme_root . '/group-notifications');
    }

    public function show_group_members($id) {
        $this->assign('nav', 'members');

        // 获取group信息
        $group = GroupModel::get(['id' => $id]);
        intercept($group == null, 'NOT EXISTS');
        intercept($group->deleted == 1, 'DELETED');

        $members = GroupJoinModel::all(['group_id' => $id]);
        foreach ($members as $member) {
            $member->realname = UserModel::get(['user_id' => $member->user_id])->realname;
        }

        // 判断当前用户是否为此班级管理员
        $is_group_manager = false;
        if ($this->loginuser && $group->owner_id == $this->loginuser->user_id) $is_group_manager = true;
        $this->assign('is_group_manager', $is_group_manager);


        $this->assign('members', $members);
        $this->assign('group', $group);
        return view($this->theme_root . '/group-members');
    }


    /**
     * Group task page
     *
     * @return \think\response\View
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function show_group_tasks($id) {
        $this->assign('nav', 'tasks');

        // 获取group信息
        $group = GroupModel::get(['id' => $id]);
        intercept($group == null, 'NOT EXISTS');
        intercept($group->deleted == 1, 'DELETED');

        $members = GroupJoinModel::all(['group_id' => $id]);
        foreach ($members as $member) {
            $member->realname = UserModel::get(['user_id' => $member->user_id])->realname;
        }

        $tasks = GroupTaskModel::all(['group_id' => $group->id]);

        /* 获取总人数和完成作业的人数 */
        $user_ids = [];
        $users = (new GroupJoinModel())
            ->where('group_id', $group->id)
            ->select();
        foreach ($users as $user) {
            $user_ids [] = $user->user_id;
        }
        $group_member_cnt = sizeof($users);

        $this->assign('group_member_cnt', $group_member_cnt);

        foreach ($tasks as $task) {
            $task->contest = ContestModel::get(['contest_id' => $task->contest_id]);
            // 获取这个比赛题目数量
            $task->contest->problem_cnt = Db::query("select count(problem_id) as cnt from contest_problem where contest_id=" . $task->contest_id)[0]['cnt'];
            // 获得登录用户A题数量
            $task->contest->loginuser_ac_cnt = Db::query("select count(DISTINCT problem_id) as cnt from solution where contest_id=" . $task->contest_id . " and user_id='" . $this->loginuser->user_id . "' and result=4")[0]['cnt'];

            $task->ac_member_cnt = (new SolutionModel())
                ->where('contest_id', $task->contest_id)
                ->where('result', 4)
                ->whereIn('user_id', $user_ids)
                ->count('distinct user_id');

        }
        $this->assign('tasks', $tasks);
        $this->assign('group', $group);
        return view($this->theme_root . '/group-tasks');
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
    function download_member_source_code($id, $user_id = '') {
        $this->assign('nav', 'tasks');

        // 获取group信息
        $group = GroupModel::get(['id' => $id]);
        intercept($group == null, 'NOT EXISTS');
        intercept($group->deleted == 1, 'DELETED');


        // 判断当前用户是否为此班级管理员
        $is_group_manager = false;
        if ($this->loginuser && $group->owner_id == $this->loginuser->user_id) $is_group_manager = true;
        $this->assign('is_group_manager', $is_group_manager);

        intercept(!$is_group_manager && $user_id != $this->loginuser->user_id, 'YOU DONT HAVE PERMISSION FOR THIS OPERATION.');
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
            ->where('group_id', $group->id)
            ->where('user_id', $user_id)
            ->find();

        intercept(null == $member, 'User not found in this group.');


        $cache_id = PasswordUtil::random_string("0123456789abcdef", 32);

        $cache_file_path = '/opt/justoj-data-cache/' . $cache_id . ".zip";
        $octet_file_name = $user_id . "_" . $user->realname . "_" . $group->name . ".zip";

        $zip = new \ZipArchive();
        $zip->open($cache_file_path, \ZIPARCHIVE::CREATE);

        /* 获取班级作业列表 */
        $group_tasks = (new GroupTaskModel())
            ->where('group_id', $group->id)
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


    public function show_group_join_page($id) {
        // 判断是否登录，如果没有登录则直接跳转至登录页面。
        if (!$this->loginuser) $this->redirect('/login?redirect=' . urlencode("/groups/{$id}/join"));

        $group = GroupModel::get(['id' => $id]);
        if (!$group) return view('not_found_group');

        // 判断当前用户是否有访问班级权限
        // 判断当前用户是否为此班级管理员
        $is_group_manager = false;
        if ($this->loginuser && $group->owner_id == $this->loginuser->user_id) $is_group_manager = true;
        $this->assign('is_group_manager', $is_group_manager);

        // 判断当前用户是否有访问权限
        $have_permission = false;
        // 判断当前用户与班级是否有group_join,并且status=1
        if ($is_group_manager) $have_permission = true;

        $group_join = GroupJoinModel::get(['user_id' => $this->loginuser->user_id, 'group_id' => $id]);
        if ($group_join && $group_join->status == 1) $have_permission = true;

        if ($have_permission) $this->redirect('/groups/' . $id);

        $this->assign('group', $group);
        // 如果此班级为public，则询问学生是否加入。(type: 0public 1private)
        // 如果此班级为private但是没有密码，则询问学生是否加入，点击加入后告知学生需要等待管理员审核。
        // 如果此班级为private且有密码，则询问学生加入密码，密码正确则直接加入此班级。
        return view($this->theme_root . '/group-join');
    }
}