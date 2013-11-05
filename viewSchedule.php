<?php
/*
 * Created on April 1, 2012
 * @author Judy Yang <jyang2@bowdoin.edu>
 */

session_start();
session_cache_expire(30);
include_once("database/dbMasterSchedule.php");
include_once("domain/MasterScheduleEntry.php");

/* Just for debugging purposes */
$_SESSION['access_level'] = 2;
$_SESSION['logged_in'] = 1;
?>
<!--  page generated by the BowdoinRMH software package -->
<html>
    <head>
        <title>Master Schedule</title>
        <!--  Choose a style sheet -->
        <link rel="stylesheet" href="styles.css" type="text/css"/>
        <link rel="stylesheet" href="calendarhouse.css" type="text/css"/>
        <!-- 	<link rel="stylesheet" href="calendar_newGUI.css" type="text/css"/> -->
    </head>
    <!--  Body portion starts here -->
    <body>
        <div id="container">
            <?php include_once("header.php"); ?>
            <div id="content">
                <?php
                if ($_SESSION['access_level'] < 2) {
                    die("<p>Only managers can view the master schedule.</p>");
                }
                $this_group = $_GET["group"];
                show_master_month($this_group);
                ?>
            </div>
        </div>
    </body>
</html>



<?php
/*
 * displays the master schedule for a given group (odd or even week of the year or week of month)
 * and series of days (Mon-Sun)
 */

function show_master_month($group) {
	$group_names = array("foodbank"=>"Food Bank", "foodpantry"=>"Food Pantry","soupkitchen"=>"Soup Kitchen");
    $days = array("Mon" => "Monday", "Tue" => "Tuesday", "Wed" => "Wednesday",
                    "Thu" => "Thursday", "Fri" => "Friday", "Sat"=> "Saturday", "Sun"=> "Sunday");
    $weeks = array (1=>"1st",2=>"2nd",3=>"3rd",4=>"4th",5=>"5th");
    echo ('<br><table id="calendar" align="center" ><tr class="weekname"><td colspan="' . (sizeof($days) + 2) . '" ' .
    'bgcolor="#99B1D1" align="center" >' . $group_names[$group] . " Master Schedule</td></tr>");
    echo ('<tr><td bgcolor="#99B1D1"></td>');
    foreach ($days as $day => $dayname) 
        echo ('<td class="dow" align="center"> ' . $dayname . ' </td>');
    echo ('<td bgcolor="#99B1D1"></td></tr>');
    $shiftdefaultsizes = array ("foodbank"=>8, "foodpantry"=>6, "soupkitchen"=>9);
    foreach ($weeks as $week_no => $week) {
    	echo('<tr><td bgcolor="#99B1D1">'.$week.'</td>');
    	foreach ($days as $day => $dayname) {
            $master_shift = retrieve_dbMasterSchedule($group.$day.$week_no);
            /* retrieves a MasterScheduleEntry for this group, day, and week of the month */
            if ($master_shift) 
                echo do_shift($master_shift); 
            else if ($day=="Sat" || $day=="Sun") {
            	$master_shift = new MasterScheduleEntry($group, $day, $week_no, 0, "", "");
            	insert_dbMasterSchedule($master_shift);
                echo do_shift($master_shift);
            }
            else {
            	$master_shift = new MasterScheduleEntry($group, $day, $week_no, $shiftdefaultsizes[$group], "", "");
            	insert_dbMasterSchedule($master_shift);
                echo do_shift($master_shift);
            }
        }
        echo('<td bgcolor="#99B1D1">'.$week.'</td></tr>');
    }
    echo "</table>";
}

function do_shift($master_shift) {
    /* $master_shift is a MasterScheduleEntry object
     */
    if ($master_shift->get_slots() == 0) {
        $s = "<td>" .
                "<a id=\"shiftlink\" href=\"editMasterSchedule.php?group=" .
                $master_shift->get_group() . "&day=" . $master_shift->get_day() . "&week_no=" .
                $master_shift->get_week_no() . "\">" .
                "<br>" .
                "</td>";
    } 
    else {
        $s = "<td>" .
                "<a id=\"shiftlink\" href=\"editMasterSchedule.php?group=" .
                $master_shift->get_group() . "&day=" . $master_shift->get_day() . "&week_no=" .
                $master_shift->get_week_no() . "\">" .
                get_people_for_shift($master_shift, $master_shift_length) .
                "</td>";
    }
    return $s;
}

function get_people_for_shift($master_shift) {
    /* $master_shift is a MasterScheduleEntry object
     * an associative array of (venue, my_group, day, time, 
     * start, end, slots, persons, notes) */
    $people = get_persons($master_shift->get_group(), $master_shift->get_day(), $master_shift->get_week_no());
    $slots = get_total_slots($master_shift->get_group(), $master_shift->get_day(), $master_shift->get_week_no());
    if (!$people[0])
        array_shift($people);
    $p = "<br>";
    for ($i = 0; $i < count($people); ++$i) {
        if (is_array($people[$i]))
          if ($people[$i]['role']!="")
            $p = $p . "&nbsp;" . $people[$i]['first_name'] . " " . $people[$i]['last_name'] . " (" . $people[$i]['role'] .")<br>";
          else 
          	$p = $p . "&nbsp;" . $people[$i]['first_name'] . " " . $people[$i]['last_name'] . "<br>";
        else
            $p = $p . "&nbsp;" . $people[$i] . "<br>";
    }
    if ($slots - count($people) > 0)
        $p = $p . "&nbsp;<b>Vacancies (" . ($slots - count($people)) . ")</b><br>";
    else if (count($people) == 0)
        $p = $p . "&nbsp;)<br>";
    return substr($p, 0, strlen($p) - 5); // remove the last )<br>
}
?>