<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/6
 * Time: 20:37
 */

namespace app\api\model;


use think\Db;
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
class ProblemModel extends Model
{
    protected $table = 'problem';

    public function fk()
    {
        $this->solved = Db::query("select count(solution_id) as cnt from solution where problem_id=" . $this->problem_id . " and contest_id is null and result=4")[0]['cnt'];
        $this->submit = Db::query("select count(solution_id) as cnt from solution where problem_id=" . $this->problem_id . " and contest_id is null")[0]['cnt'];
    }
}
