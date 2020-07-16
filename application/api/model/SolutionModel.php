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
 * @package app\api\model
 *
 * @property int solution_id
 * @property int problem_id
 * @property string user_id
 * @property int time
 * @property int memory
 * @property \DateTime in_date
 * @property int result
 * @property int language
 * @property string ip
 * @property int contest_id
 * @property int code_length
 *
 */
class SolutionModel extends Model {
    protected $table = 'solution';

    const RESULT_PENDING = 0;
    const RESULT_REJUDING = 1;
    const RESULT_COMPILING = 2;
    const RESULT_RUNNING = 3;
    const RESULT_AC = 4;
    const RESULT_PE = 5;
    const RESULT_WA = 6;
    const RESULT_TLE = 7;
    const RESULT_MLE = 8;
    const RESULT_OLE = 9;
    const RESULT_RE = 10;
    const RESULT_CE = 11;
    const RESULT_CO = 12;
    const RESULT_TR = 13;
    const RESULT_SO = 14;
    static public $result_map = [
        self::RESULT_PENDING => [
            'id' => self::RESULT_PENDING,
            'lang_id' => 'result_code_pending',
        ],
        self::RESULT_REJUDING => [
            'id' => self::RESULT_REJUDING,
            'lang_id' => 'result_code_rejuding',
        ],

        self::RESULT_COMPILING => [
            'id' => self::RESULT_COMPILING,
            'lang_id' => 'result_code_compiling',
        ],
        self::RESULT_RUNNING => [
            'id' => self::RESULT_RUNNING,
            'lang_id' => 'result_code_running',
        ],
        self::RESULT_AC => [
            'id' => self::RESULT_AC,
            'lang_id' => 'result_code_ac',
        ],
        self::RESULT_PE => [
            'id' => self::RESULT_PE,
            'lang_id' => 'result_code_pe',
        ],
        self::RESULT_WA => [
            'id' => self::RESULT_WA,
            'lang_id' => 'result_code_wa',
        ],
        self::RESULT_TLE => [
            'id' => self::RESULT_TLE,
            'lang_id' => 'result_code_tle',
        ],
        self::RESULT_MLE => [
            'id' => self::RESULT_MLE,
            'lang_id' => 'result_code_mle',
        ],
        self::RESULT_OLE => [
            'id' => self::RESULT_OLE,
            'lang_id' => 'result_code_ole',
        ],
        self::RESULT_RE => [
            'id' => self::RESULT_RE,
            'lang_id' => 'result_code_re',
        ],
        self::RESULT_CE => [
            'id' => self::RESULT_CE,
            'lang_id' => 'result_code_ce',
        ],
        self::RESULT_CO => [
            'id' => self::RESULT_CO,
            'lang_id' => 'result_code_co',
        ],
        self::RESULT_TR => [
            'id' => self::RESULT_TR,
            'lang_id' => 'result_code_tr',
        ],
        self::RESULT_SO => [
            'id' => self::RESULT_SO,
            'lang_id' => 'result_code_so',
        ],

    ];


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
    public function fk() {
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