<?php
/**
 * Created by PhpStorm.
 * User: L. Jiang <l.jiang.1024@gmail.com>
 * Date: 2018/5/13
 * Time: 10:57 PM
 */

namespace app\api\model;


use think\Model;

/**
 * Class GroupTaskModel
 *
 * @package app\api\model
 *
 * @property int id
 * @property int group_id
 * @property string title
 * @property string link
 * @property \DateTime create_time
 * @property \DateTime update_time
 * @property int contest_id
 */
class GroupTaskModel extends Model {
    protected $table = 'group_task';

    public function get_start_time() {
        /* @var $contest ContestModel */
        $contest = (new ContestModel())->where('contest_id', $this->contest_id)->find();
        return $contest->start_time;
    }

    public function get_end_time() {
        /* @var $contest ContestModel */
        $contest = (new ContestModel())->where('contest_id', $this->contest_id)->find();
        return $contest->end_time;
    }

    public function get_problem_ids() {
        $problem_ids = [];
        $contest_problems = (new ContestProblemModel())->where('contest_id', $this->contest_id)->select();
        foreach ($contest_problems as $contest_problem) {
            /* @var $contest_problem ContestProblemModel */
            $problem_ids [] = $contest_problem->problem_id;
        }
        return $problem_ids;
    }

    /**
     * 获取班级平均完成题目数
     */
    public function getTotalDoneCnt() {
        $problem_ids = $this->get_problem_ids();
        $start_time = $this->get_start_time();
        $end_time = $this->get_end_time();
        return (new SolutionModel())
            ->where('contest_id', $this->contest_id)
            ->whereBetween('in_date', [$start_time, $end_time])
            ->whereIn('problem_id', $problem_ids)
            ->where('result', SolutionModel::RESULT_AC)
            ->group('problem_id,user_id')
            ->count();
    }

    /**
     * 获取学生完成题目数
     *
     * @param string $user_id 用户ID
     * @return int|string
     * @throws \think\Exception
     */
    public function getDoneCntByUserId($user_id = '') {
        $problem_ids = $this->get_problem_ids();
        $start_time = $this->get_start_time();
        $end_time = $this->get_end_time();
        return (new SolutionModel())
            ->where('contest_id', $this->contest_id)
            ->whereIn('problem_id', $problem_ids)
            ->whereBetween('in_date', [$start_time, $end_time])
            ->where('user_id', $user_id)
            ->where('result', SolutionModel::RESULT_AC)
            ->group('problem_id')
            ->count('problem_id');
    }

    /**
     * 获取题目数量
     */
    public function getProblemCnt() {
        return (new ContestProblemModel())
            ->where('contest_id', $this->contest_id)
            ->count();
    }
}