<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/6
 * Time: 20:20
 */

namespace app\api\model;


use think\Model;

class NewsModel extends Model
{
    protected $table = "news";
    public function fk()
    {
        $this->user = UserModel::get(['user_id' => $this->user_id]);
        if ('N' == $this->defunct) {
            $this->defunct_text = '正常';
        }else{
            $this->defunct_text = '已禁用';
        }

    }
}