<?php

namespace app\training\controller;

use app\api\model\ProblemModel;
use app\api\model\SolutionModel;
use app\api\model\TrainingProblemModel;
use app\api\model\UserModel;
use app\training\common\TrainingBaseController;
use think\Db;
use think\Request;

class Index extends TrainingBaseController
{

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->assign('nav', 'training');
    }

    /**
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function index()
    {
        $training_problems = (new TrainingProblemModel())->paginate(10);
        foreach ($training_problems as $tp) {
            /* 获取题目内容 */
            $tp->problem = (new ProblemModel())->where('problem_id', $tp->problem_id)->find();

            /* 判断当前用户是否AC */
            $tp->ac = false;
            if ( (new SolutionModel())->where([
                    'problem_id' => $tp->problem_id,
                    'contest_id' => 1,
                    'result' => 4,
                    'user_id' => $this->loginuser->user_id,
                ])->find() != null ) {
                $tp->ac = true;
            }

            /* 获取提交数量 */
            $tp->submit_cnt = (new SolutionModel())->where([
                'problem_id' => $tp->problem_id,
                'contest_id' => 1
            ])->count();

            /* 获取 AC 数量 */
            $tp->ac_cnt = (new SolutionModel())->where([
                'problem_id' => $tp->problem_id,
                'contest_id' => 1,
                'result' => 4
            ])->count();
        }


        /* 获取个人信息 >>>> */
        $user_info = (new UserModel())->where('user_id', $this->loginuser->user_id)->find();
        /* <<<< 获取个人信息 */

        /* 获取当前周开始日期及结束日期 >>>> */
        //当前日期
        $currentDate = date("Y-m-d");
        //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
        $first=1;
        //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
        $w=date('w',strtotime($currentDate));
        //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
        $week_start=date('Y-m-d',strtotime("$currentDate -".($w ? $w - $first : 6).' days'));
        //本周结束日期
        $week_end=date('Y-m-d',strtotime("$week_start +6 days"));
        /* <<<< 获取当前周开始日期及结束日期 */

        /* 获取本周刷题数 >>>> */
        $current_week_ac_cnt = (new SolutionModel())
            ->where(['contest_id' => 1, 'result' => 4, 'first_training' => 1])
            ->where('create_time', '>=', $week_start . ' 00:00:00')
            ->where('create_time', '<=', $week_end . '23:59:59')
            ->count();
        /* <<<< 获取本周刷题数 */

        /* 获取上周刷题数 >>>> */
        /* <<<< 获取上周刷题数 */

        /* 获取本月刷题数 >>>> */
        /* <<<< 获取本月刷题数 */

        /* 获取本周排行榜 >>>> */
        $week_rank = Db::query("select user_id, count(solution_id) as cnt from solution where contest_id=1 and create_time >= '".$week_start." 00:00:00' and create_time <= '".$week_end." 23:59:59' and first_training=1 and result=4 group by user_id order by cnt desc limit 10");
        /* <<<< 获取本周排行榜 */

        return view($this->theme_root . '/training/index', [
            'training_problems' => $training_problems
            , 'user_info' => $user_info
            , 'current_week_ac_cnt' => $current_week_ac_cnt
            , 'week_rank' => $week_rank
            , 'week_rank_empty' => sizeof($week_rank) <= 0
        ]);
    }
}
