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

    const TYPE_CONTEST = 0;
    const TYPE_HOMEWORK = 1;

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

    /**
     * 获取参赛选手ID列表
     */
    public function get_user_ids() {
        $user_ids = [];
        $solutions = (new SolutionModel())->distinct(true)->field('user_id')->where('contest_id', $this->contest_id)->select();
        foreach ($solutions as $solution) {
            /* @var $solution SolutionModel */
            $user_ids []= $solution->user_id;
        }
        return $user_ids;
    }

    /**
     * 获取参赛选手users
     */
    public function get_users() {
        return (new UserModel())->where('user_id', 'in', function($query){
            $query->table('solution')->where(['contest_id' => $this->contest_id])->distinct(true)->field('user_id');
        })->select();
    }

    /**
     * 获取旅游队(打星号)选手ID列表
     */
    public function get_tourist_user_ids() {
        $tourist_user_ids = [];
        $contest_tourists = (new ContestTouristModel())->where(['contest_id' => $this->contest_id])->select();
        foreach ($contest_tourists as $contest_tourist) {
            /* @var $contest_tourist ContestTouristModel */
            $tourist_user_ids []= $contest_tourist->user_id;
        }
        return $tourist_user_ids;
    }

    /**
     * 获取本场比赛的有效提交Solutions
     */
    public function get_significant_solutions() {
        return (new SolutionModel())
            ->where('in_date', '>=', $this->start_time)
            ->where('in_date', '<=', $this->end_time)
            ->where('contest_id', $this->contest_id)
            ->order('in_date', 'asc')
            ->select();
    }

    /**
     * 获取本场比赛的赛题列表
     *
     * @return bool|false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_contest_problems() {
        return (new ContestProblemModel())->where(['contest_id' => $this->contest_id])->order('num', 'asc')->select();
    }
}