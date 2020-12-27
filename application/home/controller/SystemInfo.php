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
        $source_code_hash = exec("git log -1 --pretty=format:%H");
        $this->assign('source_code_hash', $source_code_hash);
        return view($this->theme_root . '/system-info-project-hash-part');
    }

    public function data_hash_part() {
        // 获取服务器上数据git hash
        $data_dir = Env::get('config.data_dir');
        $local_hash = exec("cd {$data_dir};git log -1 --pretty=format:%H");
        $this->assign('local_hash', $local_hash);

        // 判断服务器是否有数据未同步
        $local_changed = exec("cd ${data_dir};git status -s");
        $local_changed = $local_changed ? true : false;
        $this->assign('local_changed', $local_changed);
        $judge_clients = (new JudgeClientModel())->order('client_name', 'asc')->select();
        $this->assign('judge_clients', $judge_clients);

        return view($this->theme_root . '/system-info-data-hash-part');
    }

    public function pending_cnt_part() {
        $pending_cnt = (new SolutionModel())->where('result', 0)
            ->count('solution_id');
        $this->assign('pending_cnt', $pending_cnt);

        $rejudging_cnt = (new SolutionModel())->where('result', 1)
            ->count('solution_id');
        $this->assign('rejudging_cnt', $rejudging_cnt);

        $compiling_cnt = (new SolutionModel())->where('result', 2)
            ->count('solution_id');
        $this->assign('compiling_cnt', $compiling_cnt);

        $running_cnt = (new SolutionModel())->where('result', 3)
            ->count('solution_id');
        $this->assign('running_cnt', $running_cnt);

        return view($this->theme_root . '/system-info-pending-cnt-part');
    }

}