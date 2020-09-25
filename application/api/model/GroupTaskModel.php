<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
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

    public function getProblemIds() {
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
        /* @todo 需要完善只筛选在作业合法时间段提交的完成数量 */
        $problem_ids = $this->getProblemIds();
        return (new SolutionModel())
            ->where('contest_id', $this->contest_id)
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
        /* @todo 需要完善只筛选在作业合法时间段提交的完成数量 */
        /* 获取当前作业题目ID */
        $problem_ids = $this->getProblemIds();
        return (new SolutionModel())
            ->where('contest_id', $this->contest_id)
            ->whereIn('problem_id', $problem_ids)
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