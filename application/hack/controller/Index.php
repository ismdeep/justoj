<?php


namespace app\hack\controller;

use app\api\model\PrivilegeModel;
use app\api\model\UserModel;
use app\extra\controller\BaseController;
use think\Controller;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\response\Json;
use think\Session;

class Index extends BaseController {
    public function index() {
        return view('user_list');
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
            if ($this->is_login && $this->login_user->user_id == $user->user_id) {
                $user->is_login = true;
            } else {
                $user->is_login = false;
            }

            $user->privileges_text = '';
            // 判断是否是管理员administrator
            if (PrivilegeModel::get(['user_id' => $user->user_id, 'rightstr' => 'administrator'])) {
                $user->privileges_text = 'ADMIN';
            }

            // 判断是否是root账号
            if (PrivilegeModel::get(['user_id' => $user->user_id, 'rightstr' => 'root'])) {
                $user->privileges_text = 'ROOT';
            }
        }
        $count = $where->count();

        return json([
            'code' => 0,
            'data' => $users,
            'count' => $count
        ]);
    }

    /**
     * Hack login json api
     *
     * @param string $user_id
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    function hack_login_json($user_id = '') {
        intercept_json('' == $user_id, '');
        $user = (new UserModel())->where('user_id', $user_id)->find();
        intercept_json(null == $user, '');

        Session::set('user', null);
        Session::set('administrator', null);
        Session::set('root', null);

        Session::set('user', $user);

        // 判断是否是管理员administrator
        if (PrivilegeModel::get(['user_id' => $user_id, 'rightstr' => 'administrator'])) {
            Session::set('administrator', $user);
        }

        // 判断是否是root账号
        if (PrivilegeModel::get(['user_id' => $user_id, 'rightstr' => 'root'])) {
            Session::set('root', $user);
        }

        return json(['status' => 'success', 'msg' => '']);

    }
}