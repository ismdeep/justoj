<?php
/**
 * Created by PhpStorm.
 * User: L. Jiang <l.jiang.1024@gmail.com>
 * Date: 2018/5/24
 * Time: 9:12
 */

namespace app\admin\controller;


use app\admin\common\AdminBaseController;
use think\Request;

class Fileupload extends AdminBaseController {
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->assign('nav', 'upload_file');
    }

    public function index() {
        return view();
    }
}