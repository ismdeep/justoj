<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/10
 * Time: 13:36
 */

namespace app\api\model;


use think\Model;

class GroupModel extends Model
{
    protected $table = 'group';

    public function fk()
    {
        $this->teacher = UserModel::get(['user_id' => $this->ownner_id]);

        // group权限fk
		$this->type_text = '';
		switch ($this['type']) {
            case 0:
                $this->type_text = '公开';
                break;
            case 1:
                $this->type_text = '私有';
                break;
        }
    }
}