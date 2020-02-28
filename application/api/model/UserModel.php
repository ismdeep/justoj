<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/6
 * Time: 6:48 PM
 */

namespace app\api\model;


use think\Db;
use think\Model;

/**
 * Class UserModel
 * @package app\api\model
 * @property string user_id
 * @property string password
 */
class UserModel extends Model
{
	protected $table = "users";
	public function fk()
	{
		$this->submit_ac = Db::query("select count(solution_id) as cnt from solution where user_id='".$this->user_id."' and result=4")[0]['cnt'];
		$this->submit_cnt = Db::query("select count(solution_id) as cnt from solution where user_id='".$this->user_id."'")[0]['cnt'];
		if ($this->submit_cnt <= 0) {
			$this->ac_rate = "0.000";
		}else{
			$this->ac_rate = number_format($this->submit_ac * 100.00 / $this->submit_cnt,3);
		}
	}

    /**
     * Update user ac/submit count
     *
     * @param string $user_id
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
	static public function update_ac_cnt($user_id = '') {
        $user = (new UserModel())
            ->where('user_id', $user_id)
            ->find();
        if (null != $user) {
            $user->submit = (new SolutionModel())
                ->where('user_id', $user_id)
                ->whereNull('contest_id')
                ->count();
            $user->solved = (new SolutionModel())
                ->where('user_id', $user_id)
                ->whereNull('contest_id')
                ->where('result', 4)
                ->count('distinct problem_id');
            $user->save();
        }
    }

	static public function need_complete_info($user) {
	    if ('' == $user->email) {
	        return true;
        }

        if ('' == $user->nick) {
            return true;
        }

        if ('' == $user->school) {
            return true;
        }

        if ('' == $user->academy) {
            return true;
        }

        if ('' == $user->phone) {
            return true;
        }

        return false;
    }

}