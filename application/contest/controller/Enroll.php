<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/9/18
 * Time: 10:42 AM
 */

namespace app\contest\controller;


use app\api\model\ContestEnrollModel;
use app\api\model\ContestModel;
use app\api\model\UserModel;
use app\extra\controller\BaseController;

class Enroll extends BaseController
{
    /**
     * 注册比赛页面
     *
     * @param string $contest_id
     * @return \think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index($contest_id = '')
    {
        intercept('' == $contest_id, 'contest_id参数错误');
        $contest = (new ContestModel())->where(['contest_id' => $contest_id])->find();
        intercept(null == $contest, '比赛不存在');

        $this->assign('contest', $contest);
        $this->assign('contest_id', $contest_id);

        // 判断是否需要完善个人信息
        if (null == $this->loginuser) {
            $this->redirect("/login?redirect=" . urlencode("/contest/enroll?contest_id=".$contest_id));
        }

        $user = (new UserModel())->where(['user_id' => $this->loginuser->user_id])->find();

        $this->assign('need_complete_info', false);
        if (UserModel::need_complete_info($user)) {
            $this->assign('need_complete_info', true);
        }

        // 判断是否已经注册
        if (null != (new ContestEnrollModel())->where([
            'user_id' => $this->loginuser->user_id, 'contest_id' => $contest_id
            ])->find()) {
            $this->redirect("/contest?id={$contest_id}");
        }

        return view($this->theme_root . '/contest-enroll');
    }

    /**
     * 注册比赛 json接口
     * @param string $contest_id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function do_contest_enroll_post($contest_id = '')
    {
        intercept_json('' == $contest_id, 'contest_id参数不可为空');
        intercept_json(null == $this->loginuser, '尚未登录');
        intercept_json(UserModel::need_complete_info(
            (new UserModel())->where(['user_id' => $this->loginuser->user_id])->find()
        ), '请先完善个人信息');
        intercept_json(null != (new ContestEnrollModel())->where([
            'user_id' => $this->loginuser->user_id, 'contest_id' => $contest_id
            ])->find(), '你已经注册此比赛');

        $contest_enroll = new ContestEnrollModel();
        $contest_enroll->user_id = $this->loginuser->user_id;
        $contest_enroll->contest_id = $contest_id;
        $contest_enroll->save();

        return json([
            'status' => 'success',
            'msg' => '比赛注册成功'
        ]);
    }
}