<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/7
 * Time: 20:47
 */

namespace app\extra\controller;


use app\api\model\UiLanuageModel;
use app\api\model\UserModel;
use think\Config;
use think\Controller;
use think\Request;
use think\Session;

class BaseController extends Controller
{
    public $is_administrator;
    public $is_root;
    public $loginuser;
    public $is_login;

    public $show_ui_lang;
    public $lang;

    public $show_browser_banner;


    /**
     * 获取可以使用的语言列表
     */
    public function allowed_langs()
    {
        $tmps = Config::get('langs');
        $allowed_langs = [];
        foreach ($tmps as $tmp) {
            if ($tmp['allowed']) {
                $allowed_langs[] = $tmp;
            }
        }
        return $allowed_langs;
    }

    /**
     * BaseController constructor.
     * @param Request|null $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function __construct(Request $request = null)
    {
        parent::__construct($request);

        // 判断User-Agent
        $this->show_browser_banner = false;
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, "MSIE 8") || strpos($user_agent, "MSIE 7") || strpos($user_agent, "MSIE 6") || strpos($user_agent, "MSIE 5")) {
            $this->show_browser_banner = true;
        }
        $this->assign('show_browser_banner', $this->show_browser_banner);

        // 初始化当前登录用户变量
        $this->loginuser = null;
        $this->is_login = false;
        if (session('user')) {
            $this->loginuser = session('user');
            $this->loginuser = (new UserModel())->where(['user_id' => $this->loginuser->user_id])->find();
            $this->is_login = true;
        }
        $this->assign('is_login', $this->is_login);
        $this->assign('loginuser', $this->loginuser);

        // 赋予管理员权限
        $this->is_administrator = false;
        if (Session::get('administrator')) $this->is_administrator = true;
        $this->assign('is_administrator', $this->is_administrator);

        // 赋予超级管理员root权限
        $this->is_root = false;
        if (Session::get('root')) $this->is_root = true;
        $this->assign('is_root', $this->is_root);

        // 设置用户UI语言
        $dicts = Config::get('lang_dict');
        $this->show_ui_lang = 'en'; // 默认语言
        if (!$this->loginuser) {
            if (Session::get('ui_language')) $this->show_ui_lang = Session::get('ui_language');
        } else {
            $ui_language_obj = UiLanuageModel::get(['user_id' => $this->loginuser->user_id]);
            if (!$ui_language_obj) {
                $ui_language_obj = new UiLanuageModel();
                $ui_language_obj->user_id = $this->loginuser->user_id;
                $ui_language_obj->language = 'en';
                $ui_language_obj->save();
            }
            $this->show_ui_lang = $ui_language_obj->language;
        }
        $this->lang = array();
        foreach ($dicts as $key => $dict) {
            $this->lang[$key] = $dict[$this->show_ui_lang];
        }
        $this->assign('lang', $this->lang);

        // 初始化nav变量控制导航栏class="active"
        $this->assign('nav', 'home');

        // 初始化solution_result_code
        $solution_result_color = array(
            'result_code_pending' => 'black',
            'result_code_rejuding' => 'black',
            'result_code_compiling' => 'black',
            'result_code_running' => 'black',
            'result_code_ac' => 'green',
            'result_code_pe' => 'black',
            'result_code_wa' => 'red',
            'result_code_tle' => 'red',
            'result_code_mle' => 'red',
            'result_code_ole' => 'red',
            'result_code_re' => 'red',
            'result_code_ce' => 'red',
            'result_code_co' => 'black',
            'result_code_tr' => 'black',
            'result_code_so' => 'black'
        );
        $this->assign('solution_result_color', $solution_result_color);

        $this->assign('need_edit_profile', false);
        if ($this->loginuser && UserModel::need_complete_info((new UserModel())->where(['user_id' => $this->loginuser->user_id])->find())) {
            $this->assign('need_edit_profile', true);
        }
    }

    public function need_root($type = 'json')
    {
        if (!$this->is_root) {
            if ('json' == $type) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'msg' => $this->lang['do_not_have_privilege']]);
            } else {
                echo $this->lang['do_not_have_privilege'];
            }
            die();
        }
    }

    public function need_admin($type = 'json')
    {
        if (!$this->is_administrator) {
            if ('json' == $type) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'msg' => $this->lang['do_not_have_privilege']]);
            } else {
                echo $this->lang['do_not_have_privilege'];
            }
            die();
        }
    }

    public function need_login($type = 'json')
    {
        if (!$this->loginuser) {
            if ('json' == $type) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'msg' => $this->lang['not_login']]);
            } else {
                echo $this->lang['not_login'];
            }
            die();
        }
    }
}