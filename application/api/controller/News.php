<?php /** @noinspection PhpUndefinedMethodInspection */

/**
 * Created by PhpStorm.
 * User: L. Jiang <l.jiang.1024@gmail.com>
 * Date: 2018/8/19
 * Time: 11:00 PM
 */

namespace app\api\controller;


use app\api\model\NewsModel;
use app\extra\controller\ApiBaseController;

class News extends ApiBaseController {

    /**
     * delete news
     * @param null $id
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function delete($id = null) {
        $this->need_root();
        intercept(null == $id, 'id ' . $this->lang['can_not_be_null']);
        $news = NewsModel::get(['id' => $id]);
        intercept(null == $news, 'cn' == $this->show_ui_lang ? '新闻不存在' : 'news not found');
        $news->delete();
        return json([
            'status' => 'success',
            'msg' => 'cn' == $this->show_ui_lang ? '已删除' : 'Deleted'
        ]);
    }
}