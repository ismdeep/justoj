<?php /** @noinspection PhpUndefinedMethodInspection */

/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/8/19
 * Time: 11:00 PM
 */

namespace app\api\controller;


use app\api\model\NewsModel;
use app\extra\controller\ApiBaseController;

class News extends ApiBaseController
{
    /**
     * Change news' status
     * @param null $news_id
     * @param null $status
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function change_status($news_id = null, $status = null) {
        $this->need_root();
        intercept(null == $news_id, 'news_id '.$this->lang['can_not_be_null']);
        intercept(null == $status, 'status '.$this->lang['can_not_be_null']);
        $news = NewsModel::get(['news_id' => $news_id]);
        intercept(null == $news, 'cn' == $this->show_ui_lang ? '新闻不存在' : 'news not found');
        $news->defunct = $status;
        $news->save();

        return json([
            'status' => 'success',
            'msg' => 'cn' == $this->show_ui_lang ? '确定' : 'ok'
        ]);
    }

    /**
     * delete news
     * @param null $news_id
     * @return \think\response\Json
     */
    public function delete($news_id = null) {
        $this->need_root();
        intercept(null == $news_id, 'news_id '.$this->lang['can_not_be_null']);
        $news = NewsModel::get(['news_id' => $news_id]);
        intercept(null == $news, 'cn' == $this->show_ui_lang ? '新闻不存在' : 'news not found');
        $news->delete();
        return json([
            'status' => 'success',
            'msg' => 'cn' == $this->show_ui_lang ? '已删除' : 'Deleted'
        ]);
    }
}