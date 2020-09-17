<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/18
 * Time: 16:44
 */

namespace app\admin\controller;


use app\api\model\NewsModel;
use app\extra\controller\AdminBaseController;

class News extends AdminBaseController {

    public function news_list() {
        return view();
    }


    /**
     * @param int $page
     * @param int $limit
     * @param string $defunct
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function news_list_json($page = 1, $limit = 10, $news_keyword = '', $defunct = '') {
        $page = max(1, intval($page));
        $limit = max(1, intval($limit));

        $where = [];
        if ('' != $defunct) {
            $where['defunct'] = ['=', $defunct];
        }

        if ('' != $news_keyword) {
            $where['title'] = ['like', "%{$news_keyword}%"];
        }

        $newses = (new NewsModel())->where($where)->order('id', 'desc')->limit(($page - 1) * $limit, $limit)->select();
        $count = (new NewsModel())->where($where)->count();
        foreach ($newses as $news) {
            $news->fk();
        }
        return json([
            'code' => 0,
            'count' => $count,
            'data' => $newses
        ]);
    }

    public function preview($id = '') {
        intercept('' == $id, 'id不可为空');
        $news = (new NewsModel())->where('id', $id)->find();
        intercept(null == $news, '新闻不存在');
        $this->assign('news', $news);
        return view();
    }

    public function add() {
        $news = new NewsModel();
        $news->id = '';
        $news->title_cn = '';
        $news->content_cn = '';
        $news->title_en = '';
        $news->content_en = '';
        $this->assign('news', $news);
        return view('edit');
    }

    /**
     * @param string $id
     * @param string $title_cn
     * @param string $content_cn
     * @param string $title_en
     * @param string $content_en
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function save_json($id = '', $title_cn = '', $content_cn = '', $title_en = '', $content_en = '') {
        $this->need_root('json');

        intercept_json('' == $title_cn, '标题不可为空');
        intercept_json('' == $content_cn, '内容不可为空');
        intercept_json('' == $title_en, '标题不可为空');
        intercept_json('' == $content_en, '内容不可为空');

        if ('' == $id) {
            $news = new NewsModel();
            $news->user_id = $this->login_user->user_id;
            $news->defunct = 'N';
        } else {
            $news = (new NewsModel())->where('id', $id)->find();
            intercept_json(null == $news, '此新闻不存在');
        }
        $news->title_cn = $title_cn;
        $news->content_cn = $content_cn;
        $news->title_en = $title_en;
        $news->content_en = $content_en;
        $news->save();
        return json([
            'status' => 'success',
            'msg' => '保存成功'
        ]);
    }


    public function delete_json($id = '') {
        intercept_json('' == $id, 'id参数错误');
        $news = (new NewsModel())->where('id', $id)->find();
        intercept_json(null == $news, '新闻不存在');
        $news->delete();
        return json([
            'status' => 'success',
            'msg' => '删除成功'
        ]);
    }

    /****************************************/

    public function edit($id) {
        $news = (new NewsModel())->where(['id' => $id])->find();
        $this->assign('news', $news);
        return view('edit');
    }

    /**
     * 修改新闻的defunct值
     * @param string $id
     * @param string $defunct
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function change_defunct_json($id = '', $defunct = '') {
        intercept_json('' == $id, 'id不可为空');
        intercept_json('' == $defunct, 'defunct不可为空');
        $id = intval($id);
        $news = (new NewsModel())->where('id', $id)->find();
        intercept_json(null == $news, '新闻不存在');
        $news->defunct = $defunct;
        $news->save();
        return json([
            'status' => 'success',
            'msg' => 'success'
        ]);
    }

}