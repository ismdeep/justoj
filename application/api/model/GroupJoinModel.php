<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/10
 * Time: 7:49 PM
 */

namespace app\api\model;


use think\Model;

/**
 * Class GroupJoinModel
 * @package app\api\model
 *
 * @property int id
 * @property string user_id
 * @property int group_id
 * @property int status
 * @property int deleted
 * @property \DateTime create_time
 * @property \DateTime update_time
 */
class GroupJoinModel extends Model {
    protected $table = 'group_join';

    public function get_group() {
        return (new GroupModel())->where(['id' => $this->group_id])->find();
    }

    public function fk() {
        $this->group = $this->get_group();
    }
}
