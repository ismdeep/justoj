<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/10
 * Time: 13:36
 */

namespace app\api\model;


use think\Exception;
use think\Model;

/**
 * @property string id
 * @property string name
 * @property string owner_id
 * @property int type
 * @property string password
 * @property string description
 * @property int deleted
 * @property \DateTime create_time
 * @property \DateTime update_time
 */
class GroupModel extends Model
{
    protected $table = 'group';

    public function fk()
    {
        $this->teacher = UserModel::get(['user_id' => $this->owner_id]);

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

    public function copy_task_from_homework($homework_id, $start_time, $end_time) {
        /* 获取作业信息 */
        /* @var $homework_from ContestModel */
        $homework_from = (new ContestModel())->where('contest_id', $homework_id)->find();
        if (!$homework_from) {
            throw new Exception("作业不存在");
        }

        /* 创建作业 */
        $homework = new ContestModel();
        $homework->title = $homework_from->title;
        $homework->start_time = $start_time;
        $homework->end_time = $end_time;
        $homework->defunct = 'N';
        $homework->description = $homework_from->description;
        $homework->private = 0;
        $homework->type = ContestModel::TYPE_HOMEWORK;
        $homework->save();

        /* 获取作业题目列表 */
        $homework_from_problems = (new ContestProblemModel())->where('contest_id', $homework_from->contest_id)->select();
        $problem_ids = [];
        foreach ($homework_from_problems as $homework_from_problem) {
            /* @var $homework_from_problem ContestProblemModel */
            $problem_ids []= $homework_from_problem->problem_id;
        }

        $homework->set_problems($problem_ids);

        /* 赋予当前用户于比赛管理权限 */
        $privilege = new PrivilegeModel();
        $privilege->user_id = $this->owner_id;
        $privilege->rightstr = 'm' . $homework->contest_id;
        $privilege->defunct = 'N';
        $privilege->save();

        // 关联比赛与班级
        $group_task = new GroupTaskModel();
        $group_task->group_id = $this->id;
        $group_task->title = $homework->title;
        $group_task->contest_id = $homework->contest_id;
        $group_task->save();
    }

    public function copy_tasks_from_group($group_id, $start_time, $end_time) {
        /* @var $group_from GroupModel */
        $group_from = (new GroupModel())->where('id', $group_id)->find();
        if (!$group_from) {
            throw new Exception("班级不存在");
        }

        $group_tasks = (new GroupTaskModel())->where(['group_id' => $group_from->id])->select();
        foreach ($group_tasks as $group_task) {
            /* @var $group_task GroupTaskModel */
            $this->copy_task_from_homework($group_task->contest_id, $start_time, $end_time);
        }
    }

}