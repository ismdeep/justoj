<?php


namespace app\home\controller;


use app\api\model\CompileInfoModel;
use app\api\model\SolutionModel;
use app\api\model\SourceCodeModel;
use app\extra\controller\UserBaseController;
use think\Request;

class Status extends UserBaseController {

    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->assign('nav', 'status');
    }

    /**
     * @param string $run_id
     * @param string $username
     * @param string $problem_id
     * @param string $result
     * @param string $language
     * @return \think\response\View
     * @throws \think\exception\DbException
     */
    public function index($run_id = '', $username = '', $problem_id = '', $result = '', $language = '') {
        $this->assign('run_id', htmlspecialchars($run_id));
        $this->assign('username', htmlspecialchars($username));
        $this->assign('problem_id', htmlspecialchars($problem_id));
        $this->assign('result', htmlspecialchars($result));
        $this->assign('language', intval($language));
        $this->assign('allowed_langs', $this->allowed_langs());
        if ('' != $run_id) {
            $solutions = (new SolutionModel)->where('solution_id', $run_id)->paginate(10);
            $solutions->appends('run_id', $run_id);
            $this->assign('solutions', $solutions);
            return view($this->theme_root . '/status');
        }

        $where = (new SolutionModel());

        if ('' != $username) {
            $where = $where->where(['user_id' => $username]);
        }
        if ('' != $problem_id) {
            $where = $where->where(['problem_id' => $problem_id]);
        }
        if ('' != $result) {
            $where = $where->where(['result' => $result]);
        }
        if ('' != $language) {
            $where = $where->where(['language' => $language]);
        }

        $solutions = $where->order('solution_id', 'desc')->paginate(10);
        $solutions->appends('run_id', '');
        $solutions->appends('username', $username);
        $solutions->appends('problem_id', $problem_id);
        $solutions->appends('result', $result);
        $solutions->appends('language', $language);
        $this->assign('solutions', $solutions);
        return view($this->theme_root . '/status');
    }

    public function get_compile_error_info($id) {
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

    public function show_languages() {
        return view($this->theme_root . '/status-langs');
    }

}