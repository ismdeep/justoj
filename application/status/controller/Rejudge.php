<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2019-02-08
 * Time: 08:26
 */

namespace app\status\controller;


use app\api\model\SolutionModel;
use app\extra\controller\BaseController;

class Rejudge extends BaseController
{
    public function problem($id = '') {
        return view('problem', ['id' => $id]);
    }

    public function problem_json($id = '') {

        $total_cnt = (new SolutionModel())
            ->where('problem_id', $id)
            ->where('contest_id', null)
            ->count();

        $done_cnt = (new SolutionModel())
            ->where('problem_id', $id)
            ->where('contest_id', null)
            ->where('result', '>=', 4)
            ->count();

        $percent_text = ($done_cnt * 100.0) / $total_cnt;
        $percent_text .= '%';

        return json([
            'retcode' => 0,
            'retmsg' => '操作成功',
            'retdata' => [
                'total_cnt' => $total_cnt,
                'done_cnt' => $done_cnt,
                'percent_text' => $percent_text
            ]
        ]);
    }
}