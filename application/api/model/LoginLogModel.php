<?php


namespace app\api\model;


use think\Model;

/**
 *
 * Class LoginLogModel
 * @package app\api\model
 * @property int id
 * @property string user_id
 * @property string ip
 * @property string user_agent
 * @property int result
 * @property \DateTime create_time
 * @property \DateTime update_time
 */
class LoginLogModel extends Model
{
    protected $table = 'loginlog';

    static function push($user_id, $ip, $user_agent, $result)
    {
        $login_log = new LoginLogModel();
        $login_log->user_id = $user_id;
        $login_log->ip = $ip;
        $login_log->user_agent = $user_agent;
        $login_log->result = $result;
        $login_log->save();
    }
}
