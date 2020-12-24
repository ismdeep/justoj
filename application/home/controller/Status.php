<?php


namespace app\home\controller;


use app\api\model\CompileInfoModel;
use app\api\model\SolutionModel;
use app\api\model\SourceCodeModel;
use app\home\common\HomeBaseController;
use think\Request;

class Status extends HomeBaseController {

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

        if ($username == '' && (!$this->login_user || !$this->login_user->is_root) && $this->request->get('page') > 1) {
            $this->redirect('/status');
        }

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

        $solutions = $where
            ->where('contest_id', null)
            ->order('solution_id', 'desc')->paginate(10);
        $solutions->appends('run_id', '');
        $solutions->appends('username', $username);
        $solutions->appends('problem_id', $problem_id);
        $solutions->appends('result', $result);
        $solutions->appends('language', $language);
        $this->assign('solutions', $solutions);
        return view($this->theme_root . '/status');
    }

    public function show_languages() {
        return view($this->theme_root . '/status-langs');
    }

}