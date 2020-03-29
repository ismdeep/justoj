<?php


namespace app\api\model;


use think\Model;

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
        $problem_tag_dict = (new ProblemTagDictModel())
            ->where('tag_id', $tag_id)->find();
        if (null == $problem_tag_dict) {
            return;
        }

        $problem_tag_dict->cnt = (new ProblemTagModel())
            ->where('tag_id', $tag_id)->count();
        $problem_tag_dict->save();
    }
}