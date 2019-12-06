<?php


namespace app\api\controller;


use app\api\model\SolutionModel;
use app\extra\controller\ApiBaseController;

class Info extends ApiBaseController
{
    /***
     * 统计系统中各个状态对提交记录条数
     * @return \think\response\Json
     * @throws \think\Exception
     */
    public function solution_statistics()
    {

        $pending_cnt = (new SolutionModel())->where('result', 0)->count();
        $rejudging_cnt = (new SolutionModel())->where('result', 1)->count();
        $compiling_cnt = (new SolutionModel())->where('result', 2)->count();
        $running_cnt = (new SolutionModel())->where('result', 3)->count();
        $ac_cnt = (new SolutionModel())->where('result', 4)->count();
        $pe_cnt = (new SolutionModel())->where('result', 5)->count();
        $wa_cnt = (new SolutionModel())->where('result', 6)->count();
        $tle_cnt = (new SolutionModel())->where('result', 7)->count();
        $mle_cnt = (new SolutionModel())->where('result', 8)->count();
        $ole_cnt = (new SolutionModel())->where('result', 9)->count();
        $re_cnt = (new SolutionModel())->where('result', 10)->count();
        $ce_cnt = (new SolutionModel())->where('result', 11)->count();
        return json([
            'pending_cnt' => $pending_cnt,
            'rejudging_cnt' => $rejudging_cnt,
            'compiling_cnt' => $compiling_cnt,
            'running_cnt' => $running_cnt,
            'ac_cnt' => $ac_cnt,
            'pe_cnt' => $pe_cnt,
            'wa_cnt' => $wa_cnt,
            'tle_cnt' => $tle_cnt,
            'mle_cnt' => $mle_cnt,
            'ole_cnt' => $ole_cnt,
            're_cnt' => $re_cnt,
            'ce_cnt' => $ce_cnt
        ]);
    }
}