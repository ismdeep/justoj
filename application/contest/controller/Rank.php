<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/9
 * Time: 11:16 PM
 */

namespace app\contest\controller;


use app\api\model\ContestProblemModel;
use app\api\model\GroupModel;
use app\api\model\SolutionModel;
use app\api\model\UserModel;
use app\extra\controller\ContestBaseController;
use app\extra\util\PenaltyUtil;
use PHPExcel_Exception;
use PHPExcel_Reader_Exception;
use PHPExcel_Writer_Exception;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Request;

class Rank extends ContestBaseController
{
	public function __construct(Request $request = null)
	{
		parent::__construct($request);
		$this->assign('nav', 'rank');
		if (!(($this->permitted && $this->contest_started) || $this->is_administrator)) {
			$this->redirect('/contest?id='.$this->contest->contest_id);
		}

        if (!$this->permitted) {
            $this->redirect('/contest?id='.$this->contest_id);
        }
	}

    /**
     * @return \think\response\View
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function index()
	{
		// 获取当前已有提交的所有用户名$users
		$users = [];
        $users_tmp = Db::query("select DISTINCT user_id from solution where contest_id=".$this->contest->contest_id);
		// 获取当前比赛所有题目以及所有题目的first blood
		$contest_problems = (new ContestProblemModel)->where('contest_id', $this->contest->contest_id)->order('num','asc')->select();
		foreach ($contest_problems as $contest_problem) {
			$contest_problem['first_ac'] = (new SolutionModel)->
			where("contest_id", $contest_problem->contest_id)
				->where('problem_id', $contest_problem->problem_id)
				->where('in_date','>', $this->contest->start_time)
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
					->where('in_date','>', $this->contest->start_time)
					->where('in_date', '<', $this->contest->end_time)
					->where('result', 4)
					->find();
				if ($user[$contest_problem->problem_id]['first_ac']) {
					// AC
					$user[$contest_problem->problem_id]['ac'] = true;
                    $user['mark'] += 100;
					++$user['ac_cnt'];
					// 计算在first_ac之前WA次数
					$user[$contest_problem->problem_id]['wa_cnt'] = Db::query("select count(solution_id) as cnt from solution where contest_id=".$this->contest->contest_id." and problem_id=".$contest_problem->problem_id." and user_id='".$user['user_id']."' and in_date >= '".$this->contest->start_time."' and in_date < '".$user[$contest_problem->problem_id]['first_ac']['in_date']."'")[0]['cnt'];
					$user[$contest_problem->problem_id]['first_ac']['penalty'] = ( strtotime($user[$contest_problem->problem_id]['first_ac']['in_date']) - strtotime($this->contest->start_time) );;
					$user['penalty'] += $user[$contest_problem->problem_id]['first_ac']['penalty'];
					$user['penalty'] += 20 * 60 * $user[$contest_problem->problem_id]['wa_cnt'];
					// 计算first_ac对应的时间数
					$user[$contest_problem->problem_id]['first_ac']['penalty_text'] = PenaltyUtil::penalty_int_2_text($user[$contest_problem->problem_id]['first_ac']['penalty']);
				}else{
					// WA
					$user[$contest_problem->problem_id]['ac'] = false;
					// 直接获取比赛期间提交次数
					$user[$contest_problem->problem_id]['wa_cnt'] = Db::query("select count(solution_id) as cnt from solution where contest_id=".$this->contest->contest_id." and problem_id=".$contest_problem->problem_id." and user_id='".$user['user_id']."' and in_date >= '".$this->contest->start_time."' and in_date <= '".$this->contest->end_time."'")[0]['cnt'];
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
				if ($users[$j]['ac_cnt'] < $users[$j+1]['ac_cnt']) {
					$tmp = $users[$j];
					$users[$j] = $users[$j+1];
					$users[$j+1] = $tmp;
				}else if ($users[$j]['ac_cnt'] == $users[$j+1]['ac_cnt']){
					if ($users[$j]['penalty'] > $users[$j+1]['penalty']){
						$tmp = $users[$j];
						$users[$j] = $users[$j+1];
						$users[$j+1] = $tmp;
					}
				}
			}
		}

		$this->assign('users', $users);
		return view();
	}


    /**
     * 导出排名xls文件
     * @param $id
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
	public function export_xls($id = '') {

	    intercept(null == $id || '' == $id, 'id can not be empty');

		// 获取当前已有提交的所有用户名$users
		$users_tmp = Db::query("select DISTINCT user_id from solution where contest_id=".$this->contest->contest_id);
		$users = array();

		// 获取当前比赛所有题目以及所有题目的first blood
		$contest_problems = (new ContestProblemModel)->where('contest_id', $this->contest->contest_id)->order('num','asc')->select();
		foreach ($contest_problems as $contest_problem) {
			$contest_problem['first_ac'] = (new SolutionModel)->
			where("contest_id", $contest_problem->contest_id)
				->where('problem_id', $contest_problem->problem_id)
				->where('in_date','>', $this->contest->start_time)
				->where('in_date', '<', $this->contest->end_time)
				->where('result', 4)
				->find();
		}
		$this->assign('contest_problems', $contest_problems);

		// $solutions = SolutionModel::all(['contest_id' => $this->contest->contest_id]);

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
					->where('in_date','>', $this->contest->start_time)
					->where('in_date', '<', $this->contest->end_time)
					->where('result', 4)
					->find();
				if ($user[$contest_problem->problem_id]['first_ac']) {
					// AC
					$user[$contest_problem->problem_id]['ac'] = true;
					++$user['ac_cnt'];
                    $user['mark'] += 100;
					// 计算在first_ac之前WA次数
					$user[$contest_problem->problem_id]['wa_cnt'] = Db::query("select count(solution_id) as cnt from solution where contest_id=".$this->contest->contest_id." and problem_id=".$contest_problem->problem_id." and user_id='".$user['user_id']."' and in_date >= '".$this->contest->start_time."' and in_date < '".$user[$contest_problem->problem_id]['first_ac']['in_date']."'")[0]['cnt'];
					$user[$contest_problem->problem_id]['first_ac']['penalty'] = ( strtotime($user[$contest_problem->problem_id]['first_ac']['in_date']) - strtotime($this->contest->start_time) );;
					$user['penalty'] += $user[$contest_problem->problem_id]['first_ac']['penalty'];
					$user['penalty'] += 20 * 60 * $user[$contest_problem->problem_id]['wa_cnt'];
					// 计算first_ac对应的时间数
					$user[$contest_problem->problem_id]['first_ac']['penalty_text'] = PenaltyUtil::penalty_int_2_text($user[$contest_problem->problem_id]['first_ac']['penalty']);
				}else{
					// WA
					$user[$contest_problem->problem_id]['ac'] = false;
					// 直接获取比赛期间提交次数
					$user[$contest_problem->problem_id]['wa_cnt'] = Db::query("select count(solution_id) as cnt from solution where contest_id=".$this->contest->contest_id." and problem_id=".$contest_problem->problem_id." and user_id='".$user['user_id']."' and in_date >= '".$this->contest->start_time."' and in_date <= '".$this->contest->end_time."'")[0]['cnt'];
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
				if ($users[$j]['ac_cnt'] < $users[$j+1]['ac_cnt']) {
					$tmp = $users[$j];
					$users[$j] = $users[$j+1];
					$users[$j+1] = $tmp;
				}else if ($users[$j]['ac_cnt'] == $users[$j+1]['ac_cnt']){
					if ($users[$j]['penalty'] > $users[$j+1]['penalty']){
						$tmp = $users[$j];
						$users[$j] = $users[$j+1];
						$users[$j+1] = $tmp;
					}
				}
			}
		}


		$cellKey = array('A','B','C','D','E','F','G','H','I','J','K','L','M',
						 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
						 'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM',
						 'AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

		/**
		 * A Rank
		 * B Username
		 * C Nickname
		 * D Accepted
		 * E Penalty
		 * F A
		 * G B
		 */
		$phpexcel = new \PHPExcel();
		$PHPSheet = $phpexcel->getActiveSheet(); //获得当前活动sheet的操作对象

		// 标题比赛名称
		$PHPSheet->setCellValue('A1', $this->contest->title);
		$PHPSheet->mergeCells('A1:'.$cellKey[4+sizeof($contest_problems)].'1');
		$PHPSheet->getRowDimension('1')->setRowHeight('30');
		$PHPSheet->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$PHPSheet->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$PHPSheet->getStyle('A1')->getFont()->setName('Courier New');
		$PHPSheet->getStyle('A1')->getFont()->setSize(18);

		// 设置表格头
		$PHPSheet
			->setCellValue('A2', $this->lang['rank'])
			->setCellValue('B2', $this->lang['username'])
			->setCellValue('C2', $this->lang['fullname'])
            ->setCellValue('D2', $this->lang['school'])
            ->setCellValue('E2', $this->lang['academy'])
            ->setCellValue('F2', $this->lang['class'])
			->setCellValue('G2', $this->lang['accepted'])
            ->setCellValue('H2', $this->lang['mark'])
			->setCellValue('I2', $this->lang['penalty']);
		$PHPSheet->getColumnDimension('A')->setWidth('6');
		$PHPSheet->getColumnDimension('B')->setWidth('20');
		$PHPSheet->getColumnDimension('C')->setWidth('40');
		$PHPSheet->getColumnDimension('D')->setWidth('15');
		$PHPSheet->getColumnDimension('E')->setWidth('15');
        $PHPSheet->getColumnDimension('F')->setWidth('15');
        $PHPSheet->getColumnDimension('G')->setWidth('15');
        $PHPSheet->getColumnDimension('H')->setWidth('15');
        $PHPSheet->getColumnDimension('I')->setWidth('15');

		$PHPSheet->getStyle('A2')->getFont()->setBold('true');
		$PHPSheet->getStyle('B2')->getFont()->setBold('true');
		$PHPSheet->getStyle('C2')->getFont()->setBold('true');
		$PHPSheet->getStyle('D2')->getFont()->setBold('true');
		$PHPSheet->getStyle('E2')->getFont()->setBold('true');
        $PHPSheet->getStyle('F2')->getFont()->setBold('true');
        $PHPSheet->getStyle('G2')->getFont()->setBold('true');
        $PHPSheet->getStyle('H2')->getFont()->setBold('true');
        $PHPSheet->getStyle('I2')->getFont()->setBold('true');

		for ($i = 0; $i < sizeof($contest_problems); ++$i) {
			$PHPSheet->setCellValue($cellKey[9+$contest_problems[$i]->num].'2', $cellKey[$contest_problems[$i]->num]);
			$PHPSheet->getStyle($cellKey[9+$contest_problems[$i]->num].'2')->getFont()->setBold('true');
			$PHPSheet->getColumnDimension($cellKey[9+$contest_problems[$i]->num])->setWidth('12');
		}

		// 填写选手成绩
		for ($i = 0; $i < sizeof($users); ++$i) {
			$PHPSheet->setCellValue('A'.($i+3), $i+1);
			$PHPSheet->setCellValue('B'.($i+3), $users[$i]['user_id']);
			$PHPSheet->getStyle('B'.($i+3))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$PHPSheet->setCellValue('C'.($i+3), $users[$i]['user']['realname']);
			$PHPSheet->getStyle('C'.($i+3))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

            $PHPSheet->setCellValue('D'.($i+3), $users[$i]['user']['school']);
            $PHPSheet->getStyle('D'.($i+3))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

            $PHPSheet->setCellValue('E'.($i+3), $users[$i]['user']['academy']);
            $PHPSheet->getStyle('E'.($i+3))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

            $PHPSheet->setCellValue('F'.($i+3), $users[$i]['user']['class']);
            $PHPSheet->getStyle('F'.($i+3))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

            $PHPSheet->setCellValue('G'.($i+3), $users[$i]['ac_cnt']);
			$PHPSheet->getStyle('G'.($i+3))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$PHPSheet->setCellValue('H'.($i+3), $users[$i]['mark']);
			$PHPSheet->getStyle('H'.($i+3))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $PHPSheet->setCellValue('I'.($i+3), $users[$i]['penalty_text']);
            $PHPSheet->getStyle('I'.($i+3))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

			// 写入每题详情
			for ($j = 0; $j < sizeof($contest_problems); ++$j) {
				if (isset($users[$i][$contest_problems[$j]->problem_id]['first_ac'])) {

					$text = $users[$i][$contest_problems[$j]->problem_id]['first_ac']['penalty_text'];
					if ($users[$i][$contest_problems[$j]->problem_id]['wa_cnt'] > 0) {
						$text .= '(-'.$users[$i][$contest_problems[$j]->problem_id]['wa_cnt'].')';
					}
					$PHPSheet->setCellValue($cellKey[9+$contest_problems[$j]->num].($i+3), $text);

					$PHPSheet->getStyle($cellKey[9+$contest_problems[$j]->num].($i+3))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
					// 判断是否是一血
					if ($users[$i][$contest_problems[$j]->problem_id]['first_ac']['solution_id'] == $contest_problems[$j]['first_ac']['solution_id']) {
						$PHPSheet->getStyle($cellKey[9+$contest_problems[$j]->num].($i+3))->getFill()->getStartColor()->setARGB('008800');
					}else{
						$PHPSheet->getStyle($cellKey[9+$contest_problems[$j]->num].($i+3))->getFill()->getStartColor()->setARGB('A9F5AF');
					}

				}else{
					if ($users[$i][$contest_problems[$j]->problem_id]['wa_cnt'] > 0) {
						$PHPSheet->setCellValue($cellKey[9+$contest_problems[$j]->num].($i+3), '-'.$users[$i][$contest_problems[$j]->problem_id]['wa_cnt']);
						$PHPSheet->getStyle($cellKey[9+$contest_problems[$j]->num].($i+3))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
						$PHPSheet->getStyle($cellKey[9+$contest_problems[$j]->num].($i+3))->getFill()->getStartColor()->setARGB('FF7A7A');
					}
				}
			}
		}

		$PHPWriter = \PHPExcel_IOFactory::createWriter($phpexcel,'Excel2007');

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="contest'.$this->contest->contest_id.'_'.$this->contest->title.'.xlsx"');
		header('Cache-Control: max-age=0');

		$PHPWriter->save('php://output');
	}

    /**
     * @param string $id
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    public function export_register_users ($id = '') {
        intercept(null == $id || '' == $id, 'id can not be empty');

        // 获取当前已有提交的所有用户名$users
        $users_tmp = Db::query("select user_id from contest_enroll where contest_id=".$this->contest->contest_id);
        $users = array();

        // 对每个用户进行计算AC题数依旧罚时Penalty
        foreach ($users_tmp as $user) {
            $user['ac_cnt'] = 0;
            $user['penalty'] = 0;
            $user['user'] = UserModel::get(['user_id' => $user['user_id']]);
            $user['mark'] = 0.00;
            $user['penalty_text'] = PenaltyUtil::penalty_int_2_text($user['penalty']);
            $users[] = $user;
        }

        for ($i = 0; $i < sizeof($users) - 1; ++$i) {
            for ($j = 0; $j < sizeof($users) - 1 - $i; ++$j) {
                if ($users[$j]['ac_cnt'] < $users[$j+1]['ac_cnt']) {
                    $tmp = $users[$j];
                    $users[$j] = $users[$j+1];
                    $users[$j+1] = $tmp;
                }else if ($users[$j]['ac_cnt'] == $users[$j+1]['ac_cnt']){
                    if ($users[$j]['penalty'] > $users[$j+1]['penalty']){
                        $tmp = $users[$j];
                        $users[$j] = $users[$j+1];
                        $users[$j+1] = $tmp;
                    }
                }
            }
        }


        $cellKey = array('A','B','C','D','E','F','G','H','I','J','K','L','M',
            'N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
            'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM',
            'AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

        /**
         * A Rank
         * B Username
         * C Nickname
         * D Accepted
         * E Penalty
         * F A
         * G B
         */
        $phpexcel = new \PHPExcel();
        $PHPSheet = $phpexcel->getActiveSheet(); //获得当前活动sheet的操作对象

        // 标题比赛名称
        $PHPSheet->setCellValue('A1', $this->contest->title);
        $PHPSheet->getRowDimension('1')->setRowHeight('30');
        $PHPSheet->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $PHPSheet->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $PHPSheet->getStyle('A1')->getFont()->setName('Courier New');
        $PHPSheet->getStyle('A1')->getFont()->setSize(18);

        // 设置表格头
        $PHPSheet
            ->setCellValue('A2', $this->lang['rank'])
            ->setCellValue('B2', $this->lang['username'])
            ->setCellValue('C2', $this->lang['fullname'])
            ->setCellValue('D2', $this->lang['school'])
            ->setCellValue('E2', $this->lang['class']);
        $PHPSheet->getColumnDimension('A')->setWidth('6');
        $PHPSheet->getColumnDimension('B')->setWidth('20');
        $PHPSheet->getColumnDimension('C')->setWidth('20');
        $PHPSheet->getColumnDimension('D')->setWidth('15');
        $PHPSheet->getColumnDimension('E')->setWidth('15');

        $PHPSheet->getStyle('A2')->getFont()->setBold('true');
        $PHPSheet->getStyle('B2')->getFont()->setBold('true');
        $PHPSheet->getStyle('C2')->getFont()->setBold('true');
        $PHPSheet->getStyle('D2')->getFont()->setBold('true');
        $PHPSheet->getStyle('E2')->getFont()->setBold('true');
        $PHPSheet->getStyle('F2')->getFont()->setBold('true');

        // 填写选手成绩
        for ($i = 0; $i < sizeof($users); ++$i) {
            $PHPSheet->setCellValue('A'.($i+3), $i+1);
            $PHPSheet->setCellValue('B'.($i+3), $users[$i]['user_id']);
            $PHPSheet->getStyle('B'.($i+3))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $PHPSheet->setCellValue('C'.($i+3), $users[$i]['user']['realname']);
            $PHPSheet->getStyle('C'.($i+3))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $PHPSheet->setCellValue('D'.($i+3), $users[$i]['user']['school']);
            $PHPSheet->getStyle('D'.($i+3))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $PHPSheet->setCellValue('E'.($i+3), $users[$i]['user']['class']);
            $PHPSheet->getStyle('E'.($i+3))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }

        $PHPWriter = \PHPExcel_IOFactory::createWriter($phpexcel,'Excel2007');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="contest'.$this->contest->contest_id.'_'.$this->contest->title.'.xlsx"');
        header('Cache-Control: max-age=0');

        $PHPWriter->save('php://output');
    }

    /**
     * 导出班级contest的xls文件
     * @param string $group_id
     * @return string
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
	public function export_group_contest_xls($group_id = '') {
		if ('' == $group_id) {
			return "error";
		}

        $group = GroupModel::get(['id' => $group_id]);

		intercept(null == $group, 'error');

		// 获取当前已有提交的所有用户名$users
		$users_tmp = Db::query("select distinct user_id from group_join where group_id=".$group_id." and status=1");
		$users = [];

		// 获取当前比赛所有题目以及所有题目的first blood
		$contest_problems = (new ContestProblemModel)->where('contest_id', $this->contest->contest_id)->order('num','asc')->select();
		foreach ($contest_problems as $contest_problem) {
			$contest_problem['first_ac'] = (new SolutionModel)->
			where("contest_id", $contest_problem->contest_id)
				->where('problem_id', $contest_problem->problem_id)
				->where('in_date','>', $this->contest->start_time)
				->where('in_date', '<', $this->contest->end_time)
				->where('result', 4)
				->find();
		}
		$this->assign('contest_problems', $contest_problems);

		// $solutions = SolutionModel::all(['contest_id' => $this->contest->contest_id]);

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
					->where('in_date','>', $this->contest->start_time)
					->where('in_date', '<', $this->contest->end_time)
					->where('result', 4)
					->find();
				if ($user[$contest_problem->problem_id]['first_ac']) {
					// AC
					$user[$contest_problem->problem_id]['ac'] = true;
                    $user['mark'] += 100;
                    ++$user['ac_cnt'];
					// 计算在first_ac之前WA次数
					$user[$contest_problem->problem_id]['wa_cnt'] = Db::query("select count(solution_id) as cnt from solution where contest_id=".$this->contest->contest_id." and problem_id=".$contest_problem->problem_id." and user_id='".$user['user_id']."' and in_date >= '".$this->contest->start_time."' and in_date < '".$user[$contest_problem->problem_id]['first_ac']['in_date']."'")[0]['cnt'];
					$user[$contest_problem->problem_id]['first_ac']['penalty'] = ( strtotime($user[$contest_problem->problem_id]['first_ac']['in_date']) - strtotime($this->contest->start_time) );;
					$user['penalty'] += $user[$contest_problem->problem_id]['first_ac']['penalty'];
					$user['penalty'] += 20 * 60 * $user[$contest_problem->problem_id]['wa_cnt'];
					// 计算first_ac对应的时间数
					$user[$contest_problem->problem_id]['first_ac']['penalty_text'] = PenaltyUtil::penalty_int_2_text($user[$contest_problem->problem_id]['first_ac']['penalty']);
				}else{
					// WA
					$user[$contest_problem->problem_id]['ac'] = false;
					// 直接获取比赛期间提交次数
					$user[$contest_problem->problem_id]['wa_cnt'] = Db::query("select count(solution_id) as cnt from solution where contest_id=".$this->contest->contest_id." and problem_id=".$contest_problem->problem_id." and user_id='".$user['user_id']."' and in_date >= '".$this->contest->start_time."' and in_date <= '".$this->contest->end_time."'")[0]['cnt'];
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
				if ($users[$j]['ac_cnt'] < $users[$j+1]['ac_cnt']) {
					$tmp = $users[$j];
					$users[$j] = $users[$j+1];
					$users[$j+1] = $tmp;
				}else if ($users[$j]['ac_cnt'] == $users[$j+1]['ac_cnt']){
					if ($users[$j]['penalty'] > $users[$j+1]['penalty']){
						$tmp = $users[$j];
						$users[$j] = $users[$j+1];
						$users[$j+1] = $tmp;
					}
				}
			}
		}


		$cellKey = array('A','B','C','D','E','F','G','H','I','J','K','L','M',
			'N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
			'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM',
			'AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

		/**
		 * A Rank
		 * B Username
		 * C Nickname
		 * D Accepted
		 * E Penalty
		 * F A
		 * G B
		 */
		$phpexcel = new \PHPExcel();
		$PHPSheet = $phpexcel->getActiveSheet(); //获得当前活动sheet的操作对象
//        $invalidCharacters = $PHPSheet->getInvalidCharacters();
//        $group->name = str_replace($invalidCharacters, '', $group->name);
//        $sheet_title = $group->name . '-' . $this->contest->title;
//		$PHPSheet->setTitle(subtext($sheet_title, 20));

		// 标题比赛名称
		$PHPSheet->setCellValue('A1', $group->name . '-' . $this->contest->title);
		$PHPSheet->mergeCells('A1:'.$cellKey[4+sizeof($contest_problems)].'1');
		$PHPSheet->getRowDimension('1')->setRowHeight('30');
		$PHPSheet->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$PHPSheet->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
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
			$PHPSheet->setCellValue($cellKey[6+$contest_problems[$i]->num].'2', $cellKey[$contest_problems[$i]->num]);
			$PHPSheet->getStyle($cellKey[6+$contest_problems[$i]->num].'2')->getFont()->setBold('true');
			$PHPSheet->getColumnDimension($cellKey[6+$contest_problems[$i]->num])->setWidth('12');
		}

		// 填写选手成绩
		for ($i = 0; $i < sizeof($users); ++$i) {
			$PHPSheet->setCellValue('A'.($i+3), $i+1);
			$PHPSheet->setCellValue('B'.($i+3), $users[$i]['user_id']);
			$PHPSheet->getStyle('B'.($i+3))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$PHPSheet->setCellValue('C'.($i+3), $users[$i]['user']['nick']);
			$PHPSheet->getStyle('C'.($i+3))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$PHPSheet->setCellValue('D'.($i+3), $users[$i]['ac_cnt']);
			$PHPSheet->getStyle('D'.($i+3))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			$PHPSheet->setCellValue('E'.($i+3), $users[$i]['mark']);
			$PHPSheet->getStyle('E'.($i+3))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            $PHPSheet->setCellValue('F'.($i+3), $users[$i]['penalty_text']);
            $PHPSheet->getStyle('F'.($i+3))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
			// 写入每题详情
			for ($j = 0; $j < sizeof($contest_problems); ++$j) {
				if (isset($users[$i][$contest_problems[$j]->problem_id]['first_ac'])) {

					$text = $users[$i][$contest_problems[$j]->problem_id]['first_ac']['penalty_text'];
					if ($users[$i][$contest_problems[$j]->problem_id]['wa_cnt'] > 0) {
						$text .= '(-'.$users[$i][$contest_problems[$j]->problem_id]['wa_cnt'].')';
					}
					$PHPSheet->setCellValue($cellKey[6+$contest_problems[$j]->num].($i+3), $text);

					$PHPSheet->getStyle($cellKey[6+$contest_problems[$j]->num].($i+3))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
					// 判断是否是一血
					if ($users[$i][$contest_problems[$j]->problem_id]['first_ac']['solution_id'] == $contest_problems[$j]['first_ac']['solution_id']) {
						$PHPSheet->getStyle($cellKey[6+$contest_problems[$j]->num].($i+3))->getFill()->getStartColor()->setARGB('008800');
					}else{
						$PHPSheet->getStyle($cellKey[6+$contest_problems[$j]->num].($i+3))->getFill()->getStartColor()->setARGB('A9F5AF');
					}

				}else{
					if ($users[$i][$contest_problems[$j]->problem_id]['wa_cnt'] > 0) {
						$PHPSheet->setCellValue($cellKey[6+$contest_problems[$j]->num].($i+3), '-'.$users[$i][$contest_problems[$j]->problem_id]['wa_cnt']);
						$PHPSheet->getStyle($cellKey[6+$contest_problems[$j]->num].($i+3))->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
						$PHPSheet->getStyle($cellKey[6+$contest_problems[$j]->num].($i+3))->getFill()->getStartColor()->setARGB('FF7A7A');
					}
				}
			}
		}

		$PHPWriter = \PHPExcel_IOFactory::createWriter($phpexcel,'Excel2007');

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="contest'.$this->contest->contest_id.'_'.$group->name.'_'.$this->contest->title.'.xlsx"');
		header('Cache-Control: max-age=0');

		$PHPWriter->save('php://output');
	}
}
