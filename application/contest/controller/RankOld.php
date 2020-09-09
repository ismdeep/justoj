<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/9
 * Time: 11:16 PM
 */

namespace app\contest\controller;


use app\api\model\ContestProblemModel;
use app\api\model\ContestTouristModel;
use app\api\model\GroupModel;
use app\api\model\SolutionModel;
use app\api\model\UserModel;
use app\extra\controller\ContestBaseController;
use app\extra\util\PenaltyUtil;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Request;

class RankOld extends ContestBaseController {
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->assign('nav', 'rank');
        if (!(($this->permitted && $this->contest_started) || $this->is_administrator)) {
            $this->redirect('/contest?id=' . $this->contest->contest_id);
        }

        if (!$this->permitted) {
            $this->redirect('/contest?id=' . $this->contest_id);
        }
    }




    /**
     * @param string $id
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function export_register_users($id = '') {
        intercept(null == $id || '' == $id, 'id can not be empty');

        // 获取当前已有提交的所有用户名$users
        $users_tmp = Db::query("select user_id from contest_enroll where contest_id=" . $this->contest->contest_id);
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
        $spreadsheet = new Spreadsheet();
        $PHPSheet = $spreadsheet->getActiveSheet();

        // 标题比赛名称
        $PHPSheet->setCellValue('A1', $this->contest->title);
        $PHPSheet->getRowDimension('1')->setRowHeight('30');
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
            $PHPSheet->setCellValue('A' . ($i + 3), $i + 1);
            $PHPSheet->setCellValue('B' . ($i + 3), $users[$i]['user_id']);
            $PHPSheet->setCellValue('C' . ($i + 3), $users[$i]['user']['realname']);
            $PHPSheet->setCellValue('D' . ($i + 3), $users[$i]['user']['school']);
            $PHPSheet->setCellValue('E' . ($i + 3), $users[$i]['user']['class']);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="contest' . $this->contest->contest_id . '_' . $this->contest->title . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }
}
