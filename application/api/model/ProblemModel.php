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

class ProblemModel extends Model
{
    protected $table = 'problem';
    public function fk()
    {
        $this->solved = Db::query("select count(solution_id) as cnt from solution where problem_id=".$this->problem_id." and contest_id is null and result=4")[0]['cnt'];
		$this->submit = Db::query("select count(solution_id) as cnt from solution where problem_id=".$this->problem_id." and contest_id is null")[0]['cnt'];
    }
}
