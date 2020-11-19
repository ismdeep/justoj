<?php


namespace app\admin\controller;


use app\extra\controller\AdminBaseController;
use think\Env;

class DatabaseBackup extends AdminBaseController {
    public function do_backup_json() {
        // 判断系统是否有 mysqldump 进程在运行
        $results = [];
        exec("ps aux | grep mysqldump | grep -v grep | wc -l", $results);
        if ($results[0] != "0") {
            return json([
                'code' => 500,
                'msg' => '系统正在执行数据库备份，请稍后再试。'
            ]);
        }
        $file_name = sprintf("justoj-%d.sql.gz", time());

        $cmd = sprintf("bash %s %s %s >/dev/null 2>/dev/null &",
            Env::get('database.backup_sh'),
            Env::get('database.backup_dir'),
            $file_name
        );

        $results = [];
        exec($cmd, $results);

        return json([
            'code' => 0,
            'cmd' => $cmd,
            'msg' => 'success',
            'file_name' => $file_name,
            'result' => $results
        ]);
    }

    public function is_mysqldump_running_json() {
        $results = [];
        $cmd = sprintf("ps aux | grep mysqldump | grep -v grep | wc -l");
        exec($cmd, $results);

        $is_running = $results[0] == "0" ? false : true;


        return json([
            'code' => 0,
            'data' => $is_running
        ]);
    }

    public function remove_backup_file_json($filename = '') {
        if (!$filename) {
            return json([
                'code' => 500,
                'msg' => 'filename can not be empty.'
            ]);
        }

        unlink(Env::get('database.backup_dir') . '/' . $filename);
        return json(['code' => 0, 'msg' => 'success']);
    }

}