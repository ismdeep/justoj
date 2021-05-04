<?php

namespace app\admin\controller;

use app\api\model\ContestModel;
use app\api\model\ProblemModel;
use app\api\model\SolutionModel;
use app\api\model\UserModel;
use app\admin\common\AdminBaseController;
use think\Config;
use think\Db;
use think\Env;
use think\Exception;


class Index extends AdminBaseController {
    public function index() {
        return view();
    }

    public function index2() {
        return view();
    }

    public function welcome() {

        /** 题目数量 **/
        try {
            $problem_count = (new ProblemModel())->count();
        } catch (Exception $e) {
            $problem_count = '暂无信息';
        }
        /** 比赛数量 **/
        try {
            $contest_count = (new ContestModel())->where(['type' => 0])->count();
        } catch (Exception $e) {
            $contest_count = '暂无信息';
        }
        /** 作业数量 **/
        try {
            $homework_count = (new ContestModel())->where(['type' => 1])->count();
        } catch (Exception $e) {
            $homework_count = '暂无信息';
        }
        /** 用户数量 **/
        try {
            $user_count = (new UserModel())->count();
        } catch (Exception $e) {
            $user_count = '暂无信息';
        }
        /** 提交数量 **/
        try {
            $solution_count = (new SolutionModel())->count();
        } catch (Exception $e) {
            $solution_count = '暂无信息';
        }
        /** AC数量 **/
        try {
            $ac_count = (new SolutionModel())->where(['result' => 4])->count();
        } catch (Exception $e) {
            $ac_count = '暂无信息';
        }
        /** MySQL 数据库版本号 **/
        $mysql_version = Db::query("select version() as ver")[0]['ver'];

        /** / 硬盘剩余信息 **/
        $free_space = 'N/A';
        try {
            $free_space = round(disk_free_space('/') / 1024 / 1024 / 1024, 2) . 'G';
        } catch (Exception $e) {
            $free_space = 'N/A';
        }

        /** /home 硬盘剩余信息 **/
        $free_space_home = 'N/A';
        try {
            $free_space_home = round(disk_free_space('/home') / 1024 / 1024 / 1024, 2) . 'G';
        }catch (Exception $e) {
            $free_space_home = 'N/A';
        }


        $this->assign('problem_count', $problem_count);
        $this->assign('contest_count', $contest_count);
        $this->assign('homework_count', $homework_count);
        $this->assign('solution_count', $solution_count);
        $this->assign('user_count', $user_count);
        $this->assign('ac_count', $ac_count);
        $this->assign('free_space', $free_space);
        $this->assign('free_space_home', $free_space_home);
        $this->assign('mysql_version', $mysql_version);
        $this->assign('php_version', PHP_VERSION);
        $this->assign('thinkphp_version', THINK_VERSION);
        $this->assign('admin_email', Config::get('admin_email'));

        return view();
    }
}
