<?php
/**
 * Created by PhpStorm.
 * User: L. Jiang <l.jiang.1024@gmail.com>
 * Date: 2018/7/29
 * Time: 7:29 PM
 */

namespace app\api\controller;

use app\api\model\LanguageDictionaryModel;
use app\api\model\SolutionModel;
use app\api\common\ApiBaseController;

class Langs extends ApiBaseController {

    /**
     * 平台使用提交语言统计
     * @return \think\response\Json
     */
    public function statistics() {
        $c_cnt = (new SolutionModel())->whereIn('language', [0, 13])->count();
        $cpp_cnt = (new SolutionModel())->whereIn('language', [1, 14])->count();
        $java_cnt = (new SolutionModel())->where('language', 3)->count();
        $python_cnt = (new SolutionModel())->whereIn('language', [6, 18])->count();
        $other_cnt = (new SolutionModel())->whereNotIn('language', [0, 1, 3, 6, 13, 14, 18])->count();
        return json([
            'data' => [
                [
                    'name' => 'C',
                    'value' => $c_cnt
                ],
                [
                    'name' => 'C++',
                    'value' => $cpp_cnt
                ],
                [
                    'name' => 'Java',
                    'value' => $java_cnt
                ],
                [
                    'name' => 'Python',
                    'value' => $python_cnt
                ],
                [
                    'name' => 'Other',
                    'value' => $other_cnt
                ]
            ]
        ]);
    }
}