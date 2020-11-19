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
        $file_name = sprintf("justoj-%d.sql", time());

        $mysql_port = Env::get('database.port', '3306');
        $mysql_hostname = Env::get('database.hostname');
        $mysql_database = Env::get('database.database');
        $mysql_username = Env::get('database.username');
        $mysql_password = Env::get('database.password');

        $cmd = sprintf("mysqldump -h%s -P%s -u%s -p%s %s | gzip > %s/%s.gz &",
            $mysql_hostname,
            $mysql_port,
            $mysql_username,
            $mysql_password,
            $mysql_database,
            Env::get('database.backup_output_path'),
            $file_name
        );

        exec($cmd);

        return json([
            'code' => 0,
            'msg' => 'success',
            'file_name' => $file_name . ".gz"
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

        unlink(Env::get('database.backup_output_path') . '/' . $filename);
        return json(['code' => 0, 'msg' => 'success']);
    }

}