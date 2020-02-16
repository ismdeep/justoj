<?php


namespace app\api\model;


use think\Model;

/**
 * @property int problem_id
 * @property string title
 * @property string description
 * @property string input
 * @property string output
 * @property string sample_input
 * @property string sample_output
 * @property string spj
 * @property string hint
 * @property string source
 * @property string in_date
 * @property int time_limit
 * @property int memory_limit
 * @property string defunct
 * @property string tags
 * @property string op_user_id
 *
 */
class ProblemLogModel extends Model
{
    protected $table = 'problem_log';

    static function push(
          $problem_id
        , $title
        , $description
        , $input
        , $output
        , $sample_input
        , $sample_output
        , $spj
        , $hint
        , $source
        , $in_date
        , $time_limit
        , $memory_limit
        , $defunct
        , $tags
        , $op_user_id
    )
    {
        $problem_log = new ProblemLogModel();
        $problem_log->problem_id = $problem_id;
        $problem_log->title = $title;
        $problem_log->description = $description;
        $problem_log->input = $input;
        $problem_log->output = $output;
        $problem_log->sample_input = $sample_input;
        $problem_log->sample_output = $sample_output;
        $problem_log->spj = $spj;
        $problem_log->hint = $hint;
        $problem_log->source = $source;
        $problem_log->in_date = $in_date;
        $problem_log->time_limit = $time_limit;
        $problem_log->memory_limit = $memory_limit;
        $problem_log->defunct = $defunct;
        $problem_log->tags = $tags;
        $problem_log->op_user_id = $op_user_id;
        $problem_log->save();
    }

    static function pushByProblemObj($problem, $op_user_id) {
        ProblemLogModel::push(
              $problem->problem_id
            , $problem->title
            , $problem->description
            , $problem->input
            , $problem->output
            , $problem->sample_input
            , $problem->sample_output
            , $problem->spj
            , $problem->hint
            , $problem->source
            , $problem->in_date
            , $problem->time_limit
            , $problem->memory_limit
            , $problem->defunct
            , $problem->tags
            , $op_user_id
        );
    }
}
