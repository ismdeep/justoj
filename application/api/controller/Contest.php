<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/14
 * Time: 10:05 PM
 */

namespace app\api\controller;


use app\api\model\ContestModel;
use app\api\model\PrivilegeModel;
use app\extra\controller\ApiBaseController;

class Contest extends ApiBaseController
{
	/**
	 * 比赛认证
	 * @param $contest_id
	 * @param $contest_password
	 * @return string
	 * @throws \think\exception\DbException
	 */
	public function auth($contest_id,$contest_password) {
		// 判断contest是否存在
		$contest = ContestModel::get(['contest_id' => $contest_id]);
		if (!$contest) return json(['status' => 'error', 'msg' => $this->lang['contest_not_exists']]);

		// 判断当前用户是否登录
		if (!$this->loginuser) return json(['status' => 'error', 'msg' => $this->lang['not_login']]);

		// 判断密码
		if ($contest_password != $contest->password) return json(['status' => 'error', 'msg' => $this->lang['wrong_password']]);

		$privilege = new PrivilegeModel();
		$privilege->user_id = $this->loginuser->user_id;
		$privilege->rightstr = 'c'.$contest_id;
		$privilege->save();

		return json(['status' => 'success']);
	}

	/**
     * 修改 作业/比赛 类型
     */
	public function change_type($contest_id = null, $type = null) {
	    intercept_json(null == $contest_id, 'contest_id can not be null');
	    intercept_json(null == $type, 'type can not be null');
	    $this->need_root();

	    $contest = (new ContestModel())->where(['contest_id' => $contest_id])->find();
	    intercept_json(null == $contest, 'contest not found');
	    $contest->type = $type;
	    $contest->save();
	    return json(['status' => 'success']);
    }
}