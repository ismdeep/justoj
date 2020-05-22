<?php


namespace app\contest\controller;


use app\api\model\ContestEnrollModel;
use app\api\model\ContestModel;
use app\api\model\ContestProblemModel;
use app\api\model\ProblemModel;
use app\api\model\SolutionModel;
use app\api\model\UserModel;
use app\contest\common\ContestBaseController;
use think\Db;

class Index extends ContestBaseController {

    /**
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function show_contest_home_page() {
        $contest_problems_tmp = (new ContestProblemModel())
            ->where('contest_id', $this->contest->contest_id)
            ->order('num', 'asc')
            ->select();

        $contest_problems = array();
        foreach ($contest_problems_tmp as $contest_problem) {
            if (ProblemModel::get(['problem_id' => $contest_problem->problem_id])) {
                array_push($contest_problems, $contest_problem);
            }
        }

        foreach ($contest_problems as $contest_problem) {
            $contest_problem->fk();
            // 如果当前用户登录了，判断AC状态
            $contest_problem->ac = false;
            $contest_problem->pending = false;
            if ($this->loginuser) {
                if ((new SolutionModel())->
                where("contest_id", $contest_problem->contest_id)
                    ->where('user_id', $this->loginuser->user_id)
                    ->where('problem_id', $contest_problem->problem_id)
                    ->where('in_date', '>', $this->contest->start_time)
                    ->where('in_date', '<', $this->contest->end_time)
                    ->where('result', 4)
                    ->find()) {
                    $contest_problem->ac = true;
                } else {
                    if ((new SolutionModel())->
                    where("contest_id", $contest_problem->contest_id)
                        ->where('user_id', $this->loginuser->user_id)
                        ->where('problem_id', $contest_problem->problem_id)
                        ->where('in_date', '>', $this->contest->start_time)
                        ->where('in_date', '<', $this->contest->end_time)
                        ->find()) {
                        $contest_problem->pending = true;
                    }
                }
            }
        }

        // 获取比赛参与人数
        $user_count = Db::query("select count(distinct user_id) as cnt from `solution` where contest_id={$this->contest_id}")[0]['cnt'];
        $this->assign('user_count', $user_count);
        $this->assign('enroll_count', (new ContestEnrollModel())->where(['contest_id' => $this->contest_id])->count());
        $this->assign('contest_problems', $contest_problems);
        $this->assign('contest', $this->contest);
        return view($this->theme_root . '/contest');
    }

    /**
     * 注册比赛页面
     *
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function show_contest_enroll_page() {
        // 判断是否需要完善个人信息
        if (null == $this->loginuser) {
            $this->redirect("/login?redirect=" . urlencode("/contests/{$this->contest_id}/enroll"));
        }

        $user = (new UserModel())->where(['user_id' => $this->loginuser->user_id])->find();

        $this->assign('need_complete_info', false);
        if (UserModel::need_complete_info($user)) {
            $this->assign('need_complete_info', true);
        }

        // 判断是否已经注册
        if (null != (new ContestEnrollModel())->where([
                'user_id' => $this->loginuser->user_id,
                'contest_id' => $this->contest_id,
            ])->find()) {
            $this->redirect("/contests/{$this->contest_id}");
        }

        return view($this->theme_root . '/contest-enroll');
    }

}