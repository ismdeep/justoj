<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2019-02-02
 * Time: 14:10
 */

namespace app\status\controller;


use app\api\model\SolutionModel;
use app\api\model\SourceCodeModel;
use app\extra\controller\UserBaseController;

class Solution extends UserBaseController {
    /**
     * 显示提交结果
     * @param string $solution_id
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function show($solution_id = '') {
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
            'source_code' => $source_code
        ]);
    }
}