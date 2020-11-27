<?php


namespace app\api\model;


use think\Model;

/**
 * Class EmailCodeModel
 * @package app\api\model
 *
 * @property int id
 * @property string email
 * @property string code
 * @property \DateTime create_time
 * @property \DateTime update_time
 *
 */
class EmailCodeModel extends Model {
    protected $table = 'email_codes';
}