<?php
/**
 * Created by PhpStorm.
 * User: L. Jiang <l.jiang.1024@gmail.com>
 * Date: 2018/5/15
 * Time: 1:56 PM
 */

namespace app\admin\controller;


use app\api\model\ProblemModel;
use app\extra\controller\AdminBaseController;
use think\Request;

class Problems extends AdminBaseController {
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->assign('nav', 'problem');
    }

    public function index() {
        $problems = ProblemModel::where('')->order('problem_id', 'asc')->paginate(100);
        $this->assign('problems', $problems);
        return view();
    }
}