<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/7
 * Time: 19:50
 */

namespace app\api\model;


use think\Model;

/**
 * Class PrivilegeModel
 *
 * @package app\api\model
 *
 * @property string user_id
 * @property string rightstr
 * @property string defunct
 * @property \DateTime create_time
 * @property \DateTime update_time
 */
class PrivilegeModel extends Model
{
    protected $table = 'privilege';
}