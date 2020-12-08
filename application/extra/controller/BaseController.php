<?php
/**
 * Created by PhpStorm.
 * User: L. Jiang <l.jiang.1024@gmail.com>
 * Date: 2018/5/7
 * Time: 20:47
 */

namespace app\extra\controller;


use app\api\model\UiLanuageModel;
use app\api\model\UserModel;
use think\Config;
use think\Controller;
use think\Env;
use think\Request;
use think\Session;

class BaseController extends Controller {

    /* @var $login_user UserModel */
    public $login_user = null;

    public $site_name;

    public $show_ui_lang;
    public $lang;

    public $show_browser_banner;

    public $theme_root = 'home@themes/bootstrap';

    /**
     * 获取可以使用的语言列表
     */
    public function allowed_langs() {
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
    public function __construct(Request $request = null) {
        parent::__construct($request);

        $this->site_name = Env::get('config.site_name', 'JustOJ');
        $this->assign('site_name', $this->site_name);

        $this->theme_root = 'home@themes/bootstrap';

        $this->assign('admin_email', Env::get('config.admin_email'));
        $this->assign('theme_root', $this->theme_root);

        // 判断User-Agent
        $this->show_browser_banner = false;
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (
            strpos($user_agent, "QIHOO")
            || strpos($user_agent, "MSIE 10")
            || strpos($user_agent, "MSIE 9")
            || strpos($user_agent, "MSIE 8")
            || strpos($user_agent, "MSIE 7")
            || strpos($user_agent, "MSIE 6")
            || strpos($user_agent, "MSIE 5")
        ) {
            $this->show_browser_banner = true;
        }
        $this->assign('show_browser_banner', $this->show_browser_banner);

        /* 初始化当前登录用户变量 */
        $this->login_user = session('user');
        $this->assign('login_user', $this->login_user);

        // 设置用户UI语言
        $dicts = Config::get('lang_dict');
        $this->show_ui_lang = 'cn'; // 默认语言
        $this->show_ui_lang = $this->login_user ? $this->login_user->ui_lang : 'cn'; /* 默认为 cn */
        $this->assign('show_ui_lang', $this->show_ui_lang);

        $this->lang = [];
        foreach ($dicts as $key => $dict) {
            $this->lang[$key] = $dict[$this->show_ui_lang];
        }
        $this->assign('lang', $this->lang);

        // 初始化nav变量控制导航栏class="active"
        $this->assign('nav', 'home');
    }

    public function need_root($type = 'json') {
        if (!$this->login_user || !$this->login_user->is_root) {
            if ('json' == $type) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'msg' => $this->lang['do_not_have_privilege']]);
            } else {
                echo $this->lang['do_not_have_privilege'];
            }
            die();
        }
    }

    public function need_admin($type = 'json') {
        if (!$this->login_user || !$this->login_user->is_admin) {
            if ('json' == $type) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'msg' => $this->lang['do_not_have_privilege']]);
            } else {
                echo $this->lang['do_not_have_privilege'];
            }
            die();
        }
    }

    public function need_login($type = 'json') {
        if (!$this->login_user) {
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