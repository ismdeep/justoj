<?php
/**
 * Created by PhpStorm.
 * User: L. Jiang <l.jiang.1024@gmail.com>
 * Date: 2018/5/24
 * Time: 9:19
 */

namespace app\api\controller;


use app\api\model\UploadModel;
use app\api\common\ApiBaseController;


class Fileupload extends ApiBaseController {

    public function upload() {
        $file = request()->file('file');
        $info = $file->move('upload/' . $this->login_user->user_id . '/' . date("Ymd") . '/', false, true);

        $upload = new UploadModel();
        $upload->user_id = $this->login_user->user_id;
        $upload->oss_path = "/{$info->getPath()}/{$info->getSaveName()}";
        $upload->file_size = $info->getSize();
        $upload->save();

        return json([
            'code' => 0
            , 'data' => "/{$info->getPath()}/{$info->getSaveName()}"
        ]);
    }

}