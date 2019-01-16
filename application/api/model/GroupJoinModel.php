<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/10
 * Time: 7:49 PM
 */

namespace app\api\model;


use think\Model;

class GroupJoinModel extends Model
{
	protected $table = 'group_join';

	public function fk()
	{
		$this->group = GroupModel::get(['id' => $this->group_id]);
	}
}