<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/15
 * Time: 10:54 PM
 */

namespace app\api\controller;


use app\api\model\ProblemModel;
use app\extra\controller\ApiBaseController;
use think\Exception;

class Problem extends ApiBaseController {
    /**
     * 禁用题目之api接口
     * @param $problem_id
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function disable($problem_id) {
        if (!$this->is_administrator) return json(['status' => 'error', 'msg' => $this->lang['do_not_have_privilege']]);
        $problem = ProblemModel::get(['problem_id' => $problem_id]);
        $problem->defunct = 'Y';
        $problem->save();
        return json(['status' => 'success']);
    }

    /**
     * 启用题目之api接口
     * @param $problem_id
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function enable($problem_id) {
        if (!$this->is_administrator) return json(['status' => 'error', 'msg' => $this->lang['do_not_have_privilege']]);
        $problem = ProblemModel::get(['problem_id' => $problem_id]);
        $problem->defunct = 'N';
        $problem->save();
        return json(['status' => 'success']);
    }

    public function delete_files($problem_id, $file_names) {
        // 需要管理员权限
        if (!$this->is_administrator) {
            return json(['status' => 'error', 'msg' => $this->lang['do_not_have_privilege']]);
        }

        foreach ($file_names as $file_name) {
            try {
                unlink(config('data_dir') . '/' . $problem_id . '/' . $file_name);
            } catch (Exception $e) {
            }
        }
        return json(['status' => 'success']);
    }

    /**
     * 问题详情
     */
    public function details($problem_id) {
        // 判断问题是否存在
        $problem = ProblemModel::get(['problem_id' => $problem_id]);
        if (null == $problem) return json(['status' => 'error', 'msg' => $this->lang['problem_not_exists']]);

        if ('Y' == $problem->defunct && !$this->is_administrator) return json(['status' => 'error', 'msg' => $this->lang['problem_not_exists']]);

        return json(['status' => 'success', 'data' => $problem]);
    }

    /**
     * 问题详情by列表
     * @param string $problem_ids
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function details_by_list($problem_ids = '') {
        if ('' == $problem_ids) {
            return json(['status' => 'success', 'data' => []]);
        }
        // 判断输入字符合法性
        if (strchr($problem_ids, '，')) {
            return json(['status' => 'error', 'msg' => '请使用英文逗号,']);
        }

        // 判断这些题目是否都存在
        $pids = explode(',', $problem_ids);
        $problems = array();
        foreach ($pids as $pid) {
            $problem = ProblemModel::get(['problem_id' => $pid]);
            if (!$problem) {
                return json(['status' => 'error', 'msg' => 'Problem not exists. id: ' . $pid]);
            }
            array_push($problems, $problem);
        }

        return json(['status' => 'success', 'data' => $problems]);
    }
}