<?php
/*
 * Copyright 2013 by Brian Jacobel, Simon Brooks, Oliver Fisher, and Allen Tucker. 
 * This program is part of MCH Homebase, which is free software.  It comes with 
 * absolutely no warranty. You can redistribute and/or modify it under the terms 
 * of the GNU General Public License as published by the Free Software Foundation
 * (see <http://www.gnu.org/licenses/ for more information).
 */
?>
<!-- Begin Header -->
<style type="text/css">
    h1 {padding-left: 0px; padding-right:165px;}
</style>
<div id="header">
<!--<br><br><img src="images/rmhHeader.gif" align="center"><br>
<h1><br><br>Homebase <br></h1>-->
<div class="logout"><a href="<?=$path?>logout.php"><p>logout</p></a></div>
</div>

<div align="center" id="navigationLinks">

    <?PHP
    //set the time zone for all pages
    date_default_timezone_set("America/New_York"); 

    //Log-in security
    //If they aren't logged in, display our log-in form.
    if (!isset($_SESSION['logged_in'])) {
        include('login_form.php');
        die();
    } else if ($_SESSION['logged_in']) {

        /*         * Set our permission array.
         * anything a guest can do, a volunteer and house manager can also do
         * anything a volunteer can do, a house manager can do.
         *
         * If a page is not specified in the permission array, anyone logged into the system
         * can view it. If someone logged into the system attempts to access a page above their
         * permission level, they will be sent back to the home page.
         */
        //pages guests are allowed to view
        $permission_array['index.php'] = 0;
        $permission_array['about.php'] = 0;
        //pages volunteers can view
        $permission_array['help.php'] = 1;
        $permission_array['view.php'] = 1;
        $permission_array['personSearch.php'] = 1;
        $permission_array['personEdit.php'] = 1;
        $permission_array['calendar.php'] = 1;
        //pages only managers can view
        $permission_array['personEdit.php'] = 2;
        $permission_array['viewSchedule.php'] = 2;
        $permission_array['addMonth.php'] = 2;
        $permission_array['editCrew.php'] = 2;
        $permission_array['log.php'] = 2;

        //Check if they're at a valid page for their access level.
        $current_page = substr($_SERVER['PHP_SELF'], 1);
      //  echo "current page = ".$current_page;
        if ($permission_array[$current_page] > $_SESSION['access_level']) {
            //in this case, the user doesn't have permission to view this page.
            //we redirect them to the index page.
            echo "<script type=\"text/javascript\">window.location = \"index.php\";</script>";
            //note: if javascript is disabled for a user's browser, it would still show the page.
            //so we die().
            die();
        }

        //This line gives us the path to the html pages in question, useful if the server isn't installed @ root.
        $path = strrev(substr(strrev($_SERVER['SCRIPT_NAME']), strpos(strrev($_SERVER['SCRIPT_NAME']), '/')));
		$month = date('y-m');
        //they're logged in and session variables are set.
        if ($_SESSION['access_level'] >= 0) {
            echo('<a href="' . $path . 'index.php"><b>home</b></a> | ');
            echo('<a href="' . $path . 'about.php"><b>about</b></a>');
        }
        if ($_SESSION['access_level'] >= 1)
            echo(' | <a href="' . $path . 'help.php?helpPage=' . $current_page . '" target="_BLANK"><b>help</b></a>');
        if ($_SESSION['access_level'] == 0)
            echo(' | <a href="' . $path . 'personEdit.php?id=' . 'new' . '"><b>apply</b></a>');
        if ($_SESSION['access_level'] >= 1) {
            echo(' | <a href="' . $path . 'calendar.php?month='.$month.'&edit=false"><strong>calendars</strong> </a>');
        }
        if ($_SESSION['access_level'] >= 2) {
            echo('| <a href="' . $path . 'viewSchedule.php?group=foodbank"><strong>master schedule</strong></a>');
            echo('<br><strong>volunteers :</strong> <a href="' . $path . 'personSearch.php">search</a>, 
			        <a href="personEdit.php?id=' . 'new' . '">add </a> | ');
            echo('<a href="' . $path . 'dataSearch.php"><strong>reports</strong></a> ');
        }
    }
    ?>
</div>
<!-- End Header -->