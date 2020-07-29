<?php


namespace app\api\model;


use think\Model;


/**
 * Class ProblemTagDictModel
 * @package app\api\model
 *
 * @property string tag_id 标签ID
 * @property string tag_name_cn 标签名称中文
 * @property string tag_name_en 标签名称英文
 * @property int cnt 数量
 * @property \DateTime create_time
 * @property \DateTime update_time
 *
 */
class ProblemTagDictModel extends Model {
    protected $table = 'problem_tag_dict';

    /**
     * 更新标签的题目数量
     * @param $tag_id
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    static public function update_cnt($tag_id) {
        /* @var $problem_tag_dict ProblemTagDictModel */
        $problem_tag_dict = (new ProblemTagDictModel())
            ->where('tag_id', $tag_id)->find();
        if (null == $problem_tag_dict) {
            return;
        }

        $problem_tag_dict->cnt = (new ProblemTagModel())
            ->where('tag_id', $tag_id)->count();
        $problem_tag_dict->save();
    }

    /**
     * 获取标签名称
     * @param $language
     * @return string
     */
    public function getTagName($language) {
        if ($language == 'cn') {
            return $this->tag_name_cn;
        }
        return $this->tag_name_en;
    }
}