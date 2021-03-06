<?php
/**
 * Created by PhpStorm.
 * User: L. Jiang <l.jiang.1024@gmail.com>
 * Date: 2018/5/6
 * Time: 20:37
 */

namespace app\api\model;


use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Model;


/**
 * Class ProblemModel
 *
 * @property int problem_id
 * @property string title
 * @property string description
 * @property string input
 * @property string output
 * @property string sample_input
 * @property string sample_output
 * @property int spj
 * @property string hint
 * @property string source
 * @property \DateTime in_date
 * @property int time_limit
 * @property int memory_limit
 * @property string defunct
 * @property int accepted
 * @property int submit
 * @property int solved
 * @property string owner_id
 * @property string tags
 * @property \DateTime create_time
 * @property \DateTime update_time
 *
 * @package app\api\model
 */
class ProblemModel extends Model {
    protected $table = 'problem';

    public function fk() {
        $this->solved = Db::query("select count(solution_id) as cnt from solution where problem_id=" . $this->problem_id . " and contest_id is null and result=4")[0]['cnt'];
        $this->submit = Db::query("select count(solution_id) as cnt from solution where problem_id=" . $this->problem_id . " and contest_id is null")[0]['cnt'];
    }

    /**
     * Update ac/submit count
     *
     * @param string $problem_id
     * @throws Exception
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    static public function update_ac_cnt($problem_id = '') {
        /* @var $problem ProblemModel */
        $problem = (new ProblemModel())->where('problem_id', $problem_id)->find();
        if ($problem) {
            $problem->submit = (new SolutionModel())
                ->where('contest_id', null)
                ->where('problem_id', $problem_id)->count();

            $problem->accepted = (new SolutionModel())
                ->where('contest_id', null)
                ->where('problem_id', $problem_id)
                ->where('result', 4)->count();
            $problem->save();
        }
    }
}
