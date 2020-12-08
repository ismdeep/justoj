<?php
/**
 * Created by PhpStorm.
 * User: L. Jiang <l.jiang.1024@gmail.com>
 * Date: 2018/5/6
 * Time: 21:12
 */

namespace app\api\model;


use think\Model;

/**
 * Class SourceCodeModel
 * @package app\api\model
 *
 * @property string source
 *
 */
class SourceCodeModel extends Model {
    protected $table = 'source_code';
}