<?php
namespace app\index\controller;

use app\api\model\NewsModel;
use app\api\model\UserModel;
use app\extra\controller\UserBaseController;
use think\Controller;

class Index extends UserBaseController
{
    public function index()
    {
        // 获取新闻列表
        $this->assign('newss', NewsModel::where('defunct','N')->order('time', 'desc')->select());
        return view();
    }
}
