<?php


namespace app\admin\controller;


use app\admin\common\AdminBaseController;
use app\api\model\JudgeClientModel;
use think\Env;

class JudgeClient extends AdminBaseController {

    public function index() {
        return view('index');
    }

    public function index_part() {
        // 获取服务器上数据git hash
        $data_dir = Env::get('config.data_dir');
        $local_hash = exec("cd {$data_dir};git log -1 --pretty=format:%H");

        // 判断服务器是否有数据未同步
        $local_changed = exec("cd ${data_dir};git status -s");
        $local_changed = $local_changed ? true : false;

        $judge_clients = (new JudgeClientModel())
            ->order('client_name', 'asc')
            ->select();

        return view('index_part', [
            'local_hash' => $local_hash,
            'local_changed' => $local_changed,
            'clients' => $judge_clients]);
    }
}