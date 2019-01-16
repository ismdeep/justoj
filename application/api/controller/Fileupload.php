<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/24
 * Time: 9:19
 */

namespace app\api\controller;


use app\extra\controller\ApiBaseController;

class Fileupload extends ApiBaseController
{
	public function upload()
	{
		$file = request()->file('file');
		$info = $file->move('upload/'.$this->loginuser->user_id.'/'.date("Ymd").'/',false,true);
		return json(['data' => $info->getPath().'/'.$info->getSaveName()]);
	}
}