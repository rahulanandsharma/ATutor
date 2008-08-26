<?php
/****************************************************************/
/* ATutor														*/
/****************************************************************/
/* Copyright (c) 2002-2008 by Greg Gay & Joel Kronenberg        */
/* Adaptive Technology Resource Centre / University of Toronto  */
/* http://atutor.ca												*/
/*                                                              */
/* This program is free software. You can redistribute it and/or*/
/* modify it under the terms of the GNU General Public License  */
/* as published by the Free Software Foundation.				*/
/****************************************************************/
// $Id$
define('AT_INCLUDE_PATH', '../include/');
require(AT_INCLUDE_PATH.'vitals.inc.php');
require(AT_INCLUDE_PATH.'lib/test_result_functions.inc.php');

// test authentication
$tid = intval($_GET['tid']);


// make sure max attempts not reached, and still on going
$sql		= "SELECT *, UNIX_TIMESTAMP(start_date) AS start_date2, UNIX_TIMESTAMP(end_date) AS end_date2 FROM ".TABLE_PREFIX."tests WHERE test_id=".$tid." AND course_id=".$_SESSION['course_id'];
$result = mysql_query($sql, $db);
$test_row = mysql_fetch_assoc($result);
/* check to make sure we can access this test: */
if (!$test_row['guests'] && ($_SESSION['enroll'] == AT_ENROLL_NO || $_SESSION['enroll'] == AT_ENROLL_ALUMNUS)) {
	require(AT_INCLUDE_PATH.'header.inc.php');
	$msg->printInfos('NOT_ENROLLED');
	require(AT_INCLUDE_PATH.'footer.inc.php');
	exit;
}
if (!$test_row['guests'] && !authenticate_test($tid)) {
	header('Location: my_tests.php');
	exit;
}

// checks one/all questions per page, and forward user to the correct one
if (isset($_GET['action']) && $_GET['action']=='cancel') {
	$msg->addFeedback('CANCELLED');
	header('Location: '.url_rewrite('tools/my_tests.php', AT_PRETTY_URL_IS_HEADER));
	exit;
} else if (isset($_GET['action']) && $_GET['action']=='begin') {
	if ($test_row['display']) {
		header('Location: '.url_rewrite('tools/take_test_q.php?tid='.$tid, AT_PRETTY_URL_IS_HEADER));
	} else {
		header('Location: '.url_rewrite('tools/take_test.php?tid='.$tid, AT_PRETTY_URL_IS_HEADER));
	}
	exit;
}

/* 
 * If max attempted reached, then stop it.
 * @3300
 */
$sql = "SELECT COUNT(*) AS cnt FROM ".TABLE_PREFIX."tests_results WHERE status=1 AND test_id=".$tid." AND member_id=".$_SESSION['member_id'];
if ( (($test_row['start_date2'] > time()) || ($test_row['end_date2'] < time())) || 
   ( ($test_row['num_takes'] != AT_TESTS_TAKE_UNLIMITED) && ($takes['cnt'] >= $test_row['num_takes']) )  ) {
	require(AT_INCLUDE_PATH.'header.inc.php');
	$msg->printErrors('MAX_ATTEMPTS');
	
	require(AT_INCLUDE_PATH.'footer.inc.php');
	exit;
}

require(AT_INCLUDE_PATH.'header.inc.php');

// get number of attempts
$sql    = "SELECT COUNT(test_id) AS cnt FROM ".TABLE_PREFIX."tests_results WHERE status=1 AND test_id=$tid AND member_id={$_SESSION['member_id']}";
$result = mysql_query($sql, $db);
if ($row = mysql_fetch_assoc($result)) {
	$num_takes = $row['cnt'];
} else {
	$num_takes = 0;
}

if (!$test_row['random']) {
	$sql	= "SELECT COUNT(*) AS num_questions FROM ".TABLE_PREFIX."tests_questions_assoc WHERE test_id=$tid";
	$result = mysql_query($sql, $db);
	if ($row = mysql_fetch_assoc($result)) {
		$test_row['num_questions'] = $row['num_questions'];
	} // else 0
}	
?>

<div class="input-form">
		<fieldset class="group_form"><legend class="group_form"><?php echo $test_row['title']; ?></legend><div class="row">


	<div class="row">
		<dl class="col-list">
			<dt><?php echo _AT('test_description'); ?></dt>
			<dd><?php echo $test_row['description']; ?></dd>

			<dt><?php echo _AT('questions'); ?></dt>
			<dd><?php echo $test_row['num_questions']; ?></dd>

			<dt><?php echo _AT('out_of'); ?></dt>
			<dd><?php echo $test_row['out_of']; ?></dd>
	
			<dt><?php echo _AT('attempts'); ?></dt>
			<dd><?php echo $num_takes; ?> / <?php echo ($test_row['num_takes'] == AT_TESTS_TAKE_UNLIMITED) ? _AT('unlimited') : $test_row['num_takes']; ?></dd>
			
			<dt><?php echo _AT('start_date'); ?></dt>
			<dd><?php echo AT_date(	_AT('announcement_date_format'), $test_row['start_date'], AT_DATE_MYSQL_DATETIME); ?></dd>

			<dt><?php echo _AT('end_date'); ?></dt>
			<dd><?php echo AT_date(	_AT('announcement_date_format'), $test_row['end_date'], AT_DATE_MYSQL_DATETIME); ?></dd>

			<dt><?php echo _AT('anonymous'); ?></dt>
			<dd><?php echo $test_row['anonymous'] ? _AT('yes') : _AT('no'); ?></dd>

			<dt><?php echo _AT('display'); ?></dt>
			<dd><?php echo $test_row['display'] ? _AT('one_question_per_page') : _AT('all_questions_on_page'); ?></dd>
		</dl>
	</div>

	<?php if ($test_row['instructions']): ?>
	<div class="row">
		<h3><?php echo _AT('instructions'); ?></h3>
		<p><?php echo nl2br($test_row['instructions']); ?></p>
	</div>
	<?php endif; ?>

	<div>
		<a href="<?php echo url_rewrite($_SERVER['PHP_SELF'].'?tid='.$tid.SEP.'action=begin'); ?>" class="button" style="padding: 5px;"><?php echo _AT('start_test');?></a>
		<a href="<?php echo url_rewrite($_SERVER['PHP_SELF'].'?tid='.$tid.SEP.'action=cancel'); ?>" class="button" style="padding: 5px;"><?php echo _AT('cancel');?></a>
	</div>

	</div>
	</fieldset>
</div>

<?php require(AT_INCLUDE_PATH.'footer.inc.php'); ?>