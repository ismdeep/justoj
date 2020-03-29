<?php


namespace app\api\model;


use think\Model;

/**
 * @property int problem_id
 * @property mixed tag_id
 */
class ProblemTagModel extends Model {
    protected $table = "problem_tag";
}