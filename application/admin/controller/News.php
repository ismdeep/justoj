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

        $newses = (new NewsModel())->where($where)->order('news_id', 'desc')->limit(($page - 1) * $limit, $limit)->select();
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

    public function preview($news_id = '') {
        intercept('' == $news_id, 'news_id不可为空');
        $news = (new NewsModel())->where('news_id', $news_id)->find();
        intercept(null == $news, '新闻不存在');
        $this->assign('news', $news);
        return view();
    }

    public function add() {
        $news = new NewsModel();
        $news->news_id = '';
        $news->title = '';
        $news->content = '';
        $this->assign('news', $news);
        return view('edit');
    }

    /**
     * @param string $news_id
     * @param string $title
     * @param string $content
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function save_json($news_id = '', $title = '', $content = '') {
        $this->need_root('json');

        intercept_json('' == $title, '标题不可为空');
        intercept_json('' == $content, '内容不可为空');

        if ('' == $news_id) {
            $news = new NewsModel();
            $news->user_id = $this->loginuser->user_id;
            $news->time = date('Y-m-d H:i:s', time());
            $news->defunct = 'N';
        } else {
            $news = (new NewsModel())->where('news_id', $news_id)->find();
            intercept_json(null == $news, '此新闻不存在');
        }
        $news->title = $title;
        $news->content = $content;
        $news->save();
        return json([
            'status' => 'success',
            'msg' => '保存成功'
        ]);
    }


    public function delete_json($news_id = '') {
        intercept_json('' == $news_id, 'news_id参数错误');
        $news = (new NewsModel())->where('news_id', $news_id)->find();
        intercept_json(null == $news, '新闻不存在');
        $news->delete();
        return json([
            'status' => 'success',
            'msg' => '删除成功'
        ]);
    }

    /****************************************/

    public function edit($news_id) {
        $news = (new NewsModel())->where(['news_id' => $news_id])->find();
        $this->assign('news', $news);
        return view('edit');
    }

    /**
     * @param string $news_id
     * @param $title
     * @param $content
     * @throws \think\exception\DbException
     */
    public function save($news_id = '', $title, $content) {
        if ('' == $news_id) {
            $news = new NewsModel();
        } else {
            $news = NewsModel::get(['news_id' => $news_id]);
        }
        $news->title = $title;
        $news->content = $content;
        $news->user_id = $this->loginuser->user_id;
        $news->time = date("Y-m-d H:i:s");
        $news->defunct = 'N';
        $news->save();
        $this->redirect("/admin/News");
    }

    /**
     * 修改新闻的defunct值
     * @param string $news_id
     * @param string $defunct
     * @return \think\response\Json
     */
    public function change_defunct_json($news_id = '', $defunct = '') {
        intercept_json('' == $news_id, 'news_id不可为空');
        intercept_json('' == $defunct, 'defunct不可为空');
        $news_id = intval($news_id);
        $news = (new NewsModel())->where('news_id', $news_id)->find();
        intercept_json(null == $news, '新闻不存在');
        $news->defunct = $defunct;
        $news->save();
        return json([
            'status' => 'success',
            'msg' => 'success'
        ]);
    }

}