<?php


namespace app\api\model;


use think\Model;

/**
 * 上传表
 *
 * Class UploadModel
 * @package app\api\model
 *
 * @property int id
 * @property string user_id
 * @property string oss_path
 * @property int file_size
 * @property \DateTime create_time
 * @property \DateTime update_time
 *
 */
class UploadModel extends Model {
    protected $table = 'upload';
}