<?php
    ini_set('display_errors', 'on');
    error_reporting(E_ALL);

    require_once '../../config.php';

    if (isloggedin()) {
        $context_sys = get_context_instance(CONTEXT_SYSTEM);

        if (!has_capability('moodle/course:create', $context_sys)) result_output('A001', null, 'Permission denied', true, 403);

        if (isset($_REQUEST['action']) && function_exists($_REQUEST['action'])) {
            $_REQUEST['action']();
            result_output('0', null, 'OK');
        } else {
            result_output('R002', null, 'Bad request', true, 400);
        }
    } else {
        result_output('R001', null, 'User not logged in', true, 403);
    }

    function updateCat() {

        global $DB;

        if (isset($_REQUEST['name'])) {
            switch ($_REQUEST['name']) {
                case 'cat-name':
                    $row_name = 'name';
                    break;
                case 'cat-info':
                    $row_name = 'info';
                    break;
                default:
                    result_output('R002', null, 'Bad request', true, 400);
            }
        } else {
            result_output('R002', null, 'Bad request', true, 400);
        }

        if (!isset($_REQUEST['value'], $_REQUEST['pk']) || !is_numeric($_REQUEST['pk'])) result_output('R002', null, 'Bad request', true, 400);

        $DB->set_field('question_categories', $row_name, $_REQUEST['value'], ['id' => $_REQUEST['pk']]);

    }

    function updateQuestion() {

        global $DB;

        if (isset($_REQUEST['name'])) {
            switch ($_REQUEST['name']) {
                case 'qbank-name':
                    $table_name = 'question';
                    $row_name = 'name';
                    break;
                case 'qbank-info':
                    $table_name = 'question';
                    $row_name = 'questiontext';
                    break;
                case 'qbank-feedback':
                    $table_name = 'question';
                    $row_name = 'generalfeedback';
                    break;
                case 'qbank-correctans':
                    $table_name = 'question_answers';
                    $row_name = 'answer';
                    break;
                default:
                    result_output('R002', null, 'Bad request', true, 400);
            }
        } else {
            result_output('R002', null, 'Bad request', true, 400);
        }

        if (!isset($_REQUEST['value'], $_REQUEST['pk']) || !is_numeric($_REQUEST['pk'])) result_output('R002', null, 'Bad request', true, 400);

        $DB->set_field($table_name, $row_name, $_REQUEST['value'], ['id' => $_REQUEST['pk']]);

    }

    function switchBeep() {

        global $DB;

        if (isset($_REQUEST['qid'], $_REQUEST['enable']) && is_numeric($_REQUEST['qid']) && is_bool((bool) $_REQUEST['enable'])) {
            $question = $DB->get_records('question', ['id' => (int) $_REQUEST['qid']]);

            if (count($question) == 1) $question = $question[(int) $_REQUEST['qid']];
            else result_output('I001', null, 'Question not exists', true);

            $question->qtype = strtolower($question->qtype);

            switch ($question->qtype) {
                case 'multichoice':
                    $old_type   = $question->qtype;
                    $new_type   = 'beepmulchoice';

                    $old_table  = 'qtype_multichoice_options';
                    $new_table  = 'qtype_beepmulchoice_options';

                    $col_name   = 'questionid';
                    break;
                case 'beepmulchoice':
                    $old_type = $question->qtype;
                    $new_type = 'multichoice';

                    $old_table  = 'qtype_beepmulchoice_options';
                    $new_table  = 'qtype_multichoice_options';

                    $col_name   = 'questionid';
                    break;
                case 'truefalse':
                case 'beeptruefalse':
                    $old_type   = $question->qtype;
                    $new_type   = substr($old_type, 0, 4) == 'beep' ? substr($old_type, 4) : ('beep' . $old_type);

                    $old_table  = 'question_' . $old_type;
                    $new_table  = 'question_' . $new_type;

                    $col_name   = 'question';
                    break;
                default:
                    result_output('I003', null, 'Unsupported question type', true);
            }

            try { // query with transaction
                $transaction = $DB->start_delegated_transaction();

                $DB->update_record('question', ['id' => (int) $_REQUEST['qid'], 'qtype' => $new_type]);

                $old_record = $DB->get_record($old_table, [$col_name => (int) $_REQUEST['qid']]);

                if ($old_record === false) throw new Exception('Retrieve old record failed.');

                unset($old_record->id); // throw away original auto increment ID

                $DB->insert_record($new_table, $old_record); // insert into new table

                $DB->delete_records($old_table, [$col_name => (int) $_REQUEST['qid']]); // remove from old table

                $transaction->allow_commit();
            } catch(Exception $e) {
                $transaction->rollback($e);
                result_output('I002', null, 'Database execution error: ' . $e, true);
            }

            result_output('0', null, 'OK');

        } else {
            result_output('R002', null, 'Bad request', true, 400);
        }

    }

    function getAnswers() {

        global $DB;

        if (isset($_REQUEST['qid']) && is_numeric($_REQUEST['qid'])) {

            try {
                $answers = $DB->get_records('question_answers', ['question' => (int) $_REQUEST['qid']], '', '*', 0, 4);

                if (count($answers) <= 0) result_output('I001', null, 'Question not exists', true);

                $ret = [];

                foreach ($answers as $aid => $answer) {
                    $ret[] = [
                        'aid'       => $answer->id,
                        'text'      => strip_tags(str_ireplace(['</p><p>', '<p>', '</p>', '<br />', '<br>'], [PHP_EOL, null, null, PHP_EOL, PHP_EOL], $answer->answer)),
                        'correct'   => (float) $answer->fraction > 0
                    ];
                }

                result_output('0', $ret, 'OK');
            } catch(Exception $e) {
                result_output('I002', null, 'Database execution error: ' . $e, true);
            }
        }

    }

    function updateAnswers() {

        global $DB;

        $fields = [];

        for ($i = 0; $i < 4; $i++) {
            if (isset($_REQUEST['ans' . $i . '-text'], $_REQUEST['ans' . $i . '-correct'], $_REQUEST['ans' . $i . '-aid']) && is_numeric($_REQUEST['ans' . $i . '-aid'])) {
                $fields[] = [
                    'aid'       => (int) $_REQUEST['ans' . $i . '-aid'],
                    'text'      => empty(trim($_REQUEST['ans' . $i . '-text'])) ? '' : $_REQUEST['ans' . $i . '-text'],
                    'correct'   => $_REQUEST['ans' . $i . '-correct'] == 'true'
                ];
            }
        }

        try { // query with transaction
            $transaction = $DB->start_delegated_transaction();

            for ($i = 0; $i < count($fields); $i++) {
                $DB->update_record('question_answers', ['id' => $fields[$i]['aid'], 'answer' => $fields[$i]['text'], 'fraction' => $fields[$i]['correct'] ? '1.0000000' : '0.0000000']);
            }

            $transaction->allow_commit();
        } catch(Exception $e) {
            $transaction->rollback($e);
            result_output('I002', null, 'Database execution error: ' . $e, true);
        }

    }

    function addQuestion() {
        
        global $DB;
        global $USER;

        $check_arr = [
            'cat-id',
            'ques-name',
            'ques-text',
            'ques-fback',
            'ques-beep',
            'ans0-text',
            'ans0-correct',
            'ans1-text',
            'ans1-correct',
            'ans2-text',
            'ans2-correct',
            'ans3-text',
            'ans3-correct'
        ];

        for ($i = 0; $i < count($check_arr); $i++) {
            if (!isset($_REQUEST[$check_arr[$i]])) result_output('R002', null, 'Bad request', true, 400);
        }

        if (empty($_REQUEST['ques-name'])) result_output('R003', null, 'You must enter the question name.', true);

        $is_beep = $_REQUEST['ques-beep'] == 'true';

        $ins_question = new stdClass();
        $ins_question->category                 = (int) $_REQUEST['cat-id'];
        $ins_question->parent                   = 0;
        $ins_question->name                     = htmlspecialchars($_REQUEST['ques-name']);
        $ins_question->questiontext             = htmlspecialchars(str_ireplace([PHP_EOL], ['<br />'], $_REQUEST['ques-text']));
        $ins_question->questiontextformat       = 1;
        $ins_question->generalfeedback          = htmlspecialchars(str_ireplace([PHP_EOL], ['<br />'], $_REQUEST['ques-fback']));
        $ins_question->generalfeedbackformat    = 1;
        $ins_question->qtype                    = $is_beep ? 'beepmulchoice' : 'multichoice';
        $ins_question->stamp                    = make_unique_id_code();
        $ins_question->version                  = make_unique_id_code();
        $ins_question->timecreated              = time();
        $ins_question->timemodified             = $ins_question->timecreated;
        $ins_question->createdby                = $USER->id;
        $ins_question->modifiedby               = $USER->id;

        try { // query with transaction
            $transaction = $DB->start_delegated_transaction();

            $qid = $DB->insert_record('question', $ins_question, true); // add question to table
            unset($ins_question);

            $opt_question = new stdClass();
            $opt_question->questionid                       = $qid;
            $opt_question->single                           = 1;
            $opt_question->shuffleanswers                   = 1;
            $opt_question->correctfeedback                  = 'Your answer is correct.';
            $opt_question->correctfeedbackformat            = 1;
            $opt_question->partiallycorrectfeedback         = 'Your answer is partially correct.';
            $opt_question->partiallycorrectfeedbackformat   = 1;
            $opt_question->incorrectfeedback                = 'Your answer is incorrect.';
            $opt_question->shownumcorrect                   = 1;

            $DB->insert_record($is_beep ? 'qtype_beepmulchoice_options' : 'qtype_multichoice_options', $opt_question); // specify question options
            unset($opt_question);

            $ans_count = 0;

            for ($i = 0; $i < 4; $i++) {
                if (empty($_REQUEST["ans{$i}-text"])) continue; // skip empty answer

                $ans_count++;

                $answer = new stdClass();
                $answer->question       = $qid;
                $answer->answer         = htmlspecialchars(str_ireplace([PHP_EOL], ['<br />'], $_REQUEST["ans{$i}-text"]));
                $answer->answerformat   = 1;
                $answer->fraction       = $_REQUEST["ans{$i}-correct"] == 'true' ? '1.0000000' : '0.0000000';
                $answer->feedback       = '';
                $answer->feedbackformat = 1;

                if (!isset($aid)) {
                    $aid = $DB->insert_record('question_answers', $answer, true);
                } else {
                    $DB->insert_record('question_answers', $answer, true); // insert answers
                }
            }

            if ($ans_count < 1) result_output('R003', null, 'You must enter at least one answer.', true);
            else $transaction->allow_commit();
        } catch(Exception $e) {
            $transaction->rollback($e);
            result_output('I002', null, 'Database execution error: ' . $e, true);
        }

        result_output('0', ['id' => $qid, 'aid' => isset($aid) ? $aid : -1], 'OK');
    }

    function createCat() {

        global $DB;

        if (empty($_REQUEST['name']) || empty($_REQUEST['cid']) || !is_numeric($_REQUEST['cid'])) result_output('R002', null, 'Bad request', true, 400);

        $record = new stdClass();
        $record->name = htmlspecialchars($_REQUEST['name']);
        $record->info = htmlspecialchars($_REQUEST['info']);
        $record->infoformat = 0;
        $record->contextid = (int) $_REQUEST['cid'];
        $record->stamp = make_unique_id_code();
        $record->parent = 0;
        $record->sortorder = 999;

        try {
            $insert_id = $DB->insert_record('question_categories', $record, true);

            if (!is_numeric($insert_id)) throw new Exception('Insert to database failed.');

            result_output('0', ['id' => $insert_id], 'OK');
        } catch(Exception $e) {
            result_output('I004', null, 'Execution error: ' . $e, true);
        }

    }

    function bulkExec() {

        global $DB;

        if (empty($_REQUEST['method']) || empty($_REQUEST['ids']) || empty($_REQUEST['scope']) || !in_array($_REQUEST['scope'], ['cat', 'question'])) result_output('R002', null, 'Bad request', true, 400);

        if (substr($_REQUEST['method'], 0, 4) == 'cat-') {
            preg_match("/^cat-\d+$/", $_REQUEST['method'], $matches);

            if (!isset($matches[0])) result_output('R002', null, 'Bad request', true, 400);
            else $matches[0] = str_ireplace('cat-', null, $matches[0]);

            if (!is_numeric($matches[0])) result_output('R002', null, 'Bad request', true, 400);
            else {
                $cat_id = $matches[0];
                $_REQUEST['method'] = 'movecat';
            }
        }

        $targets = explode(',', $_REQUEST['ids']);

        try {
            $transaction = $DB->start_delegated_transaction();

            switch (strtolower($_REQUEST['method'])) {
                case 'del':
                    for ($i = 0; $i < count($targets); $i++) {
                        if (!is_numeric($targets[$i])) continue;

                        if ($_REQUEST['scope'] == 'cat') {
                            $DB->delete_records('question_categories', ['id' => $targets[$i]]); // delete category
                            $DB->delete_records('question', ['category' => $targets[$i]]); // delete corresponding questions
                        } elseif ($_REQUEST['scope'] == 'question') {
                            $DB->delete_records('question', ['id' => $targets[$i]]);
                            $DB->delete_records('qtype_multichoice_options', ['questionid' => $targets[$i]]);
                            $DB->delete_records('qtype_beepmulchoice_options', ['questionid' => $targets[$i]]);
                        }
                    }
                    break;
                case 'movecat':
                    for ($i = 0; $i < count($targets); $i++) {
                        $question = new stdClass();
                        $question->id       = (int) $targets[$i];
                        $question->category = (int) $cat_id;

                        $DB->update_record('question', $question);
                    }
                    break;
                case 'fixfback':
                    if ($_REQUEST['scope'] == 'cat') {

                        $total = 0;

                        for ($i = 0; $i < count($targets); $i++) {
                            $total += fixCatFeedback($targets[$i]);
                        }

                        result_output('0', ['total' => $total], 'OK');

                    } else result_output('R002', null, 'Bad request', true, 400);
                    break;
                case 'beep':
                    for ($i = 0; $i < count($targets); $i++) {
                        $question = $DB->get_records('question', ['id' => $targets[$i]]);

                        if (count($question) == 1) $question = $question[$targets[$i]];
                        else continue;

                        $question->qtype = strtolower($question->qtype);

                        switch ($question->qtype) {
                            case 'multichoice':
                                $old_type   = $question->qtype;
                                $new_type   = 'beepmulchoice';

                                $old_table  = 'qtype_multichoice_options';
                                $new_table  = 'qtype_beepmulchoice_options';

                                $col_name   = 'questionid';
                                break;
                            case 'beepmulchoice':
                                $old_type = $question->qtype;
                                $new_type = 'multichoice';

                                $old_table  = 'qtype_beepmulchoice_options';
                                $new_table  = 'qtype_multichoice_options';

                                $col_name   = 'questionid';
                                break;
                            case 'truefalse':
                            case 'beeptruefalse':
                                $old_type   = $question->qtype;
                                $new_type   = substr($old_type, 0, 4) == 'beep' ? substr($old_type, 4) : ('beep' . $old_type);

                                $old_table  = 'question_' . $old_type;
                                $new_table  = 'question_' . $new_type;

                                $col_name   = 'question';
                                break;
                            default:
                                result_output('I003', null, 'Unsupported question type', true);
                        }

                        $DB->update_record('question', ['id' => $targets[$i], 'qtype' => $new_type]);

                        $old_record = $DB->get_record($old_table, [$col_name => $targets[$i]]);

                        if ($old_record === false) throw new Exception('Retrieve old record failed.');

                        unset($old_record->id); // throw away original auto increment ID

                        $DB->insert_record($new_table, $old_record); // insert into new table

                        $DB->delete_records($old_table, [$col_name => $targets[$i]]); // remove from old table
                    }
                    break;
                default:
                    result_output('R002', null, 'Bad request', true, 400);
            }

            $transaction->allow_commit();
            
            result_output('0', ['reload' => true], 'OK');
        } catch(Exception $e) {
            $transaction->rollback($e);
            result_output('I004', null, 'Execution error: ' . $e, true);
        }
    }

    function fixCatFeedback($cat = null) {

        global $DB, $CFG;

        if (is_null($cat)) {
            if (empty($_REQUEST['cid']) || !is_numeric($_REQUEST['cid']) || (int) $_REQUEST['cid'] < 1) result_output('R002', null, 'Bad request', true, 400);

            $cid = (int) $_REQUEST['cid'];
        } else {
            $cid = (int) $cat;
        }

        try {

            $transaction = $DB->start_delegated_transaction();

            $questions = $DB->get_records('question', ['category' => $cid, 'qtype' => 'beepmulchoice']);
            $table_prefix = $CFG->prefix;

            foreach ($questions as $qid => $qobj) {

                $query = <<<SQL
INSERT INTO `{$table_prefix}qtype_beepmulchoice_options`
    (questionid, single, correctfeedback, correctfeedbackformat, partiallycorrectfeedback, partiallycorrectfeedbackformat, incorrectfeedback, incorrectfeedbackformat, shownumcorrect)
VALUES (
        {$qid},
        '1',
        'Your answer is correct.',
        '1',
        'Your answer is partially correct.',
        '1',
        'Your answer is incorrect.',
        '1',
        '1'
    )
ON DUPLICATE KEY UPDATE
    `correctfeedback` = 'Your answer is correct.',
    `correctfeedbackformat` = '1',
    `partiallycorrectfeedback` = 'Your answer is partially correct.',
    `partiallycorrectfeedbackformat` = '1',
    `incorrectfeedback` = 'Your answer is incorrect.',
    `incorrectfeedbackformat` = '1',
    `shownumcorrect` = '1'
SQL;
                $result = $DB->execute($query);

            }

            $transaction->allow_commit();

            if (is_null($cat)) result_output('0', ['total' => count($questions)], 'OK');
            else return count($questions);

        } catch(Exception $e) {
            $transaction->rollback($e);
            result_output('I004', null, 'Execution error: ' . $e, true);
        }
    }

    function pingpong() {
        result_output('0', null, 'OK');
    }

    function result_output($code, $data = null, $msg = null, $onerror = false, $http_code = 200) {

        $json = [
            'code'      => $code,
            'onerror'   => $onerror
        ];

        if (!is_null($data)) $json['data'] = $data;
        if (!is_null($msg)) $json['msg'] = $msg;

        header('Content-Type: application/json');
        http_response_code($http_code);
        echo json_encode($json);
        exit;

    }

?>