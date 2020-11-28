<?php


namespace app\contest\controller;


use app\api\model\ContestEnrollModel;
use app\api\model\ContestModel;
use app\api\model\UserModel;
use app\extra\controller\BaseController;

class Enroll extends BaseController {

    /**
     * 注册比赛页面
     *
     * @param string $id
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function show_contest_enroll_page($id = '') {
        // 判断是否需要完善个人信息
        if (null == $this->login_user) {
            $this->redirect("/login?redirect=" . urlencode("/contests/{$id}/enroll"));
        }

        $user = (new UserModel())->where(['user_id' => $this->login_user->user_id])->find();
        $contest = (new ContestModel())->where(['contest_id' => $id])->find();

        $this->assign('need_complete_info', false);
        if (UserModel::need_complete_info($user)) {
            $this->assign('need_complete_info', true);
        }

        // 判断是否已经注册
        if (null != (new ContestEnrollModel())->where([
                'user_id' => $this->login_user->user_id,
                'contest_id' => $id,
            ])->find()) {
            $this->redirect("/contests/{$id}");
        }

        return view('./contest-enroll', ['contest' => $contest]);
    }
}