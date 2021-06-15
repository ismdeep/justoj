<?php
/**
 * Created by PhpStorm.
 * User: L. Jiang <l.jiang.1024@gmail.com>
 * Date: 2018/5/15
 * Time: 11:45 PM
 */

namespace app\admin\controller;


use app\api\model\ProblemLogModel;
use app\api\model\ProblemModel;
use app\api\model\ProblemTagDictModel;
use app\api\model\ProblemTagModel;
use app\admin\common\AdminBaseController;
use app\extra\util\PasswordUtil;
use think\Exception;
use think\response\Json;

class Problem extends AdminBaseController {

    public function problem_list() {
        return view();
    }

    /**
     * @param int $page
     * @param int $limit
     * @param string $keyword
     * @return Json
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function problem_list_json($page = 1, $limit = 10, $keyword = '') {
        $page = max(1, intval($page));
        $limit = max(1, intval($limit));
        $problems = (new ProblemModel());
        if ('' != $keyword) {
            if ('' != $keyword) {
                $problems = $problems
                    ->where(['problem_id' => $keyword])
                    ->whereOr(['title' => ['like', "%{$keyword}%"]]);
            }
        }
        $problems = $problems->order('problem_id', 'asc')->limit(($page - 1) * $limit, $limit)->select();
        $count = (new ProblemModel())->count();
        return json([
            'code' => 0,
            'count' => $count,
            'data' => $problems
        ]);
    }

    /**
     * 文件管理器
     *
     * @param $problem_id
     * @return \think\response\View
     * @throws \think\exception\DbException
     */
    public function files($problem_id) {
        /* 获取problem */
        $problem = ProblemModel::get(['problem_id' => $problem_id]);
        intercept(!$problem, 'Problem not exists.');

        $data_path = config('data_dir') . "/{$problem_id}";

        /* 判断目录是否存在，不存在则创建目录 */
        if (!is_dir($data_path)) {
            mkdir($data_path, 0777, true);
        }

        /* 获取文件列表 */
        $file_names = array();
        $dh = opendir($data_path);
        while (($file = readdir($dh)) != false) {
            if ('.' != $file && '..' != $file && !is_dir(config('data_dir') . '/' . $problem_id . '/' . $file)) {
                array_push($file_names, $file);
            }
        }
        sort($file_names);
        $files = array();
        foreach ($file_names as $file_name) {
            array_push($files, array(
                'name' => $file_name,
                'size' => filesize(config('data_dir') . '/' . $problem_id . '/' . $file_name),
                'md5' => md5_file(config('data_dir') . '/' . $problem_id . '/' . $file_name)));
        }

        $this->assign('problem', $problem);
        $this->assign('files', $files);
        return view();
    }

    public function enable_problem_json($problem_id = '') {
        intercept_json('' == $problem_id, 'contest_id参数错误');
        $problem = (new ProblemModel())->where('problem_id', $problem_id)->find();
        intercept_json(null == $problem, '题目不存在');
        $problem->defunct = 'N';
        $problem->save();

        ProblemLogModel::pushByProblemObj($problem, $this->login_user->user_id);

        return json([
            'status' => 'success',
            'msg' => '操作成功'
        ]);
    }

    public function disable_problem_json($problem_id = '') {
        intercept_json('' == $problem_id, 'contest_id参数错误');
        $problem = (new ProblemModel())->where('problem_id', $problem_id)->find();
        intercept_json(null == $problem, '题目不存在');
        $problem->defunct = 'Y';
        $problem->save();

        ProblemLogModel::pushByProblemObj($problem, $this->login_user->user_id);

        return json([
            'status' => 'success',
            'msg' => '操作成功'
        ]);
    }

    /**
     * 打包下载文件
     */
    public function download_files($problem_id) {
        // 获取文件列表
        $file_names = array();
        $dh = opendir(config('data_dir') . '/' . $problem_id);
        while (($file = readdir($dh)) != false) {
            if ('.' != $file && '..' != $file && !is_dir(config('data_dir') . '/' . $problem_id . '/' . $file)) {
                array_push($file_names, $file);
            }
        }

        $cache_id = PasswordUtil::random_string("0123456789abcdef", 32);
        mkdir(config('cache_dir') . '/' . $cache_id, 0777, true);
        $zip = new \ZipArchive();
        $zip->open(config('cache_dir') . '/' . $cache_id . "/" . $problem_id . ".zip", \ZIPARCHIVE::CREATE);
        foreach ($file_names as $file_name) {
            $zip->addFile(config('data_dir') . "/" . $problem_id . "/" . $file_name, $problem_id . "/" . basename($file_name));
        }
        $zip->close();
        header('Content-Type: application/octet-stream');
        header('Accept-Ranges: bytes');
        header('Accept-Length: ' . filesize(config('cache_dir') . '/' . $cache_id . "/" . $problem_id . ".zip"));
        header('Content-Disposition: attachment; filename=' . $problem_id . '.zip');
        ob_clean();
        flush();


        $filesize = filesize(config('cache_dir') . '/' . $cache_id . "/" . $problem_id . ".zip");
        //设置分流
        $buffer = 1024;
        //来个文件字节计数器
        $count = 0;
        $fp = fopen(config('cache_dir') . '/' . $cache_id . "/" . $problem_id . ".zip", 'r');//只读方式打开
        while (!feof($fp) && ($filesize - $count > 0)) {
            $data = fread($fp, $buffer);
//			$count+=$data;//计数
            echo $data;//传数据给浏览器端
        }
        fclose($fp);
    }

    /**
     * 下载单个文件
     *
     * @param null $problem_id
     * @param string $file_name
     */
    public function download_single_file($problem_id = null, $file_name = '') {
        $file_path = config('data_dir') . "/{$problem_id}/{$file_name}";
        intercept(file_exists($file_path) == false, '文件不存在');

        $file = fopen($file_path, 'r');
        header('Content-Type: application/octet-stream');
        header('Accept-Ranges: bytes');
        header('Accept-Length: ' . filesize($file_path));
        header("Content-Disposition: attachment; filename=" . $file_name);
        echo fread($file, filesize($file_path));
        fclose($file);
        exit();
    }


    /**
     * 添加问题
     */
    public function add() {
        $problem = new ProblemModel();
        $problem->problem_id = '';
        $problem->title = '';
        $problem->time_limit = 1;
        $problem->memory_limit = 128;
        $problem->description = '';
        $problem->input = '';
        $problem->output = '';
        $problem->sample_input = '';
        $problem->sample_output = '';
        $problem->hint = '';
        $problem->source = '';


        $this->assign('problem', $problem);
        return view('edit');
    }

    /**
     * 编辑问题
     * @param string $id 题目id
     * @return \think\response\View
     * @throws \think\exception\DbException
     */
    public function edit($id = '') {
        intercept('' == $id, 'id参数不可为空');
        $problem = ProblemModel::get(['problem_id' => $id]);
        intercept(null == $problem, '题目不存在');
        $this->assign('problem', $problem);
        return view('edit');
    }

    public function edit2($id = '') {
        intercept('' == $id, 'id参数不可为空');
        $problem = ProblemModel::get(['problem_id' => $id]);
        intercept(null == $problem, '题目不存在');
        $this->assign('problem', $problem);
        return view('problem-edit');
    }


    /**
     * @param string $problem_id
     * @param string $title
     * @param int $time_limit
     * @param int $memory_limit
     * @param string $description
     * @param string $input
     * @param string $output
     * @param string $sample_input
     * @param string $sample_output
     * @param string $hint
     * @param string $source
     * @return Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function problem_save_json(
        $problem_id = '',
        $title = '',
        $time_limit = 1,
        $memory_limit = 128,
        $description = '',
        $input = '',
        $output = '',
        $sample_input = '',
        $sample_output = '',
        $hint = '',
        $source = ''
    ) {
        intercept_json('' == $title, '标题不可为空');

        $create_folder_flag = false;

        if ('' == $problem_id) {
            $create_folder_flag = true;
            $problem = new ProblemModel();
            $problem->spj = 0;
            $problem->in_date = date('Y-m-d H:i:s', time());
            $problem->defunct = 'Y';
            $problem->accepted = 0;
            $problem->submit = 0;
            $problem->solved = 0;
            $problem->tags = '';
            $problem->owner_id = $this->login_user->user_id;
        } else {
            $problem_id = intval($problem_id);
            $problem = (new ProblemModel())->where('problem_id', $problem_id)->find();
            intercept_json(null == $problem, '题目不存在');
        }

        $problem->title = $title;
        $problem->time_limit = intval($time_limit);
        $problem->memory_limit = intval($memory_limit);
        $problem->description = $description;
        $problem->input = $input;
        $problem->output = $output;
        $problem->sample_input = $sample_input;
        $problem->sample_output = $sample_output;
        $problem->hint = $hint;
        $problem->source = $source;
        $problem->save();

        ProblemLogModel::pushByProblemObj($problem, $this->login_user->user_id);

        if ($create_folder_flag) {
            // 新建题目目录并写入样例数据
            try {
                mkdir(config('data_dir') . '/' . $problem->problem_id);
            } catch (Exception $e) {
            }

            try {
                $gitkeep_file = fopen(config('data_dir') . '/' . $problem->problem_id . '/.gitkeep', 'w') or dir('Unable to open file');
                fclose($gitkeep_file);
            } catch (Exception $e) {
            }

            /* 取消将样例数据写入测试数据目录 */
//            try{
//                $sample_input_file = fopen(config('data_dir') . '/'.$problem->problem_id.'/sample.in','w') or die("Unable to open file");
//                $sample_output_file = fopen(config('data_dir') . '/'.$problem->problem_id.'/sample.out','w') or die("Unable to open file");
//                fwrite($sample_input_file, $sample_input);
//                fclose($sample_input_file);
//                fwrite($sample_output_file, $sample_output);
//                fclose($sample_output_file);
//            }catch (Exception $e){}
        }

        return json([
            'status' => 'success',
            'msg' => '保存成功',
            'data' => $problem
        ]);

    }


    /**
     * add edit页面均提交到此处进行保存
     */
    public function save($problem_id = '', $title = '', $time_limit = '', $memory_limit = '', $description = '', $input = '', $output = '', $sample_input = '', $sample_output = '', $hint = '', $source = '', $spj = 0) {
        $problem = null;
        if ('' == $problem_id) {
            // 新建一个题目
            $problem = new ProblemModel();
            $problem->title = $title;
            $problem->description = $description;
            $problem->input = $input;
            $problem->output = $output;
            $problem->sample_input = $sample_input;
            $problem->sample_output = $sample_output;
            $problem->hint = $hint;
            $problem->source = $source;
            $problem->time_limit = $time_limit;
            $problem->memory_limit = $memory_limit;
            $problem->owner_id = $this->login_user->user_id;
            $problem->spj = $spj;
            $problem->save();
        } else {
            // 修改一个题目
            $problem = ProblemModel::get(['problem_id' => $problem_id]);
            $problem->title = $title;
            $problem->description = $description;
            $problem->input = $input;
            $problem->output = $output;
            $problem->sample_input = $sample_input;
            $problem->sample_output = $sample_output;
            $problem->hint = $hint;
            $problem->source = $source;
            $problem->time_limit = $time_limit;
            $problem->memory_limit = $memory_limit;
            $problem->owner_id = $this->login_user->user_id;
            $problem->spj = $spj;
            $problem->save();
        }

        ProblemLogModel::pushByProblemObj($problem, $this->login_user->user_id);

        // 新建题目目录并写入样例数据
        try {
            mkdir(config('data_dir') . '/' . $problem->problem_id);
        } catch (Exception $e) {
        }

        try {
            $gitkeep_file = fopen(config('data_dir') . '/' . $problem->problem_id . '/.gitkeep', 'w') or dir('Unable to open file');
            fclose($gitkeep_file);
        } catch (Exception $e) {
        }

        try {


            $sample_input_file = fopen(config('data_dir') . '/' . $problem->problem_id . '/sample.in', 'w') or die("Unable to open file");
            $sample_output_file = fopen(config('data_dir') . '/' . $problem->problem_id . '/sample.out', 'w') or die("Unable to open file");
            fwrite($sample_input_file, $sample_input);
            fclose($sample_input_file);
            fwrite($sample_output_file, $sample_output);
            fclose($sample_output_file);
        } catch (Exception $e) {
        }

        return $this->redirect('/admin/Problem/save_success?id=' . $problem->problem_id);
    }

    /**
     * 题目添加或修改成功跳到成功页面
     */
    public function save_success($id) {
        $problem = ProblemModel::get(['problem_id' => $id]);
        $this->assign('problem', $problem);
        return view();
    }

    /**
     * 题目添加数据
     * @param string $problem_id
     * @return Json
     */
    public function upload_files($problem_id = '') {
        intercept('' == $problem_id, 'problem_id不可为空');
        $file = request()->file('file');
        // 移动到框架应用根目录/uploads/ 目录下
        $info = $file->move(config('data_dir') . '/' . $problem_id, false, true);
        try {
            return json([
                'status' => 'success',
                'msg' => ''
            ]);
        } catch (OssException $e) {
            return json([
                'status' => 'error',
                'msg' => '上传失败'
            ]);
        }
    }

    public function add_files($problem_id = '') {
        intercept('' == $problem_id, 'problem_id不可为空');
        $problem = (new ProblemModel())->where('problem_id', $problem_id)->find();
        $this->assign('problem', $problem);
        return view('add_files');
    }

    /**
     * 设置题目标签
     * @param string $problem_id
     * @return Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function set_tag($problem_id = '') {
        intercept('' == $problem_id, 'ERROR on ARGS');
        $problem = (new ProblemModel())->where('problem_id', $problem_id)->find();
        intercept(null == $problem, "Not found this problem. [problem_id:$problem_id]");

        /* 题目标签列表 >>>> */
        $problem_tags = (new ProblemTagDictModel())->order('tag_id', 'asc')->select();
        $this->assign('problem_tags', $problem_tags);
        /* <<<< 题目标签列表 */

        /* 对标签列表进行处理 >>>> */
        foreach ($problem_tags as $problem_tag) {
            $problem_tag->selected = false;
            if ((new ProblemTagModel())->where('problem_id', $problem_id)->where('tag_id', $problem_tag->tag_id)->find()) {
                $problem_tag->selected = true;
            }
        }
        /* <<<< 对标签列表进行处理 */

        $this->assign('problem', $problem);
        return view('set_tag');
    }

    /***
     * 设置题目标签API
     * @param string $problem_id
     * @param string $tags
     * @return Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws Exception
     */
    public function set_tag_json($problem_id = '', $tags = '') {
        intercept_json('' == $problem_id, 'ERROR');
        $problem = (new ProblemModel())->where('problem_id', $problem_id)->find();
        intercept_json(null == $problem, "Not found this problem. [problem_id:$problem_id]");

        $tag_list = explode(',', $tags);
        if ($tags == '') {
            $tag_list = [];
        }

        $problem->tags = $tags;
        $problem->save();

        ProblemLogModel::pushByProblemObj($problem, $this->login_user->user_id);

        (new ProblemTagModel())->where('problem_id', $problem_id)->delete();

        foreach ($tag_list as $tag) {
            $problem_tag = new ProblemTagModel();
            $problem_tag->problem_id = $problem_id;
            $problem_tag->tag_id = $tag;
            $problem_tag->save();
            ProblemTagDictModel::update_cnt($tag);
        }

        return json([
            'status' => 'success',
            'tag_list' => $tag_list
        ]);
    }
}