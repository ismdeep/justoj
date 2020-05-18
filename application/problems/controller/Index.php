<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/6
 * Time: 20:31
 */

namespace app\problems\controller;


use app\api\model\ProblemModel;
use app\api\model\ProblemTagDictModel;
use app\api\model\SolutionModel;
use app\extra\controller\UserBaseController;
use think\Request;

class Index extends UserBaseController {
    public $max_p;
    public $page_cnt;

    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->assign('nav', 'problem');
        // 获取总页数
        $this->max_p = (new ProblemModel)->order('problem_id', 'desc')->limit(1)->select()[0];
        $this->page_cnt = ceil(($this->max_p->problem_id - 1000 + 1) / 100.0);
        $this->assign('page_cnt', $this->page_cnt);
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
    public function index($keyword = '', $tag = '', $page = 1) {
        $page = intval($page);
        $this->assign('page', $page);
        $this->assign('keyword', htmlspecialchars($keyword));

        /***************** 获取当前用户未完成题目列表 >>>> ******************/
        $solved_problem_ids = [];
        $unsolved_problem_ids = [];

        if ($this->loginuser) {
            $solved_problems = (new SolutionModel())
                ->where('user_id', $this->loginuser->user_id)
                ->where('result', 4)
                ->distinct('problem_id')
                ->field('problem_id')
                ->select();
            foreach ($solved_problems as $solved_problem) {
                $solved_problem_ids [] = $solved_problem->problem_id;
            }

            $submit_problems = (new SolutionModel())
                ->where('user_id', $this->loginuser->user_id)
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
            $problem_tag_dict_map[$problem_tag_dict->tag_id] = $problem_tag_dict->tag_name;
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
            if ($this->loginuser) {
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
        $this->assign('is_login', $this->is_login);
        return view($this->theme_root . '/problems');
    }
}