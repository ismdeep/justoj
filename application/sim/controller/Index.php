<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018-12-01
 * Time: 15:32
 */

namespace app\sim\controller;


use app\api\controller\Contest;
use app\api\model\ContestModel;
use app\api\model\SimModel;
use app\api\model\SolutionModel;
use app\api\model\SourceCodeModel;
use app\api\model\UserModel;
use app\extra\controller\BaseController;

class Index extends BaseController
{
    /**
     * @param string $id
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index($id = '')
    {
//        intercept(!$this->is_administrator, $this->lang['do_not_have_privilege']);
        intercept('' == $id, 'null');
        $sim = (new SimModel())->where('s_id', $id)->find();
        $sim->s_src = (new SourceCodeModel())->where('solution_id', $sim->s_id)->find();
        $sim->s_src->source = htmlspecialchars($sim->s_src->source);
        $sim->s_solution = (new SolutionModel())->where('solution_id', $sim->s_id)->find();
        $sim->s_solution->contest = (new ContestModel())->where('contest_id', $sim->s_solution->contest_id)->find();
        $sim->s_solution->user = (new UserModel())->where('user_id', $sim->s_solution->user_id)->find();

        $sim->sim_s_src = (new SourceCodeModel())->where('solution_id', $sim->sim_s_id)->find();
        $sim->sim_s_src->source = htmlspecialchars($sim->sim_s_src->source);
        $sim->sim_s_solution = (new SolutionModel())->where('solution_id', $sim->sim_s_id)->find();
        $sim->sim_s_solution->contest = (new ContestModel())->where('contest_id', $sim->sim_s_solution->contest_id)->find();
        $sim->sim_s_solution->user = (new UserModel())->where('user_id', $sim->sim_s_solution->user_id)->find();

        $this->assign('title', "sim - {$id}");
        $this->assign('sim', $sim);
        return view($this->theme_root . '/sim');
    }
}