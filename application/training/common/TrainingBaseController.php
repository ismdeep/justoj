<?php
namespace app\training\common;

use app\extra\controller\UserBaseController;
use think\Request;

class TrainingBaseController extends UserBaseController
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        if (null == $this->loginuser) {
            $this->redirect('/login?redirect=%2Ftraining');
        }
    }
}