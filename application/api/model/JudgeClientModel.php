<?php


namespace app\api\model;


use think\Model;

/**
 * Class JudgeClientModel
 *
 * @package app\api\model
 *
 * @property int id
 * @property string client_name
 * @property string data_git_hash
 * @property \DateTime create_time
 * @property \DateTime update_time
 *
 */
class JudgeClientModel extends Model {
    protected $table = 'judge_client';
}