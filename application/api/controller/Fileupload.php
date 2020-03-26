<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/24
 * Time: 9:19
 */

namespace app\api\controller;


use app\api\model\UploadModel;
use app\extra\controller\ApiBaseController;


class Fileupload extends ApiBaseController {

    public function upload() {
        $file = request()->file('file');
        $info = $file->move('upload/' . $this->loginuser->user_id . '/' . date("Ymd") . '/', false, true);

        $upload = new UploadModel();
        $upload->user_id = $this->loginuser->user_id;
        $upload->oss_path = "/{$info->getPath()}/{$info->getSaveName()}";
        $upload->file_size = $info->getSize();
        $upload->save();

        return json([
            'code' => 0
            , 'data' => "/{$info->getPath()}/{$info->getSaveName()}"
        ]);
    }

}