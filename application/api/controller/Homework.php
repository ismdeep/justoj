<?php


namespace app\api\controller;


use app\api\model\ContestModel;
use app\api\common\ApiBaseController;

class Homework extends ApiBaseController {

    public function search_json($search_key = '') {
        $homeworks = (new ContestModel())
            ->where('title', 'like', "%{$search_key}%")
            ->whereOr('contest_id', $search_key)
            ->limit(10)
            ->select();
        foreach ($homeworks as $homework) {
            /* @var $homework ContestModel */
            $homework->password = '******';
        }

        return json(['code' => 0, 'data' => $homeworks]);
    }
}
