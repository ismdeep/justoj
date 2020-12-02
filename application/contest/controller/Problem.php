<?php


namespace app\contest\controller;


use app\api\model\ContestProblemModel;
use app\api\model\ProblemModel;
use app\api\model\SolutionModel;
use app\contest\common\ContestBaseController;

class Problem extends ContestBaseController {

    /**
     * 题目页面
     *
     * @param $pid
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function show_problem_detail($pid) {
        /* 判断比赛是否开始 */
        if (!$this->contest_started) {
            $this->redirect("/contests/{$this->contest_id}");
        }

        $contest_problem = ContestProblemModel::get(['contest_id' => $this->contest->contest_id, 'num' => $pid]);
        $contest_problem->ac = false;
        $contest_problem->ac2 = false;
        $contest_problem->pending = false;
        if ($this->login_user) {
            if (SolutionModel::
            where("contest_id", $contest_problem->contest_id)
                ->where('user_id', $this->login_user->user_id)
                ->where('problem_id', $contest_problem->problem_id)
                ->where('in_date', '>', $this->contest->start_time)
                ->where('in_date', '<', $this->contest->end_time)
                ->where('result', 4)
                ->find()) {
                $contest_problem->ac = true;
            } else {
                if (SolutionModel::
                where("contest_id", $contest_problem->contest_id)
                    ->where('user_id', $this->login_user->user_id)
                    ->where('problem_id', $contest_problem->problem_id)
                    ->where('in_date', '>', $this->contest->start_time)
                    ->where('in_date', '<', $this->contest->end_time)
                    ->find()) {
                    $contest_problem->pending = true;
                }
            }
        }
        $problem = ProblemModel::get(['problem_id' => $contest_problem->problem_id]);

        $this->assign('contest_problem', $contest_problem);
        $this->assign('problem', $problem);

        // 获取比赛题目列表
        $contest_problems = ContestProblemModel::where('contest_id', $this->contest->contest_id)->order('num', 'asc')->select();
        foreach ($contest_problems as $problem) {
            $contest_problem->fk();
            // 如果当前用户登录了，判断AC状态
            $problem->ac = false;
            $problem->ac2 = false; /* ac2表示赛后AC */
            $problem->pending = false;

            if ($this->login_user) {
                if ((new SolutionModel())
                    ->where("contest_id", $problem->contest_id)
                    ->where('user_id', $this->login_user->user_id)
                    ->where('problem_id', $problem->problem_id)
                    ->where('in_date', '>', $this->contest->start_time)
                    ->where('in_date', '<', $this->contest->end_time)
                    ->where('result', 4)
                    ->find()) {
                    $problem->ac = true;
                } else if ((new SolutionModel())
                    ->where("contest_id", $problem->contest_id)
                    ->where('user_id', $this->login_user->user_id)
                    ->where('problem_id', $problem->problem_id)
                    ->where('in_date', '>=', $this->contest->end_time)
                    ->where('result', 4)
                    ->find()) {
                    $problem->ac2 = true;
                } else if ((new SolutionModel())
                    ->where("contest_id", $problem->contest_id)
                    ->where('user_id', $this->login_user->user_id)
                    ->where('problem_id', $problem->problem_id)
                    ->where('in_date', '>', $this->contest->start_time)
                    ->where('in_date', '<', $this->contest->end_time)
                    ->find()) {
                    $problem->pending = true;
                }
            }
        }

        $allowed_langs = [];
        $allowed_langs_all = $this->allowed_langs();
        if ('*' == $this->contest->langmask) {
            $allowed_langs = $allowed_langs_all;
        } else {
            $allowed_lang_ids = explode(',', $this->contest->langmask);
            foreach ($allowed_lang_ids as $allowed_lang_id) {
                foreach ($allowed_langs_all as $item) {
                    if (intval($item['id']) == intval($allowed_lang_id)) {
                        $allowed_langs[] = $item;
                    }
                }
            }
        }

        /* 获取近期提交记录 */
        if ($this->is_login) {
            $recent_solutions = (new SolutionModel())
                ->where('contest_id', $this->contest_id)
                ->where('user_id', $this->login_user->user_id)
                ->where('problem_id', $contest_problem->problem_id)
                ->order('create_time', 'desc')
                ->select();
            foreach ($recent_solutions as $recent_solution) {
                $recent_solution->fk();
                $recent_solution->result_text = $this->lang[$recent_solution->result_code];
            }

            $this->assign('recent_solutions', $recent_solutions);
        }

        $this->assign('contest_problems', $contest_problems);
        $this->assign('allowed_langs', $allowed_langs);
        return view('./contest-problem');
    }

}