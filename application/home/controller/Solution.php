<?php


namespace app\home\controller;


use app\api\model\CompileInfoModel;
use app\api\model\SolutionModel;
use app\api\model\SourceCodeModel;
use app\extra\controller\UserBaseController;

class Solution extends UserBaseController {

    /**
     * 显示提交结果
     *
     * @param string $solution_id
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function show_detail($solution_id) {
        intercept('' == $solution_id, 'invalid');
        $solution = (new SolutionModel())->where('solution_id', $solution_id)->find();
        intercept(null == $solution, 'invalid');

        // 检查是否有访问权限
        if (!($this->is_administrator || ($this->loginuser && $solution->user_id == $this->loginuser->user_id))) {
            return $this->lang['do_not_have_privilege'];
        }

        $solution->fk();
        $solution->result_text = $this->lang[$solution->result_code];
        $source_code = (new SourceCodeModel())->where('solution_id', $solution_id)->find();
        intercept(null == $source_code, 'invalid');
        $source_code->source = htmlspecialchars($source_code->source);
        return view($this->theme_root . '/status-solution', [
            'solution' => $solution,
            'source_code' => $source_code,
            'result_map' => SolutionModel::$result_map,
        ]);
    }

    public function get_compile_error_info($solution_id) {
        // 获取solution信息
        $solution = SolutionModel::get(['solution_id' => $solution_id]);

        // TODO 权限检查 是否管理员、是否本人
        intercept(null == $this->loginuser, $this->lang['not_login']);
        intercept(!$this->is_administrator && $this->loginuser->user_id != $solution->user_id, $this->lang['do_not_have_privilege']);

        $solution->fk();
        $this->assign('solution', $solution);

        $source_code = SourceCodeModel::get(['solution_id' => $solution_id]);
        $source_code->source = str_replace('<', '&lt;', $source_code->source);
        $source_code->source = str_replace('>', '&gt;', $source_code->source);
        $this->assign('source_code', $source_code);

        $compile_info = CompileInfoModel::get(['solution_id' => $solution_id]);
        intercept(null == $compile_info, '没有编译信息');
        if ($compile_info) {
            $compile_info->error = str_replace('<', '&lt;', $compile_info->error);
            $compile_info->error = str_replace('>', '&gt;', $compile_info->error);
            $this->assign('compile_info', $compile_info);
        }
        return view($this->theme_root . '/status-ceinfo');
    }
}