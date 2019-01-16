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