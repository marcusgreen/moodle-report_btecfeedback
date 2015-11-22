<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Report on assignments that use the BTEC advanced grading type
 *
 * @since      2.9
 * @package    report_btecfeedback
 * @copyright  Marcus Green 2015
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
global $PAGE, $COURSE, $DB, $CFG;

$courseid = required_param('id', PARAM_INT);
$groupid = optional_param('group', null, PARAM_INT);

// Check permissions
require_login($courseid);
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('dataTables', 'report_btecfeedback');

$PAGE->set_context(context_course::instance($COURSE->id));
$url = new moodle_url('/report/btecfeedback/index.php');

$PAGE->set_url($url);
$PAGE->set_pagelayout('report');
$PAGE->set_heading($COURSE->fullname);
$PAGE->set_title('btecfeedback', 'report_btecfeedback');
echo $OUTPUT->header();
/*
  $table = new html_table();
  $table->attributes=array('width'=>'10%');
  $table->head = array('Date','Time');
  $table->data = array(array('Today', 'The Time'));
  echo html_writer::table($table);
 * 
 */

$PAGE->navigation->add(get_string('pluginname', 'report_btecfeedback'), $url);
$report = new report_btecfeedback();
$report->init($courseid);
$users = $report->get_students($courseid, $groupid);
$assigns = $report->get_all_assigns($courseid);
print $report->course->fullname;
$URL = $CFG->wwwroot . '/report/btecfeedback/index.php';

foreach ($report->groups as $id => $name) {
    $groups[$URL . '?id=' . $courseid . '&group=' . $id] = $name;
}
$default = $URL . '?id=' . $courseid;
$select = new url_select($groups, null, array($default => 'Select'));
if (isset($groupid)) {
    $select->selected = $URL . '?id=' . $courseid . '&group=' . $groupid;
}
$select->set_label('Group');
echo $OUTPUT->render($select);
/* explains what the letters in the cells mean, e.g. N for No submission */
echo get_string('key', 'report_btecfeedback');

$maxcriteria = $report->get_max_criteria($courseid);

$submissionstatus = $report->get_submission_status($courseid);


print "<table id='grades' border=1>";
echo "<thead>";
echo "<tr>";

foreach ($users as $user) {
    echo "<td colspan=4 width=10%>First Name</td>";
    print "<td>" . $user->firstname . "</td>";
    echo "<td colspan=5 >Last Name</td>";
    print "<td>" . $user->lastname . "</td></tr>";
    foreach ($assigns as $a) {
            $assignment_name = $a->assignment_name;
            $assignment_name = substr($assignment_name, 0, 15);
            $assignment_name = $assignment_name . "...";
            print "<tr><td colspan=13 title='" . $a->assignment_name . "'>" . $assignment_name . "</td></tr>";
            $criteria = $report->get_assign_criteria($a->coursemodid);
            foreach ($criteria as $c) {
                print "<th colspan=4 class='criteria'>" . $c->shortname . "</th>";
                $g = $report->get_user_criteria_grades($user->userid, $a->coursemodid, $c->criteriaid);
                if ($g == 'A') {
                    $tag = '<td colspan=5 class="achieved" width=2%>';
                } else if ($g == 'N') {
                    $tag = '<td colspan=5 class="notmet"width=2%>';
                } else {
                    $tag = '<td>';
                }
                print $tag;
                print $g->grade;
                print '</td>';
                print '<td colspan=6>';
                print $g->remark;
                print '</td>';
                echo "</tr>";
            }
            echo "<tr>";
            print '<td colspan=7>';
            print ($report->get_overall_feedback($a->assignid,$courseid,$user->userid));
            print '</td>';
            echo "</tr>";
    }
}
/* echo "<th>Last Name</th>";
  $counter = 0;
  foreach ($assigns as $a) {
  $assignment_name = $a->assignment_name;
  if (strlen($assignment_name) > 15) {
  $assignment_name = substr($assignment_name, 0, 15);
  $assignment_name = $assignment_name . "...";
  }
  print "<th title='" . $a->assignment_name . "'>" . $assignment_name . "</th>";
  $criteria = $report->get_assign_criteria($a->coursemodid);
  foreach ($criteria as $c) {
  print "<th class='criteria'>" . $c->shortname . "</th>";
  }
  }
  print "<th>Total</th></tr>";
  echo "</thead>";
 */

/* $ug = $report->get_all_usergrades($user, $assigns);
  foreach ($assigns as $a) {
  $criteria = $report->get_assign_criteria($a->coursemodid);
  $usergrade = $report->get_user_grade($user, $a);
  $tag = "<td>";
  if ($usergrade->grade == 'R') {
  $tag = "<td class='refer'>";
  } elseif ($usergrade->grade == 'P') {
  $tag = "<td class='achieved'>";
  }

  $textclass = "";
  if ($usergrade->grade == '!') {
  $textclass = 'newsub';
  }
  $link = "<a href=../../mod/assign/view.php?id=" . $a->coursemodid . "&rownum=0&action=grade class='$textclass'>";

  print $tag . $link . $usergrade->grade . "</a></td>";

  foreach ($criteria as $c) {
  $g = $report->get_user_criteria_grades($user->userid, $a->coursemodid, $c->criteriaid);
  if ($g == 'A') {
  $tag = '<td class="achieved">';
  } else if ($g == 'N') {
  $tag = '<td class="notmet">';
  } else {
  $tag = '<td>';
  }
  print $tag;
  print $g;
  print '</td>';
  }
  }

  /* calculated grade for all assignments */
/* $overallgrade = $report->num_to_letter($ug->modulegrade);
  $tag = '<td class=' . $report->grade_style($overallgrade) . '>';
  print $tag;
  print $overallgrade;
  echo "</td>";
  print "</tr>";
  }
 */

print "</table>";
/*
$noassigns = get_string('noassigns', 'report_btecfeedback');
echo "<script>$('#grades' ) .dataTable({"
 . "oLanguage: { "
 . "sEmptyTable:' $noassigns '"
 . "},"
 . "aaSorting: [], "
 . "iDisplayLength:30, "
 . "aLengthMenu : [30, 50, 100], "
 . "});"
 . "</script>";
echo $OUTPUT->footer();
 * */
 