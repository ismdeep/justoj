<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/7/29
 * Time: 7:29 PM
 */

namespace app\api\controller;

use app\api\model\LanguageDictionaryModel;
use app\api\model\SolutionModel;
use app\extra\controller\ApiBaseController;

class Langs extends ApiBaseController
{

    /**
     * 平台使用提交语言统计
     * @return \think\response\Json
     */
    public function statistics()
    {
        $c_cnt      = SolutionModel::where('language',0)->count();
        $cpp_cnt    = SolutionModel::where('language',1)->count();
        $java_cnt   = SolutionModel::where('language',3)->count();
        $python_cnt = SolutionModel::where('language',6)->count();
        return json([
            'data' => [
                [
                    'name' => 'C',
                    'value' => $c_cnt
                ],[
                    'name' => 'C++',
                    'value' => $cpp_cnt
                ],[
                    'name' => 'Java',
                    'value' => $java_cnt
                ],[
                    'name' => 'Python',
                    'value' => $python_cnt
                ]
            ]
        ]);
    }
}