<?php
    ini_set('display_errors', 'on');
    error_reporting(E_ALL);

    require_once '../../config.php';

    if (isloggedin()) {
        $context_sys = get_context_instance(CONTEXT_SYSTEM);

        if (has_capability('moodle/course:create', $context_sys)) {
            $moodle_url = new moodle_url('/');
            $site_url = $moodle_url->__toString();
            $cat = $DB->get_records('question_categories');
        } else {
            http_response_code(403);
            exit('You do not have sufficient permission to perform this operation (Requires: At least Course Creator role).');
        }

        $table_prefix = $CFG->prefix;
        $course_sql = <<<SQL
SELECT  {$table_prefix}context.instanceid AS 'CourseID',
        {$table_prefix}course.fullname    AS 'CourseName',
        {$table_prefix}context.id         AS 'ContextID',
        {$table_prefix}role_assignments.userid AS 'UserID'
FROM    {$table_prefix}role_assignments
JOIN    {$table_prefix}context
ON      {$table_prefix}role_assignments.contextid = {$table_prefix}context.id
JOIN    {$table_prefix}course
ON      {$table_prefix}context.instanceid = {$table_prefix}course.id
JOIN    {$table_prefix}course_categories
ON      {$table_prefix}course.category = {$table_prefix}course_categories.id
WHERE   {$table_prefix}context.contextlevel = '50'
AND     roleid <= 3
ORDER BY category ASC
SQL;

        $courses = $DB->get_records_sql($course_sql);

        if (isset($_COOKIE['editor_zoom']) && is_numeric($_COOKIE['editor_zoom']) && (int) $_COOKIE['editor_zoom'] <= 4 && (int) $_COOKIE['editor_zoom'] >= -4) {
            $scale = 1 + $_COOKIE['editor_zoom'] / 10;
        } else {
            $scale = 1;
        }

    } else {
        http_response_code(403);
        exit('You are not logged in.');
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
                                    <li><a href="./update?action=changeFontSize&size=-4&goto=/">Smaller -4</a></li>
                                    <li><a href="./update?action=changeFontSize&size=-3&goto=/">Smaller -3</a></li>
                                    <li><a href="./update?action=changeFontSize&size=-2&goto=/">Smaller -2</a></li>
                                    <li><a href="./update?action=changeFontSize&size=-1&goto=/">Smaller -1</a></li>
                                    <li class="divider"></li>
                                    <li><a href="./update?action=changeFontSize&size=0&goto=/">Normal</a></li>
                                    <li class="divider"></li>
                                    <li><a href="./update?action=changeFontSize&size=1&goto=/">Larger +1</a></li>
                                    <li><a href="./update?action=changeFontSize&size=2&goto=/">Larger +2</a></li>
                                    <li><a href="./update?action=changeFontSize&size=3&goto=/">Larger +3</a></li>
                                    <li><a href="./update?action=changeFontSize&size=4&goto=/">Larger +4</a></li>
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
                <h2>Question Bank Categories</h2>
                <p>Select a category from below to manage associated questions.</p>
            </div>
        </div>

        <table class="table table-striped">
            <thead>
              <tr>
                <th class="col-xs-1">ID</th>
                <th class="col-xs-4">Name</th>
                <th class="col-xs-6">Description</th>
                <th class="col-xs-1">Action</th>
              </tr>
            </thead>
            <tbody id="table-body">
				<?php foreach ($cat as $k => $v) { ?>

				<tr>
					<td style="vertical-align: middle"><input class="cat-bulk" class="checkbox" type="checkbox" data-pk="<?= $v->id ?>"> &nbsp;<?= $v->id ?></td>
					<td><a href="#" class="cat-edit editable-item" data-name="cat-name" data-type="text" data-pk="<?= $v->id ?>" data-title="Category name"><?= $v->name ?></a></td>
					<td><a href="#" class="cat-edit editable-item" data-name="cat-info" data-type="text" data-pk="<?= $v->id ?>" data-title="Category info"><?= $v->info ?></a></td>
					<td align="center">
                        <a href="qbank?cid=<?= $v->id ?>" class="btn btn-info btn-xs cat-manage">Manage</a>
                        <a href="#" data-cid="<?= $v->id ?>" class="btn btn-default btn-xs cat-fixfback">Fix</a>
                    </td>
				</tr>

				<?php } ?>

                <tr id="table-bulk">
                    <td style="vertical-align: middle">+</td>
                    <td><a href="#" class="cat-add editable-item-new editable-item" id="cat-add-name" data-type="text" data-title="Category name"></a></td>
                    <td><a href="#" class="cat-add editable-item-new editable-item" id="cat-add-info" data-type="text" data-title="Category info"></a></td>
                    <td align="center">
                        <div class="btn-group">
                            <a id="cat-create-btn" href="#" class="btn btn-success btn-xs dropdown-toggle" data-toggle="dropdown">+ Create</a>
                            <ul class="dropdown-menu">

                                <?php foreach ($courses as $k => $v) { ?>

                                <li><a href="#" class="cat-add-dropdown" data-cid="<?= $v->contextid ?>">(<?= $v->courseid ?>) <?= $v->coursename ?></a></li>

                                <?php } ?>

                            </ul>
                        </div>
                    </td>
                </tr>
            </tbody>

            <tr>
                <td style="vertical-align: middle"><input type="checkbox" id="cat-selectall">&nbsp; <strong>All</strong></td>
                <td>
                    <select class="form-control input-sm" id="cat-bulk-select">
                        <option selected disabled>Select Bulk Action</option>
                        <optgroup label="General Actions">
                            <option value="del">Delete</option>
                        </optgroup>
                    </select>
                </td>
                <td><a href="#" class="btn btn-warning btn-sm" id="cat-bulk-exec" style="display: none">Bulk Action</a></td>
                <td></td>
            </tr>

        </table>
   
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/ie10-viewport-bug-workaround.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
        <script src="assets/js/custom.js"></script>
    </body>
</html>
