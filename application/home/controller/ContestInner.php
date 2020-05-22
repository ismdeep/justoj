<?php


namespace app\home\controller;


use app\api\model\ContestEnrollModel;
use app\api\model\ContestModel;
use app\api\model\ContestProblemModel;
use app\api\model\ContestTouristModel;
use app\api\model\PrivilegeModel;
use app\api\model\ProblemModel;
use app\api\model\SimModel;
use app\api\model\SolutionModel;
use app\api\model\UserModel;
use app\extra\controller\ContestBaseController;
use app\extra\util\PenaltyUtil;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

class ContestInner extends ContestBaseController {

    /**
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function show_contest_home_page($id) {

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
        $user_count = Db::query("select count(distinct user_id) as cnt from `solution` where contest_id={$id}")[0]['cnt'];
        $this->assign('user_count', $user_count);
        $this->assign('enroll_count', (new ContestEnrollModel())->where(['contest_id' => $id])->count());
        $this->assign('contest_problems', $contest_problems);
        $this->assign('contest', $this->contest);
        return view($this->theme_root . '/contest');
    }

    /**
     * @return \think\response\View
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function show_rank_page() {
        // 获取本场比赛旅游队列表
        $tourist_user_ids = ContestTouristModel::tourists_in_contest($this->contest->contest_id);

        // 获取当前已有提交的所有用户名$users
        $users = [];
        $users_tmp = Db::query("select DISTINCT user_id from solution where contest_id=" . $this->contest->contest_id);
        // 获取当前比赛所有题目以及所有题目的first blood
        $contest_problems = (new ContestProblemModel)->where('contest_id', $this->contest->contest_id)->order('num', 'asc')->select();
        foreach ($contest_problems as $contest_problem) {
            $contest_problem['first_ac'] = (new SolutionModel)->
            where("contest_id", $contest_problem->contest_id)
                ->where('problem_id', $contest_problem->problem_id)
                ->where('in_date', '>', $this->contest->start_time)
                ->where('in_date', '<', $this->contest->end_time)
                ->where('result', 4)
                ->find();
        }
        $this->assign('contest_problems', $contest_problems);

        // 对每个用户进行计算AC题数依旧罚时Penalty
        foreach ($users_tmp as $user) {
            $user['ac_cnt'] = 0;
            $user['penalty'] = 0;
            $user['user'] = UserModel::get(['user_id' => $user['user_id']]);
            $user['mark'] = 0.00;
            foreach ($contest_problems as $contest_problem) {
                // 判断$user是否有AC$contest_problem
                $user[$contest_problem->problem_id]['first_ac'] = (new SolutionModel)->
                where("contest_id", $contest_problem->contest_id)
                    ->where('user_id', $user['user_id'])
                    ->where('problem_id', $contest_problem->problem_id)
                    ->where('in_date', '>', $this->contest->start_time)
                    ->where('in_date', '<', $this->contest->end_time)
                    ->where('result', 4)
                    ->find();
                if ($user[$contest_problem->problem_id]['first_ac']) {
                    // AC
                    $user[$contest_problem->problem_id]['ac'] = true;
                    $user['mark'] += 100;
                    ++$user['ac_cnt'];
                    // 计算在first_ac之前WA次数
                    $user[$contest_problem->problem_id]['wa_cnt'] = Db::query("select count(solution_id) as cnt from solution where contest_id=" . $this->contest->contest_id . " and problem_id=" . $contest_problem->problem_id . " and user_id='" . $user['user_id'] . "' and in_date >= '" . $this->contest->start_time . "' and in_date < '" . $user[$contest_problem->problem_id]['first_ac']['in_date'] . "'")[0]['cnt'];
                    $user[$contest_problem->problem_id]['first_ac']['penalty'] = (strtotime($user[$contest_problem->problem_id]['first_ac']['in_date']) - strtotime($this->contest->start_time));;
                    $user['penalty'] += $user[$contest_problem->problem_id]['first_ac']['penalty'];
                    $user['penalty'] += 20 * 60 * $user[$contest_problem->problem_id]['wa_cnt'];
                    // 计算first_ac对应的时间数
                    $user[$contest_problem->problem_id]['first_ac']['penalty_text'] = PenaltyUtil::penalty_int_2_text($user[$contest_problem->problem_id]['first_ac']['penalty']);
                } else {
                    // WA
                    $user[$contest_problem->problem_id]['ac'] = false;
                    // 直接获取比赛期间提交次数
                    $user[$contest_problem->problem_id]['wa_cnt'] = Db::query("select count(solution_id) as cnt from solution where contest_id=" . $this->contest->contest_id . " and problem_id=" . $contest_problem->problem_id . " and user_id='" . $user['user_id'] . "' and in_date >= '" . $this->contest->start_time . "' and in_date <= '" . $this->contest->end_time . "'")[0]['cnt'];
                    if (intval($user[$contest_problem->problem_id]['wa_cnt']) > 0) {
                        $user['mark'] += 33.33334;
                    }
                }

            }
            $user['penalty_text'] = PenaltyUtil::penalty_int_2_text($user['penalty']);
            $user['mark'] = intval($user['mark'] / sizeof($contest_problems));

            // 处理是否是旅游队
            $user['is_tourist'] = in_array($user['user_id'], $tourist_user_ids);

            $users[] = $user;
        }

        for ($i = 0; $i < sizeof($users) - 1; ++$i) {
            for ($j = 0; $j < sizeof($users) - 1 - $i; ++$j) {
                if ($users[$j]['ac_cnt'] < $users[$j + 1]['ac_cnt']) {
                    $tmp = $users[$j];
                    $users[$j] = $users[$j + 1];
                    $users[$j + 1] = $tmp;
                } else if ($users[$j]['ac_cnt'] == $users[$j + 1]['ac_cnt']) {
                    if ($users[$j]['penalty'] > $users[$j + 1]['penalty']) {
                        $tmp = $users[$j];
                        $users[$j] = $users[$j + 1];
                        $users[$j + 1] = $tmp;
                    }
                }
            }
        }

        $this->assign('users', $users);
        return view($this->theme_root . '/contest-rank');
    }

    /**
     * 比赛内搜索
     *
     * @param $id
     * @param string $run_id
     * @param string $username
     * @param string $problem_id
     * @param string $result
     * @param string $language
     * @return \think\response\View
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function show_status_list($id, $run_id = '', $username = '', $problem_id = '', $result = '', $language = '') {

        if (!is_numeric($run_id)) {
            $run_id = '';
        } else {
            $run_id = intval($run_id);
        }

        if (!is_numeric($result)) {
            $result = '';
        } else {
            $result = intval($result);
        }

        if (!is_numeric($language)) {
            $language = '';
        } else {
            $language = intval($language);
        }

        $this->assign('run_id', htmlspecialchars($run_id));
        $this->assign('username', htmlspecialchars($username));
        $this->assign('problem_id', htmlspecialchars($problem_id));
        $this->assign('result', $result);
        $this->assign('language', $language);

        $this->assign('allowed_langs', $this->allowed_langs());

        // 获取题目列表
        $contest_problems = (new ContestProblemModel())->where('contest_id', $this->contest->contest_id)->order('num', 'asc')->select();
        // 获取题目ids
        $contest_problem_ids = [];

        foreach ($contest_problems as $contest_problem) {
            $contest_problem->problem = (new ProblemModel())->where(['problem_id' => $contest_problem->problem_id])->find();
            $contest_problem_ids[] = $contest_problem->problem_id;
        }
        $this->assign('contest_problems', $contest_problems);

        // 构建problem_id 真实到虚拟映射
        $problem_id_true_to_fake = [];
        foreach ($contest_problems as $contest_problem) {
            $problem_id_true_to_fake[$contest_problem->problem_id] = $contest_problem->num;
        }
        $this->assign('problem_id_true_to_fake', $problem_id_true_to_fake);

        // 指明solution_id
        if ('' != $run_id) {
            $solutions = (new SolutionModel)
                ->where('solution_id', $run_id)
                ->where('contest_id', $this->contest_id)
                ->paginate(10);
            foreach ($solutions as $solution) {
                $solution->sim = (new SimModel())->where('s_id', $solution->solution_id)->find();
            }
            $solutions->appends('run_id', $run_id);
            $this->assign('solutions', $solutions);
            return view($this->theme_root . '/contest-status');
        }
        // 获取所有题目列表

        // 搜索
        $where = (new SolutionModel());
        $where = $where->where(['contest_id' => $this->contest->contest_id])->where(['problem_id' => ['in', $contest_problem_ids]]);
        if ('' != $username) {
            $where = $where->where(['user_id' => $username]);
        }
        if ('' != $problem_id) {
            $problem_id_true = ContestProblemModel::get(['contest_id' => $this->contest->contest_id, 'num' => $problem_id])->problem_id;
            $where = $where->where(['problem_id' => $problem_id_true]);
        }
        if ('' != $result) {
            $where = $where->where(['result' => $result]);
        }
        if ('' != $language) {
            $where = $where->where(['language' => $language]);
        }
        $solutions = $where->order('solution_id', 'desc')->paginate(10);

        foreach ($solutions as $solution) {
            $solution->sim = (new SimModel())->where('s_id', $solution->solution_id)->find();
        }

        $solutions->appends('id', $this->contest->contest_id);
        $solutions->appends('run_id', '');
        $solutions->appends('username', $username);
        $solutions->appends('problem_id', $problem_id);
        $solutions->appends('result', $result);
        $solutions->appends('language', $language);

        $this->assign('solutions', $solutions);
        return view($this->theme_root . '/contest-status');
    }

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
        $contest_problem = ContestProblemModel::get(['contest_id' => $this->contest->contest_id, 'num' => $pid]);
        $contest_problem->ac = false;
        $contest_problem->pending = false;
        if ($this->loginuser) {
            if (SolutionModel::
            where("contest_id", $contest_problem->contest_id)
                ->where('user_id', $this->loginuser->user_id)
                ->where('problem_id', $contest_problem->problem_id)
                ->where('in_date', '>', $this->contest->start_time)
                ->where('in_date', '<', $this->contest->end_time)
                ->where('result', 4)
                ->find()) {
                $contest_problem->ac = true;
            } else {
                if (SolutionModel::
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
        $problem = ProblemModel::get(['problem_id' => $contest_problem->problem_id]);

        $this->assign('contest_problem', $contest_problem);
        $this->assign('problem', $problem);

        // 获取比赛题目列表
        $contest_problems = ContestProblemModel::where('contest_id', $this->contest->contest_id)->order('num', 'asc')->select();
        foreach ($contest_problems as $problem) {
            $contest_problem->fk();
            // 如果当前用户登录了，判断AC状态
            $problem->ac = false;
            $problem->pending = false;
            if ($this->loginuser) {
                if (SolutionModel::
                where("contest_id", $problem->contest_id)
                    ->where('user_id', $this->loginuser->user_id)
                    ->where('problem_id', $problem->problem_id)
                    ->where('in_date', '>', $this->contest->start_time)
                    ->where('in_date', '<', $this->contest->end_time)
                    ->where('result', 4)
                    ->find()) {
                    $problem->ac = true;
                } else {
                    if ((new SolutionModel())
                        ->where("contest_id", $problem->contest_id)
                        ->where('user_id', $this->loginuser->user_id)
                        ->where('problem_id', $problem->problem_id)
                        ->where('in_date', '>', $this->contest->start_time)
                        ->where('in_date', '<', $this->contest->end_time)
                        ->find()) {
                        $problem->pending = true;
                    }
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
                ->where('user_id', $this->loginuser->user_id)
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
        return view($this->theme_root . '/contest-problem');
    }


    /**
     * 注册比赛页面
     *
     * @param string $contest_id
     *
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function show_contest_enroll_page($id) {
        intercept(!$id, 'contest_id参数错误');
        $contest = (new ContestModel())->where(['contest_id' => $id])->find();
        intercept(null == $contest, '比赛不存在');

        $this->assign('contest', $contest);
        $this->assign('contest_id', $id);

        // 判断是否需要完善个人信息
        if (null == $this->loginuser) {
            $this->redirect("/login?redirect=" . urlencode("/contests/{$id}/enroll"));
        }

        $user = (new UserModel())->where(['user_id' => $this->loginuser->user_id])->find();

        $this->assign('need_complete_info', false);
        if (UserModel::need_complete_info($user)) {
            $this->assign('need_complete_info', true);
        }

        // 判断是否已经注册
        if (null != (new ContestEnrollModel())->where([
                'user_id' => $this->loginuser->user_id, 'contest_id' => $id
            ])->find()) {
            $this->redirect("/contests/{$id}");
        }

        return view($this->theme_root . '/contest-enroll');
    }
}