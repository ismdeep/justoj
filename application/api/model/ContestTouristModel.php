<?php


namespace app\api\model;


use think\Model;

/**
 * Class ContestTouristModel
 * @package app\api\model
 *
 * @property int id
 * @property int contest_id
 * @property int user_id
 * @property \DateTime create_time
 * @property \DateTime update_time
 */
class ContestTouristModel extends Model {
    protected $table = 'contest_tourist';

    static public function tourists_in_contest($contest_id = '') {
        if ('' == $contest_id) {
            return [];
        }
        $contest_tourists = ContestTouristModel::all(['contest_id' => $contest_id]);
        $ans = [];
        foreach ($contest_tourists as $item) {
            $ans [] = $item->user_id;
        }
        return $ans;
    }
}