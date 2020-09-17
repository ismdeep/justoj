<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/9/14
 * Time: 11:19 PM
 */

namespace app\admin\controller;


use app\api\model\PrivilegeModel;
use app\api\model\UserModel;
use app\extra\controller\AdminBaseController;
use app\extra\util\PasswordUtil;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\response\Json;

class User extends AdminBaseController {
    /**
     * @return \think\response\View
     */
    public function user_list() {
        return view();
    }

    /**
     * @param int $page
     * @param int $limit
     * @param string $keyword
     * @return Json
     * @throws Exception
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function user_list_json($page = 1, $limit = 10, $keyword = '') {
        $where = (new UserModel());
        if ('' != $keyword) {
            $where = $where
                ->where(['user_id' => ['like', "%{$keyword}%"]])
                ->whereOr(['nick' => ['like', "%{$keyword}%"]])
                ->whereOr(['email' => ['like', "%{$keyword}%"]])
                ->whereOr(['nick' => ['like', "%{$keyword}%"]])
                ->whereOr(['phone' => ['like', "%{$keyword}%"]]);
        }

        $users = $where->limit(($page - 1) * $limit, $limit)->select();
        foreach ($users as $user) {
            $user->school = htmlspecialchars($user->school);
        }
        $count = $where->count();

        return json([
            'code' => 0,
            'data' => $users,
            'count' => $count
        ]);
    }

    /**
     * @return \think\response\View
     */
    public function admin_user_list() {
        return view();
    }

    /**
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function admin_user_list_json() {
        $privileges = (new PrivilegeModel())->where(['rightstr' => 'administrator'])->order('create_time', 'asc')->select();
        $user_ids = [];
        foreach ($privileges as $privilege) $user_ids[] = $privilege->user_id;
        $admin_users = (new UserModel())->where(['user_id' => ['in', $user_ids]])->order('reg_time', 'asc')->select();
        return json([
            'code' => 0,
            'msg' => '',
            'data' => $admin_users
        ]);
    }

    /**
     * 添加管理员 页面
     * @return \think\response\View
     */
    public function add_admin() {
        return view();
    }

    /**
     * 用户列表搜索 json接口
     * @param string $keyword
     * @param int $page
     * @param int $limit
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function user_search_by_keyword_json($keyword = '', $page = 1, $limit = 10) {
        $page = max(1, intval($page));
        $limit = max(1, intval($limit));
        $users = (new UserModel())->where(['user_id' => ['like', "%{$keyword}%"]])->whereOr(['nick' => ['like', "%{$keyword}%"]])->limit(($page - 1) * $limit, $limit)->select();
        $count = (new UserModel())->where(['user_id' => ['like', "%{$keyword}%"]])->whereOr(['nick' => ['like', "%{$keyword}%"]])->count();
        return json([
            'code' => 0,
            'count' => $count,
            'data' => $users
        ]);
    }

    /**
     * @param string $user_id
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function add_admin_privilege($user_id = '') {
        intercept_json('' == $user_id, 'user_id参数不可为空');
        $user = (new UserModel())->where(['user_id' => $user_id])->find();
        intercept_json(null == $user, '用户不存在');
        intercept_json(null != (new PrivilegeModel())->where([
                'user_id' => $user_id, 'rightstr' => 'administrator'
            ])->find(), '用户已经是管理员。');
        $privilege = new PrivilegeModel();
        $privilege->user_id = $user_id;
        $privilege->rightstr = 'administrator';
        $privilege->defunct = 'N';
        $privilege->save();
        return json([
            'status' => 'success',
            'msg' => '添加成功'
        ]);
    }

    /**
     * @param string $user_id
     * @return Json
     */
    public function remove_admin_privilege_json($user_id = '') {
        $this->need_root('json');
        intercept_json('' == $user_id, '参数错误');

        (new PrivilegeModel())->where(['user_id' => $user_id, 'rightstr' => 'administrator'])->delete();

        return json([
            'status' => 'success',
            'msg' => 'ok'
        ]);
    }


    /**
     * @param string $user_id
     * @return \think\response\View
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function change_password($user_id = '') {
        intercept_json('' == $user_id, 'user_id不可为空');
        $user = (new UserModel())->where('user_id', $user_id)->find();
        intercept_json(null == $user, '用户不存在');
        $this->assign('user', $user);
        return view();
    }

    /**
     * @param string $user_id
     * @param string $newpassword
     * @param string $newpassword2
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function change_password_json($user_id = '', $newpassword = '', $newpassword2 = '') {
        intercept_json(!$this->is_administrator, '没有操作权限');
        intercept_json('' == $user_id, 'user_id不可为空');
        intercept_json('' == $newpassword || $newpassword != $newpassword2, '密码不可为空且两次输入的密码必须相同。');
        $user = (new UserModel())->where('user_id', $user_id)->find();
        intercept_json(null == $user, '用户不存在');

        // 权限拦截（超级管理员可以修改任何人密码，管理员只能修改普通用户密码。）
        intercept_json(!$this->is_root && $this->login_user->user_id != $user_id && null != (new PrivilegeModel())->where(['user_id' => $user_id, 'rightstr' => 'administrator'])->find(), '没有操作权限');

        $user->password = PasswordUtil::gen_password($newpassword);
        $user->save();
        return json([
            'status' => 'success',
            'msg' => '密码修改成功'
        ]);
    }
}