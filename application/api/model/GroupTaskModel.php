<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/13
 * Time: 10:57 PM
 */

namespace app\api\model;


use think\Model;

/**
 * Class GroupTaskModel
 *
 * @package app\api\model
 *
 * @property int id
 * @property int group_id
 * @property string title
 * @property string link
 * @property \DateTime create_time
 * @property \DateTime update_time
 * @property int contest_id
 */
class GroupTaskModel extends Model {
    protected $table = 'group_task';
}