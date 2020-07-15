<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/8
 * Time: 14:13
 */

namespace app\api\model;


use think\Exception;
use think\Model;

/**
 * Class ContestModel
 * @package app\api\model
 *
 * @property int contest_id
 * @property string title
 * @property \DateTime start_time
 * @property \DateTime end_time
 * @property string defunct
 * @property string description
 * @property int private
 * @property string langmask
 * @property string password
 * @property int type
 * @property int is_need_enroll
 * @property \DateTime create_time
 * @property \DateTime update_time
 *
 */
class ContestModel extends Model {
    protected $table = 'contest';

    public function fk() {
        // 比赛状态有：未开始，进行中，已结束
        $this->status_text = '';
        $this->status_code = '';
        if ($this->start_time > date("Y-m-d H:i:s")) {
            $this->status_text = '未开始';
            $this->status_code = 'contest_code_pending';
        } else {
            if ($this->end_time > date("Y-m-d H:i:s")) {
                $this->status_text = '进行中';
                $this->status_code = 'contest_code_running';
            }
            if ($this->end_time < date("Y-m-d H:i:s")) {
                $this->status_text = '已结束';
                $this->status_code = 'contest_code_ended';
            }
        }

        // 比赛权限 0公开 1私有
        $privilege_code_arr = array('privilege_code_public', 'privilege_code_private');
        $this->privilege_code = $privilege_code_arr[$this->private];

        // 获取比赛创建者
        try {
            $this->manager_id = PrivilegeModel::where('rightstr', 'm' . $this->contest_id)->order('create_time', 'asc')->find()->user_id;
            $this->manager = UserModel::get(['user_id' => $this->manager_id]);
        } catch (Exception $e) {
            $this->manager_id = 'ismdeep';
            $this->manager = UserModel::get(['user_id' => $this->manager_id]);
        }
    }
}