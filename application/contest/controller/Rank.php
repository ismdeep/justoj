<?php


namespace app\contest\controller;


use app\api\model\ContestProblemModel;
use app\api\model\ContestTouristModel;
use app\api\model\GroupModel;
use app\api\model\SolutionModel;
use app\api\model\UserModel;
use app\contest\common\ContestBaseController;
use app\extra\util\PenaltyUtil;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

class Rank extends ContestBaseController {

    /**
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function show_rank_page() {
        /* 判断比赛是否开始 */
        if (!$this->contest_started) {
            $this->redirect("/contests/{$this->contest_id}");
        }

        /* 获取本场比赛旅游队列表 */
        $tourist_user_ids = $this->contest->get_tourist_user_ids();

        /* 获取本场比赛有效Solutions */
        $solutions = $this->contest->get_significant_solutions();

        /* 获取本场First Blood SolutionIDs */
        $first_ac_solution_ids = [];
        $tmp_problem_ids = [];
        foreach ($solutions as $solution) {
            /* @var $solution SolutionModel */
            if ($solution->result == SolutionModel::RESULT_AC && !in_array($solution->problem_id, $tmp_problem_ids)) {
                $first_ac_solution_ids []= $solution->solution_id;
                $tmp_problem_ids []= $solution->problem_id;
            }
        }

        /* 准备每位选手数据下的problem列表 */
        $contest_problems = $this->contest->get_contest_problems();
        $problem_data = [];
        foreach ($contest_problems as $contest_problem) {
            /* @var $contest_problem ContestProblemModel */
            $problem_data[$contest_problem->problem_id] = [
                'first_ac' => false,
                'ac_flag' => false,
                'ac_time' => null,
                'wa_cnt' => 0,
                'penalty' => 0,
                'penalty_text' => '',
            ];
        }

        /* 获取当前已有提交的所有用户名$users */
        $users = $this->contest->get_users();
        foreach ($users as $user) {
            /* @var $user UserModel */
            $user['is_tourist'] = in_array($user->user_id, $tourist_user_ids);
            $user['ac_cnt'] = 0;
            $user['penalty'] = 0;
            $user['penalty_text'] = '';
            $user['problem'] = $problem_data;
        }

        $users_map = [];
        foreach ($users as &$user) {
            /* @var $user UserModel */
            $users_map[$user->user_id] = $user;
        }

        foreach ($solutions as $solution) {
            /* @var $solution SolutionModel */
            $user = $users_map[$solution->user_id];
            $problem = $user['problem'];
            if ($problem[$solution->problem_id]['ac_flag']) {
                continue;
            }
            if ($solution->result == SolutionModel::RESULT_AC) {
                $problem[$solution->problem_id]['ac_time'] = strtotime($solution->in_date) - strtotime($this->contest->start_time);
                $problem[$solution->problem_id]['penalty'] += strtotime($solution->in_date) - strtotime($this->contest->start_time);
                $problem[$solution->problem_id]['penalty_text'] = PenaltyUtil::penalty_int_2_text($problem[$solution->problem_id]['penalty']);
                $problem[$solution->problem_id]['ac_flag'] = true;
                if (in_array($solution->solution_id, $first_ac_solution_ids)) {
                    $problem[$solution->problem_id]['first_ac'] = true;
                }
                $user['penalty'] += $problem[$solution->problem_id]['penalty'];
                $user['penalty_text'] = PenaltyUtil::penalty_int_2_text($user['penalty']);
                $user['ac_cnt'] = $user['ac_cnt'] + 1;
            } else {
                $problem[$solution->problem_id]['penalty'] += 20 * 60;
                $problem[$solution->problem_id]['wa_cnt']++;
            }

            $user['problem'] = $problem;
        }

        /* 对 $users 进行排序 */
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

        $this->assign('contest_problems', $contest_problems);
        $this->assign('users', $users);
        return view($this->theme_root . '/contest-rank');
    }

    /**
     * 导出班级 contest 的 xls 文件
     *
     * @param  string $group_id
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function export_group_contest_xls($group_id = '') {
        if ('' == $group_id) {
            return "error";
        }

        $group = GroupModel::get(['id' => $group_id]);

        intercept(null == $group, 'error');

        // 获取当前已有提交的所有用户名$users
        $users_tmp = Db::query("select distinct user_id from group_join where group_id=" . $group_id . " and status=1");
        $users = [];

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


        $cellKey = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
            'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM',
            'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

        /**
         * A Rank
         * B Username
         * C Nickname
         * D Accepted
         * E Penalty
         * F A
         * G B
         */
        $spreadsheet = new  Spreadsheet();
        $PHPSheet = $spreadsheet->getActiveSheet();

        // 标题比赛名称
        $PHPSheet->setCellValue('A1', $group->name . '-' . $this->contest->title);
        $PHPSheet->mergeCells('A1:' . $cellKey[4 + sizeof($contest_problems)] . '1');
        $PHPSheet->getRowDimension('1')->setRowHeight('30');
        $PHPSheet->getStyle('A1')->getFont()->setName('微软雅黑');
        $PHPSheet->getStyle('A1')->getFont()->setSize(18);

        // 设置表格头
        $PHPSheet
            ->setCellValue('A2', $this->lang['rank'])
            ->setCellValue('B2', $this->lang['username'])
            ->setCellValue('C2', $this->lang['nickname'])
            ->setCellValue('D2', $this->lang['accepted'])
            ->setCellValue('E2', $this->lang['mark'])
            ->setCellValue('F2', $this->lang['penalty']);
        $PHPSheet->getColumnDimension('A')->setWidth('6');
        $PHPSheet->getColumnDimension('B')->setWidth('20');
        $PHPSheet->getColumnDimension('C')->setWidth('40');
        $PHPSheet->getColumnDimension('D')->setWidth('15');
        $PHPSheet->getColumnDimension('E')->setWidth('15');
        $PHPSheet->getColumnDimension('F')->setWidth('15');

        $PHPSheet->getStyle('A2')->getFont()->setBold('true');
        $PHPSheet->getStyle('B2')->getFont()->setBold('true');
        $PHPSheet->getStyle('C2')->getFont()->setBold('true');
        $PHPSheet->getStyle('D2')->getFont()->setBold('true');
        $PHPSheet->getStyle('E2')->getFont()->setBold('true');
        $PHPSheet->getStyle('F2')->getFont()->setBold('true');

        for ($i = 0; $i < sizeof($contest_problems); ++$i) {
            $PHPSheet->setCellValue($cellKey[6 + $contest_problems[$i]->num] . '2', $cellKey[$contest_problems[$i]->num]);
            $PHPSheet->getStyle($cellKey[6 + $contest_problems[$i]->num] . '2')->getFont()->setBold('true');
            $PHPSheet->getColumnDimension($cellKey[6 + $contest_problems[$i]->num])->setWidth('12');
        }

        // 填写选手成绩
        for ($i = 0; $i < sizeof($users); ++$i) {
            $PHPSheet->setCellValue('A' . ($i + 3), $i + 1);
            $PHPSheet->setCellValue('B' . ($i + 3), $users[$i]['user_id']);
            $PHPSheet->setCellValue('C' . ($i + 3), $users[$i]['user']['nick']);
            $PHPSheet->setCellValue('D' . ($i + 3), $users[$i]['ac_cnt']);
            $PHPSheet->setCellValue('E' . ($i + 3), $users[$i]['mark']);
            $PHPSheet->setCellValue('F' . ($i + 3), $users[$i]['penalty_text']);
            // 写入每题详情
            for ($j = 0; $j < sizeof($contest_problems); ++$j) {
                if (isset($users[$i][$contest_problems[$j]->problem_id]['first_ac'])) {

                    $text = $users[$i][$contest_problems[$j]->problem_id]['first_ac']['penalty_text'];
                    if ($users[$i][$contest_problems[$j]->problem_id]['wa_cnt'] > 0) {
                        $text .= '(-' . $users[$i][$contest_problems[$j]->problem_id]['wa_cnt'] . ')';
                    }
                    $PHPSheet->setCellValue($cellKey[6 + $contest_problems[$j]->num] . ($i + 3), $text);

                    // 判断是否是一血
                    if ($users[$i][$contest_problems[$j]->problem_id]['first_ac']['solution_id'] == $contest_problems[$j]['first_ac']['solution_id']) {
                        $PHPSheet->getStyle($cellKey[6 + $contest_problems[$j]->num] . ($i + 3))->getFill()->getStartColor()->setARGB('008800');
                    } else {
                        $PHPSheet->getStyle($cellKey[6 + $contest_problems[$j]->num] . ($i + 3))->getFill()->getStartColor()->setARGB('A9F5AF');
                    }

                } else {
                    if ($users[$i][$contest_problems[$j]->problem_id]['wa_cnt'] > 0) {
                        $PHPSheet->setCellValue($cellKey[6 + $contest_problems[$j]->num] . ($i + 3), '-' . $users[$i][$contest_problems[$j]->problem_id]['wa_cnt']);
                        $PHPSheet->getStyle($cellKey[6 + $contest_problems[$j]->num] . ($i + 3))->getFill()->getStartColor()->setARGB('FF7A7A');
                    }
                }
            }
        }


        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="contest' . $this->contest->contest_id . '_' . $group->name . '_' . $this->contest->title . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }

}