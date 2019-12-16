<?php
/**
 * Created by PhpStorm.
 * User: ismdeep
 * Date: 2018/5/6
 * Time: 21:12
 */

namespace app\api\model;


use think\Model;

/**
 * Class SolutionModel
 * @property string source
 * @property int result
 * @property int memory
 * @property int time
 * @property int problem_id
 * @property string user_id
 * @property int language
 * @property string contest_id
 * @package app\api\model
 */
class SolutionModel extends Model
{
    protected $table = 'solution';

    /**
     * 0 等待判题
     * 1 等待重判
     * 2 正在编译
     * 3 正在运行
     * 4 正确
     * 5 格式错误
     * 6 答案错误
     * 7 时间超限
     * 8 内存超限
     * 9 输出超限
     * 10 运行错误
     * 11 编译错误
	 * 12 编译完成
	 * 13 测试运行
	 * 14 已经提交
     */
    public function fk()
	{
		$result_code_arr = array(
			'result_code_pending',
			'result_code_rejuding',
			'result_code_compiling',
			'result_code_running',
			'result_code_ac',
			'result_code_pe',
			'result_code_wa',
			'result_code_tle',
			'result_code_mle',
			'result_code_ole',
			'result_code_re',
			'result_code_ce',
			'result_code_co',
			'result_code_tr',
			'result_code_so'
		);

        $this->result_code = $result_code_arr[$this->result];

        $lang_arr = config('langs');
		foreach ($lang_arr as $item) {
            if ($this->language == $item['id']) {
                $this->language_text = $item['name'];
            }
        }
	}
}