<?php /** @noinspection PhpUndefinedFieldInspection */

/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/8/4
 * Time: 下午6:33
 */

namespace app\user\controller;


use app\api\model\SolutionModel;
use app\api\model\UserModel;
use app\extra\controller\BaseController;

class Info extends BaseController
{
    /**
     *
     * @param $user
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index($user)
    {
        $user = UserModel::get(['user_id' => $user]);
        $rank = (new UserModel)->where('solved', '>', $user['solved'])->count() + 1;
        $ac_solutions = (new SolutionModel)->where('user_id', '=', $user->user_id)->order('problem_id', 'asc')->select();

        $ac_list = [];
        foreach ($ac_solutions as $ac) {
            if (!in_array($ac->problem_id, $ac_list) && $ac->problem_id > 0) {
                $ac_list[] = $ac->problem_id;
            }
        }
        $submit_count = (new SolutionModel())
            ->where('user_id', $user->user_id)
            ->whereNull('contest_id')
            ->count();
        return view('index', ['user' => $user, 'rank' => $rank, 'ac_list' => $ac_list, 'submit_count' => $submit_count]);
    }
}