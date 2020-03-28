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
use OSS\Core\OssException;
use OSS\OssClient;


class Fileupload extends ApiBaseController {

    public function upload() {
        $file = request()->file('file');
        $info = $file->move('upload/' . $this->loginuser->user_id . '/' . date("Ymd") . '/', false, true);


        return json([
                'code' => 0
                , 'data' => "/{$info->getPath()}/{$info->getSaveName()}"
            ]);
    }

}