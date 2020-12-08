<?php
/**
 * Created by PhpStorm.
 * User: L. Jiang <l.jiang.1024@gmail.com>
 * Date: 2018/5/9
 * Time: 8:42 PM
 */

namespace app\api\model;


use think\Db;
use think\Exception;
use think\Model;


/**
 * Class ContestProblemModel
 * @package app\api\model
 *
 * @property int problem_id
 * @property int contest_id
 * @property string title
 * @property int num
 * @property \DateTime create_time
 * @property \DateTime update_time
 *
 */
class ContestProblemModel extends Model {
    protected $table = 'contest_problem';

    /**
     * @return ProblemModel
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_problem() {
        /* @var $problem ProblemModel */
        $problem = (new ProblemModel())->where('problem_id', $this->problem_id)->find();
        return $problem;
    }

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function fk() {
        $this->problem = $this->get_problem();
        // 获得这场比赛中AC题目的人数
        $this->problem->ac_cnt = Db::query("select count(solution_id) as cnt from solution where contest_id=" . $this->contest_id . " and problem_id=" . $this->problem->problem_id . " and result=4")[0]["cnt"];
        $this->problem->submit_cnt = Db::query("select count(solution_id) as cnt from solution where contest_id=" . $this->contest_id . " and problem_id=" . $this->problem->problem_id)[0]["cnt"];
    }
}