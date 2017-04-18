<?php
    ini_set('display_errors', 'on');
    error_reporting(E_ALL);

    require_once '../../config.php';

    $tab_index = -1;

    if (isloggedin()) {
        $context_sys = get_context_instance(CONTEXT_SYSTEM);

        if (has_capability('moodle/course:create', $context_sys)) {
            $moodle_url = new moodle_url('/');
            $site_url = $moodle_url->__toString();
            $cat = $DB->get_records('question_categories');

            if (!is_numeric($_REQUEST['cid']) || (int) $_REQUEST['cid'] < 0) {
                http_response_code(400);
                exit('Bad request.');
            }

            $qbank = $DB->get_records_select('question', sprintf('category = "%d" AND "questiontext" != "0"', (int) $_REQUEST['cid']));
            $qans = [];

            foreach ($qbank as $qid => $v) {
                $ret = $DB->get_records_select('question_answers', sprintf('question = "%d" AND fraction > 0 ORDER BY id ASC LIMIT 1', $qid));

                foreach ($ret as $aid => $av) {
                    $qans[$qid] = [$aid, strip_tags(str_ireplace(['<p>', '</p><p>', '</p>', '<br>', '<br />', '<br >'], [null, PHP_EOL, null, PHP_EOL, PHP_EOL, PHP_EOL], $av->answer))];
                    break;
                }
            }

            if (isset($_COOKIE['editor_zoom']) && is_numeric($_COOKIE['editor_zoom']) && (int) $_COOKIE['editor_zoom'] <= 4 && (int) $_COOKIE['editor_zoom'] >= -4) {
                $scale = 1 + $_COOKIE['editor_zoom'] / 10;
            } else {
                $scale = 1;
            }

        } else {
            http_response_code(403);
            exit('You do not have sufficient permission to perform this operation (Requires: At least Course Creator role).');
        }
    } else {
        http_response_code(403);
        exit('You are not logged in.');
    }

    function getTabIndex() {
        global $tab_index;

        $tab_index++;
        return $tab_index;
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=<?= $scale ?>, user-scalable=no">
        <meta name="description" content="Moodle Question Bank Inline Editor">
        <meta name="author" content="Robin Liu">
        <title>Moodle Q-Bank Smart Editor</title>
        <link rel="shortcut icon" href="<?= $site_url ?>theme/image.php/clean/theme/inline/favicon" />
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <link href="https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/flatly/bootstrap.min.css" rel="stylesheet" type="text/css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet" type="text/css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">
        <link href="assets/css/custom.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div class="container">
            <nav class="navbar navbar-default navbar-fixed-top">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a class="navbar-brand">Moodle Q-Bank</a>
                    </div>
                    <div id="navbar" class="navbar-collapse collapse">
                        <ul class="nav navbar-nav">
                            <li>
                                <a href="./">Category</a>
                            </li>
                            <li class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#">Font Size <span class="caret"></span></a>
                                <ul class="dropdown-menu" aria-labelledby="themes">
                                    <li><a href="./update?action=changeFontSize&size=-4&goto=/qbank?cid=<?= (int) $_REQUEST['cid'] ?>">Smaller -4</a></li>
                                    <li><a href="./update?action=changeFontSize&size=-3&goto=/qbank?cid=<?= (int) $_REQUEST['cid'] ?>">Smaller -3</a></li>
                                    <li><a href="./update?action=changeFontSize&size=-2&goto=/qbank?cid=<?= (int) $_REQUEST['cid'] ?>">Smaller -2</a></li>
                                    <li><a href="./update?action=changeFontSize&size=-1&goto=/qbank?cid=<?= (int) $_REQUEST['cid'] ?>">Smaller -1</a></li>
                                    <li class="divider"></li>
                                    <li><a href="./update?action=changeFontSize&size=0&goto=/qbank?cid=<?= (int) $_REQUEST['cid'] ?>">Normal</a></li>
                                    <li class="divider"></li>
                                    <li><a href="./update?action=changeFontSize&size=1&goto=/qbank?cid=<?= (int) $_REQUEST['cid'] ?>">Larger +1</a></li>
                                    <li><a href="./update?action=changeFontSize&size=2&goto=/qbank?cid=<?= (int) $_REQUEST['cid'] ?>">Larger +2</a></li>
                                    <li><a href="./update?action=changeFontSize&size=3&goto=/qbank?cid=<?= (int) $_REQUEST['cid'] ?>">Larger +3</a></li>
                                    <li><a href="./update?action=changeFontSize&size=4&goto=/qbank?cid=<?= (int) $_REQUEST['cid'] ?>">Larger +4</a></li>
                                </ul>
                            </li>
                        </ul>
                        <ul class="nav navbar-nav navbar-right">
                            <li class="active">
                                <a href="<?= $site_url ?>" target="_blank">Return to Moodle >></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            <div class="jumbotron">
                <h2>Manage Questions</h2>
                <p>You are currently managing questions in category ID <?= $_REQUEST['cid'] ?>.</p>
            </div>
            <input type="hidden" id="cat-id" value="<?= (int) $_REQUEST['cid'] ?>">

        </div>

        <table class="table table-hover table-sm">
            <thead>
              <tr>
                <th class="col-xs-1">ID</th>
                <th class="col-xs-2">Question Name</th>
                <th class="col-xs-4">Question Text</th>
                <th class="col-xs-3">Correct Answer</th>
                <th class="col-xs-1">Feedback</th>
                <th class="col-xs-1">Edit</th>
              </tr>
            </thead>
            <tbody id="question-table">
            <?php foreach ($qbank as $k => $v) { ?>
                <?php
                    $strip_obj = ['name', 'questiontext', 'correctans', 'generalfeedback'];

                    for ($i = 0; $i < count($strip_obj); $i++) {
                        $v->$strip_obj[$i] = strip_tags(str_ireplace(['<p>', '</p><p>', '</p>', '<br>', '<br />', '<br >'], [null, PHP_EOL, null, PHP_EOL, PHP_EOL, PHP_EOL], $v->$strip_obj[$i]));
                    }
                ?>

            <tr id="question-row-<?= $v->id ?>">
                <td style="vertical-align: middle"><input class="qbank-bulk" type="checkbox" data-pk="<?= $v->id ?>" tabindex=-1> &nbsp;<?= $v->id ?></td>
                <td><a href="#" class="qbank-edit editable-item" data-name="qbank-name" data-type="textarea" data-pk="<?= $v->id ?>" data-title="Question name" focusitem><?= $v->name ?></a></td>
                <td><a href="#" class="qbank-edit editable-item" data-name="qbank-info" data-type="textarea" data-pk="<?= $v->id ?>" data-title="Question Text" focusitem><?= $v->questiontext ?></a></td>
                <td>
                    <?php if (!empty($qans[$v->id][1])) { ?>
                        <a href="#" class="qbank-edit editable-item" data-name="qbank-correctans" data-type="textarea" data-pk="<?= $qans[$v->id][0] ?>" data-title="Correct Answer" focusitem"><?= $qans[$v->id][1] ?></a>
                    <?php } else { ?>
                        (No correct answer)
                    <?php } ?>
                </td>
                <td><?= substr($v->qtype, 0, 4) !== 'beep' ? null : '<i class="fa fa-volume-up" aria-hidden="true"></i> ' ?><a href="#" class="qbank-edit editable-item" data-name="qbank-feedback" data-type="textarea" data-pk="<?= $v->id ?>" data-title="General Feedback" focusitem"><?= $v->generalfeedback ?></a></td>
                <td><a href="#" data-pk="<?= $v->id ?>" data-keyboard="true" class="qbank-edit-modal btn btn-info btn-xs"><i class="fa fa-paint-brush" aria-hidden="true"></i> Answer</a></td>
            </tr>

            <?php } ?>

            <tr class="active" id="question-tadd-general">
                <td style="vertical-align: middle"><i class="fa fa-plus-circle" aria-hidden="true"></i></td>
                <td><a href="#" id="qbank-tadd-name" class="qbank-tadd editable-item" data-emptytext="( Question Name )" data-name="qbank-tadd-name" data-type="textarea" data-title="Question Name" data-placeholder="Enter Question Name" focusitem></a></td>
                <td><a href="#" id="qbank-tadd-text" class="qbank-tadd editable-item" data-emptytext="( Question Text )" data-name="qbank-tadd-text" data-type="textarea" data-title="Question Text" data-placeholder="Enter Question Text" focusitem></a></td>
                <td><a href="#" id="qbank-tadd-ans0-text" class="qbank-tadd editable-item" data-emptytext="( Correct Answer Text )" data-name="qbank-tadd-ans0-text" data-type="textarea" data-title="Correct Answer" data-placeholder="Enter Correct Answer" focusitem></a></td>
                <td><label id="qbank-tadd-beep-label">Beep &nbsp; <input id="qbank-tadd-beep" class="qbank-tadd-chkbox" type="checkbox" checked="checked" focusitem></label></td>
                <td></td>
            </tr>
 
            <?php for ($i = 1; $i < 4; $i++) { ?>

            <tr class="active" id="question-tadd-ans<?= $i ?>">
                <td></td>
                <td style="vertical-align: middle">Answer <?= ($i + 1) ?></td>
                <td><a href="#" id="qbank-tadd-ans<?= $i ?>-text" class="qbank-tadd editable-item" data-emptytext="( Answer <?= ($i + 1) ?> Text )" data-name="qbank-tadd-ans<?= $i ?>-text" data-type="textarea" data-placeholder="Enter Answer <?= ($i + 1) ?> Text" focusitem></a></td>
                <td><label>&#10004; <input id="qbank-tadd-ans<?= $i ?>-correct" class="qbank-tadd-chkbox" type="checkbox" focusitem></label></td>
                <td></td>
                <td></td>
            </tr>

            <?php } ?>

            <tr class="active">
                <td></td>
                <td style="vertical-align: middle"><span class="question-tadd-generalfeedback">General Feedback</span></td>
                <td><a href="#" id="qbank-tadd-feedback" class="qbank-tadd editable-item question-tadd-generalfeedback" data-emptytext="( General Feedback Text )" data-name="qbank-tadd-feedback" data-type="textarea" data-title="General Feedback" data-placeholder="Enter General Feedback" focusitem></a></td>
                <td>
                <td>
                <td><a href="#" class="btn btn-success btn-xs" id="qbank-tadd-submit"><i class="fa fa-plus-circle" aria-hidden="true" focusitem></i> Add Question</a></td>
            </tr>

            </tbody>
            
            <tr>
                <td style="vertical-align: middle"><input type="checkbox" id="qbank-selectall"> <strong>All</strong></td>
                <td>
                    <select class="form-control input-sm" id="qbank-bulk-select" focusitem>
                        <option selected disabled>Select Bulk Action</option>
                        <optgroup label="General Actions">
                            <option value="del">Delete</option>
                            <option value="beep">Beep On/Off</option>
                        </optgroup>
                        <optgroup label="Move to Category...">
                        <?php foreach ($cat as $k => $v) { ?>
                            <?php if ($v->id == (int) $_REQUEST['cid']) continue; ?>

                            <option value="cat-<?= $v->id ?>"><?= '(' . $v->id . ') '. $v->name ?></option>

                        <?php } ?>

                      </optgroup>
                    </select>
                </td>
                <td><a href="#" class="btn btn-warning btn-sm" id="qbank-bulk-exec" style="display: none" focusitem>Bulk Action</a></td>
                <td><label><input type="checkbox" id="is_brainstorm" focusitem> &nbsp;Brainstorm Mode</label></td>
                <td></td>
                <td></td>
            </tr>
            
        </table>
      
		<div id="modal-edit" class="modal" tabindex="-1">
		    <div class="modal-dialog larger-modal">
		        <div class="modal-content">
		            <div class="modal-header">
		                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		                <h4 class="modal-title">Edit Answer #<span id="modal-edit-ansid">0</span></h4>
		            </div>
		            <div class="modal-body">
		            	<div class="row" id="edit-ques-ans-alert" style="display: none">
			                <div class="col-xs-12">
			                    <div id="edit-ques-ans-alert-context" class="alert">
			                        <span id="edit-ques-ans-alert-content"></span>
			                    </div>
			                </div>
			            </div>
		                <div class="row">
			                <div id="edit-ques-ans-0-group">
			                    <div class="col-xs-5">
			                        <label for="edit-ques-ans-0-text">Answer 1 Text:</label>
			                        <textarea class="form-control edit-ques-ans" id="edit-ques-ans-0-text" autofocus="true"></textarea>
			                    </div>
			                    <div class="col-xs-1">
			                        <label>&#10004; <input class="edit-ques-ans" tabindex="-1" type="checkbox" id="edit-ques-ans-0-correct"></label>
			                    </div>
			                </div>

			                <div id="edit-ques-ans-1-group">
			                    <div class="col-xs-5">
			                        <label for="edit-ques-ans-1-text">Answer 2 Text:</label>
			                        <textarea class="form-control edit-ques-ans" id="edit-ques-ans-1-text"></textarea>
			                    </div>
			                    <div class="col-xs-1">
			                        <label>&#10004; <input class="edit-ques-ans" tabindex="-1" type="checkbox" id="edit-ques-ans-1-correct"></label>
			                    </div>
			                </div>
			            </div>
			            <div class="row">
			                <div id="edit-ques-ans-2-group">
			                    <div class="col-xs-5">
			                        <label for="edit-ques-ans-2-text">Answer 3 Text:</label>
			                        <textarea class="form-control edit-ques-ans" id="edit-ques-ans-2-text"></textarea>
			                    </div>
			                    <div class="col-xs-1">
			                        <label>&#10004; <input class="edit-ques-ans" tabindex="-1" type="checkbox" id="edit-ques-ans-2-correct"></label>
			                    </div>
			                </div>

			                <div id="edit-ques-ans-3-group">
			                    <div class="col-xs-5">
			                        <label for="edit-ques-ans-3-text">Answer 4 Text:</label>
			                        <textarea class="form-control edit-ques-ans" id="edit-ques-ans-3-text"></textarea>
			                    </div>
			                    <div class="col-xs-1">
			                        <label>&#10004; <input class="edit-ques-ans" tabindex="-1" type="checkbox" id="edit-ques-ans-3-correct"></label>
			                    </div>
			                </div>
			            </div>
		            </div>
		            <div class="modal-footer">
		            	<button id="modal-edit-submit" data-qid="-1" type="button" class="btn btn-primary btn-sm"><i class="fa fa-floppy-o" aria-hidden="true"></i> Update Answer</button>
		                <button type="button" class="btn btn-warning btn-sm" data-dismiss="modal"><i class="fa fa-trash-o" aria-hidden="true"></i> Cancel</button>
		            </div>
		        </div>
		    </div>
		</div>

        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/ie10-viewport-bug-workaround.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
        <script src="assets/js/custom.js"></script>
    </body>
</html>
