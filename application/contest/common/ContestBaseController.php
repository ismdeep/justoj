<?php
/**
 * Created by PhpStorm.
 * User: L. Jiang <l.jiang.1024@gmail.com>
 * Date: 2018/5/9
 * Time: 8:19 PM
 */

namespace app\contest\common;

use app\api\model\ContestEnrollModel;
use app\api\model\ContestModel;
use app\api\model\GroupJoinModel;
use app\api\model\GroupTaskModel;
use app\api\model\PrivilegeModel;
use app\home\common\HomeBaseController;
use think\Request;

class ContestBaseController extends HomeBaseController {
    public $contest_id;
    /* @var $contest ContestModel */
    public $contest;
    public $contest_started;
    public $contest_ended;
    public $permitted;
    public $is_contest_manager;

    /**
     * ContestBaseController constructor.
     *
     * @param Request|null $request
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function __construct(Request $request = null, $id) {
        parent::__construct($request);

        $this->contest_id = intval($id);

        // 分配一个字母数组
        $this->assign('alphabet', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');

        // 获取contest信息
        $this->contest = (new ContestModel())->where(['contest_id' => $this->contest_id])->find();
        intercept(null == $this->contest, $this->lang['contest_not_exists']);
        $this->assign('contest', $this->contest);

        // 判断比赛是否已经开始
        $this->contest_started = $this->contest->start_time <= date("Y-m-d H:i:s");
        $this->assign('contest_started', $this->contest_started);

        /* 判断比赛是否已经结束 */
        $this->contest_ended = $this->contest->end_time < date("Y-m-d H:i:s");
        $this->assign('contest_ended', $this->contest_ended);

        /* 判断是否是比赛管理员 */
        $this->is_contest_manager = false;
        if ($this->login_user && $this->login_user->is_root && PrivilegeModel::get(['user_id' => $this->login_user->user_id, 'rightstr' => 'm' . $this->contest_id])) {
            $this->is_contest_manager = true;
        }
        if ($this->login_user && $this->login_user->is_root) {
            $this->is_contest_manager = true;
        }
        $this->assign('is_contest_manager', $this->is_contest_manager);

        /************ 访问权限拦截 begin ************/
        $this->permitted = false; // 默认没有访问权限

        /* 比赛是公开状态，并且已经开始 */
        if (0 == $this->contest->private && $this->contest_started && 'N' == $this->contest->defunct) {
            $this->permitted = true;
        }

        /* 如果此比赛绑定了group，并且判断当前用户是否已经加入了这些班级 */
        if ($this->login_user && $this->login_user->is_root) {
            $group_tasks = GroupTaskModel::all(['contest_id' => $this->contest_id]);
            foreach ($group_tasks as $group_task) {
                if (GroupJoinModel::get(['user_id' => $this->login_user->user_id, 'group_id' => $group_task->group_id, 'status' => 1])) {
                    $this->permitted = true;
                }
            }
        }

        /* 如果当前用户登录，判断当前用户在privilege中是否有此比赛的对应记录 */
        if ($this->login_user && $this->login_user->is_root) {
            if (PrivilegeModel::get(['user_id' => $this->login_user->user_id, 'rightstr' => 'c' . $this->contest_id, 'defunct' => 'N'])) {
                $this->permitted = true;
            }
        }

        $this->assign('permitted', $this->permitted);
        /************ 访问权限拦截 end   ************/

        /* 注册拦截，如果比赛是需要注册的，并且当前用户并没有注册比赛，则跳转至注册页面 */
        if ($this->contest->is_need_enroll) {
            if (null == $this->login_user) {
                $this->redirect("/login?redirect=" . urlencode("/contest?id=" . $this->contest_id));
            }

            if (null == (new ContestEnrollModel())->where(['user_id' => $this->login_user->user_id, 'contest_id' => $this->contest_id])->find()) {
                $this->redirect("/contests/{$this->contest_id}/enroll");
            }
        }
    }
}
