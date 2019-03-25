<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/10
 * Time: 12:44
 */

namespace app\status\controller;


use app\api\model\CompileInfoModel;
use app\api\model\SolutionModel;
use app\api\model\SourceCodeModel;
use app\extra\controller\UserBaseController;

class Ceinfo extends UserBaseController
{
    public function index($id)
    {
        // 获取solution信息
        $solution = SolutionModel::get(['solution_id' => $id]);

        // TODO 权限检查 是否管理员、是否本人
        intercept(null == $this->loginuser, $this->lang['not_login']);
        intercept(!$this->is_administrator && $this->loginuser->user_id != $solution->user_id, $this->lang['do_not_have_privilege']);

        $solution->fk();
        $this->assign('solution', $solution);

        $source_code = SourceCodeModel::get(['solution_id' => $id]);
        $source_code->source = str_replace('<', '&lt;', $source_code->source);
        $source_code->source = str_replace('>', '&gt;', $source_code->source);
        $this->assign('source_code', $source_code);

        $compile_info = CompileInfoModel::get(['solution_id' => $id]);
        intercept(null == $compile_info, '没有编译信息');
        if ($compile_info) {
            $compile_info->error = str_replace('<', '&lt;', $compile_info->error);
            $compile_info->error = str_replace('>', '&gt;', $compile_info->error);
            $this->assign('compile_info', $compile_info);
        }
        return view($this->theme_root . '/status-ceinfo');
    }
}