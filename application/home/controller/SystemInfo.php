<?php


namespace app\home\controller;


use app\api\model\JudgeClientModel;
use app\api\model\SolutionModel;
use app\home\common\HomeBaseController;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\response\View;

class SystemInfo extends HomeBaseController {
    public function index() {
        return view($this->theme_root . '/system-info');
    }

    public function project_hash_part() {
        // Read From /justoj-version
        $str = "unknown";
        $version_file_path = '/justoj-version';
        if (file_exists($version_file_path)) {
            $str = file_get_contents($version_file_path);
        }

        $this->assign('project_version', $str);
        return view($this->theme_root . '/system-info-project-hash-part');
    }

    /**
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function data_hash_part() {
        $data_dir = config('data_dir');

        $branch_name = exec("cd $data_dir;git symbolic-ref --short -q HEAD");
        $this->assign('branch_name', $branch_name);

        $release_date = exec("cd $data_dir;git log -1 --pretty=format:%at");
        $release_date = date('Y-m-d H:i:s', $release_date);
        $this->assign('release_date', $release_date);

        // 获取服务器上数据git hash

        $local_hash = exec("cd " . $data_dir . ";git log -1 --pretty=format:%H");
        $local_hash = substr($local_hash, 0, 7);
        $this->assign('local_hash', $local_hash);

        // 判断服务器是否有数据未同步
        $local_changed = exec("cd " . $data_dir . ";git status -s");
        $local_changed = (bool)$local_changed;
        $this->assign('local_changed', $local_changed);
        $judge_clients = (new JudgeClientModel())->order('client_name', 'asc')->select();
        foreach ($judge_clients as $client) {
            /* @var $client JudgeClientModel */
            $client->data_git_hash = substr($client->data_git_hash, 0, 7);
        }
        $this->assign('judge_clients', $judge_clients);

        return view($this->theme_root . '/system-info-data-hash-part');
    }

    /**
     * Get Pending Cnt HTML Part
     *
     * @return View
     * @throws Exception
     */
    public function pending_cnt_part() {
        $pending_cnt = (new SolutionModel())->where('result', SolutionModel::RESULT_PENDING)
            ->count('solution_id');
        $this->assign('pending_cnt', $pending_cnt);

        $rejudging_cnt = (new SolutionModel())->where('result', SolutionModel::RESULT_REJUDING)
            ->count('solution_id');
        $this->assign('rejudging_cnt', $rejudging_cnt);

        $compiling_cnt = (new SolutionModel())->where('result', SolutionModel::RESULT_COMPILING)
            ->count('solution_id');
        $this->assign('compiling_cnt', $compiling_cnt);

        $running_cnt = (new SolutionModel())->where('result', SolutionModel::RESULT_RUNNING)
            ->count('solution_id');
        $this->assign('running_cnt', $running_cnt);

        return view($this->theme_root . '/system-info-pending-cnt-part');
    }

    public function solution_statistics_part() {
        return view($this->theme_root . '/system-info-solution-statistics-part');
    }
}