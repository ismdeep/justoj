<?php


namespace app\contest\controller;


use app\api\model\ContestProblemModel;
use app\api\model\ContestTouristModel;
use app\api\model\SolutionModel;
use app\api\model\UserModel;
use app\contest\common\ContestBaseController;
use app\extra\util\PenaltyUtil;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

class Rank extends ContestBaseController {

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

}