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

        vendor('aliyun-oss-php-sdk.autoload');
        $oss_client = new OssClient('XhYqlheIeMqs4vYM', 'krF5kKnOjra94e9g00fKHgtU4Zvzzt', 'http://oss-cn-shenzhen.aliyuncs.com');
        $bucket = 'ismdeep';

        $obj = 'justoj-data/' . $this->loginuser->user_id . '/' . date('Ymd') . '/' . $info->getSaveName();
        $file_path = $info->getPath() . '/' . $info->getSaveName();
        try {
            $oss_client->uploadFile($bucket, $obj, $file_path);

            $upload = new UploadModel();
            $upload->user_id = $this->loginuser->user_id;
            $upload->oss_path = $obj;
            $upload->file_size = $info->getSize();
            $upload->save();

            return json([
                'code' => 0
                , 'data' => "https://ismdeep.oss-accelerate.aliyuncs.com/{$obj}"
            ]);
        } catch (OssException $e) {
            return json([
                'code' => 502
                , 'msg' => '无法上传，系统错误。'
            ]);
        }
    }

}