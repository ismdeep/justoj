<?php


namespace app\home\controller;


use app\api\model\ProblemModel;
use app\api\model\ProblemTagDictModel;
use app\api\model\SolutionModel;
use app\home\common\HomeBaseController;
use think\Request;

class Problem extends HomeBaseController {

    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->assign('nav', 'problem');
    }

    /**
     * @param string $keyword
     * @param string $tag
     * @param int $page
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_problem_list($keyword = '', $tag = '', $page = 1) {
        // 获取总页数
        $max_p = (new ProblemModel)->order('problem_id', 'desc')->limit(1)->select()[0];
        $page_cnt = ceil(($max_p->problem_id - 1000 + 1) / 100.0);
        $this->assign('page_cnt', $page_cnt);

        $page = intval($page);
        $this->assign('page', $page);
        $this->assign('keyword', htmlspecialchars($keyword));

        /***************** 获取当前用户未完成题目列表 >>>> ******************/
        $solved_problem_ids = [];
        $unsolved_problem_ids = [];

        if ($this->login_user) {
            $solved_problems = (new SolutionModel())
                ->where('user_id', $this->login_user->user_id)
                ->where('result', 4)
                ->distinct('problem_id')
                ->field('problem_id')
                ->select();
            foreach ($solved_problems as $solved_problem) {
                $solved_problem_ids [] = $solved_problem->problem_id;
            }

            $submit_problems = (new SolutionModel())
                ->where('user_id', $this->login_user->user_id)
                ->distinct('problem_id')
                ->field('problem_id')
                ->select();
            foreach ($submit_problems as $submit_problem) {
                if (!in_array($submit_problem->problem_id, $solved_problem_ids)) {
                    $unsolved_problem_ids [] = $submit_problem->problem_id;
                }
            }

            $this->assign('unsolved_problem_ids', $unsolved_problem_ids);
        }

        /***************** <<<< 获取当前用户未完成题目列表 ******************/

        /* 题目标签列表 >>>> */
        $problem_tags = (new ProblemTagDictModel())->order('cnt', 'desc')->select();
        $this->assign('problem_tags', $problem_tags);
        /* <<<< 题目标签列表 */


        $problems = (new ProblemModel());

        if ('' != $keyword) {
            $problems = $problems
                ->where('title', 'like', '%' . $keyword . '%');
        }

        if ('' != $tag) {
            $problems = $problems
                ->where('tags', 'like', "%$tag%");
        }

        $problems = $problems->order('problem_id', 'asc');
        $problems = $problems->paginate(100);

        if ('' != $keyword) {
            $problems->appends('keyword', $keyword);
        }
        if ('' != $tag) {
            $problems->appends('tag', $tag);
        }

        /* 题目标签映射 >>>> */
        $problem_tag_dicts = (new ProblemTagDictModel())->select();
        $problem_tag_dict_map = [];
        foreach ($problem_tag_dicts as $problem_tag_dict) {
            /* @var $problem_tag_dict ProblemTagDictModel */
            $problem_tag_dict_map[$problem_tag_dict->tag_id] = $problem_tag_dict->getTagName($this->show_ui_lang);
        }
        $this->assign('problem_tag_dict_map', $problem_tag_dict_map);
        /* <<<< 题目标签映射 */


        foreach ($problems as $problem) {
            $problem->solved = $problem->accepted;

            /* 对题目标签进行渲染 >>>> */
            $tag_list = explode(',', $problem->tags);
            $problem->tag_list = $tag_list;
            if (null == $problem->tags || "" == $problem->tags) {
                $problem->tag_list = [];
            }
            /* <<<< 对题目标签进行渲染 */

            // 获取当前登录用户的解题情况
            $problem->solve_status = 0; // 无状态
            if ($this->login_user) {
                // 判断是否有提交
                if (in_array($problem->problem_id, $unsolved_problem_ids)) {
                    $problem->solve_status = 1; // 有提交
                }
                // 判断是否有AC题目
                if (in_array($problem->problem_id, $solved_problem_ids)) {
                    $problem->solve_status = 2; // 已通过
                }
            }
        }

        $this->assign('problems', $problems);
        return view($this->theme_root . '/problems');

    }

    public function get_problem_detail($id) {
        $problem = ProblemModel::get(['problem_id' => $id]);
        if (!$problem) {
            // TODO 后期加入404 NOT FOUND页面
            $this->redirect('/problems');
        }

        if ('Y' == $problem->defunct && (!$this->login_user || !$this->login_user->is_admin)) {
            // TODO 后期加入无访问权限操作
            $this->redirect('/problems');
        }

        /* 题目标签映射 >>>> */
        $problem_tag_dicts = (new ProblemTagDictModel())->select();
        $problem_tag_dict_map = [];
        foreach ($problem_tag_dicts as $problem_tag_dict) {
            /* @var $problem_tag_dict ProblemTagDictModel */
            $problem_tag_dict_map[$problem_tag_dict->tag_id] = $problem_tag_dict->getTagName($this->show_ui_lang);
        }
        $this->assign('problem_tag_dict_map', $problem_tag_dict_map);
        /* <<<< 题目标签映射 */

        /* 对题目标签进行渲染 >>>> */
        $tag_list = explode(',', $problem->tags);
        $problem->tag_list = $tag_list;
        if (null == $problem->tags || "" == $problem->tags) {
            $problem->tag_list = [];
        }
        /* <<<< 对题目标签进行渲染 */

        // 如果当前用户登录了，判断AC状态
        $problem->ac = false;
        $problem->pending = false;
        if ($this->login_user) {
            if (SolutionModel::where('user_id', $this->login_user->user_id)
                ->where('problem_id', $problem->problem_id)
                ->where('result', 4)
                ->find()) {
                $problem->ac = true;
            } else {
                if (SolutionModel::where('user_id', $this->login_user->user_id)
                    ->where('problem_id', $problem->problem_id)
                    ->find()) {
                    $problem->pending = true;
                }
            }
        }

        $this->assign('problem', $problem);
        $this->assign('allowed_langs', $this->allowed_langs());
        return view($this->theme_root . '/problem');
    }

    public function get_problem_detail_recent_solution_part($id = '') {
        $problem = ProblemModel::get(['problem_id' => $id]);

        /* 获取近期提交记录 */
        if ($this->login_user) {
            $recent_solutions = (new SolutionModel())
                ->where('contest_id', null)
                ->where('user_id', $this->login_user->user_id)
                ->where('problem_id', $problem->problem_id)
                ->order('create_time', 'desc')
                ->limit(5)
                ->select();

            foreach ($recent_solutions as $recent_solution) {
                /* @var $recent_solution SolutionModel */
                $recent_solution->fk();
                $recent_solution->result_text = $this->lang[$recent_solution->result_code];
            }

            $this->assign('recent_solutions', $recent_solutions);
        }

        return view($this->theme_root . "/problem-recent-solutions-part");
    }

    public function show_rejudge_page($id) {
        return view($this->theme_root . '/status-rejudge', ['id' => $id]);
    }

}