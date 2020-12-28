<?php


namespace app\home\controller;


use app\api\model\JudgeClientModel;
use app\api\model\SolutionModel;
use app\home\common\HomeBaseController;
use think\Env;

class SystemInfo extends HomeBaseController {
    public function index() {
        return view($this->theme_root . '/system-info');
    }

    public function project_hash_part() {
        $branch_name = exec("git symbolic-ref --short -q HEAD");
        $this->assign('branch_name', $branch_name);

        $release_date = exec("git log -1 --pretty=format:%at");
        $release_date = date('Y-m-d H:i:s', $release_date);
        $this->assign('release_date', $release_date);

        $source_code_hash = exec("git log -1 --pretty=format:%H");
        $source_code_hash = substr($source_code_hash, 0, 7);
        $this->assign('source_code_hash', $source_code_hash);

        return view($this->theme_root . '/system-info-project-hash-part');
    }

    public function data_hash_part() {

        $data_dir = Env::get('config.data_dir');

        $branch_name = exec("cd {$data_dir};git symbolic-ref --short -q HEAD");
        $this->assign('branch_name', $branch_name);

        $release_date = exec("cd {$data_dir};git log -1 --pretty=format:%at");
        $release_date = date('Y-m-d H:i:s', $release_date);
        $this->assign('release_date', $release_date);

        // 获取服务器上数据git hash

        $local_hash = exec("cd {$data_dir};git log -1 --pretty=format:%H");
        $local_hash = substr($local_hash, 0, 7);
        $this->assign('local_hash', $local_hash);

        // 判断服务器是否有数据未同步
        $local_changed = exec("cd ${data_dir};git status -s");
        $local_changed = $local_changed ? true : false;
        $this->assign('local_changed', $local_changed);
        $judge_clients = (new JudgeClientModel())->order('client_name', 'asc')->select();
        foreach ($judge_clients as $client) {
            /* @var $client JudgeClientModel */
            $client->data_git_hash = substr($client->data_git_hash, 0, 7);
        }
        $this->assign('judge_clients', $judge_clients);

        return view($this->theme_root . '/system-info-data-hash-part');
    }

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

}