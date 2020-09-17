<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/6
 * Time: 20:20
 */

namespace app\api\model;


use think\Model;


/**
 * Class NewsModel
 *
 * @package app\api\model
 *
 * @property int id
 * @property string user_id
 * @property string title_cn
 * @property string content_cn
 * @property string title_en
 * @property string content_en
 * @property string defunct
 * @property \DateTime create_time
 * @property \DateTime update_time
 *
 */
class NewsModel extends Model {
    protected $table = "news";

    public function getUser() {
        return (new UserModel())->get(['user_id' => $this->user_id]);
    }

    public function fk() {
        $this->user = $this->getUser();
        if ('N' == $this->defunct) {
            $this->defunct_text = '正常';
        } else {
            $this->defunct_text = '已禁用';
        }
    }

    public function getTitle($lang = 'en') {
        if ($lang == 'en') {
            return $this->title_en;
        }
        return $this->title_cn;
    }

    public function getContent($lang = 'en') {
        if ($lang == 'en') {
            return $this->content_en;
        }
        return $this->content_cn;
    }

}