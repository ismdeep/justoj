<?php


namespace app\api\model;


use DateTime;
use think\Model;

/**
 * Class PasswordResetLinkModel
 * @package app\api\model
 *
 * @property int id
 * @property string user_id
 * @property string uuid
 * @property DateTime create_time
 * @property DateTime update_time
 */
class PasswordResetLinkModel extends Model {
    protected $table = 'password_reset_links';
}