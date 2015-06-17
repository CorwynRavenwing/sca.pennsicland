<html>
<head>
<title>PW Land Admin SQL</title>
</head>
<body>
<?php
# http://forums.devshed.com/mysql-help-4/create-table-php-strings-165919.html

ini_set('track_errors', 1);

require_once("include/connect.php");

$data_dir = "sql/";

$NOW = time();

$cmd_ignore = $_GET['ignore'];
$cmd_view   = $_GET['view'];
$cmd_scan   = $_GET['scan'];
$cmd_design = $_GET['design'];
$cmd_check  = $_GET['check'];

if ($cmd_ignore) {
    $tablename = $cmd_ignore;
    print "<h2>IGNORE $tablename</h2>\n";
    $ignore_file  = $data_dir . $tablename . ".ign";
    safe_put_contents($ignore_file, "IGNORE TABLE");
}

if ($cmd_view) {
    $tablename = $cmd_view;
    print "<h2>VIEW $tablename</h2>\n";
    
    print "<pre>" . get_create_sql($tablename) . "</pre>\n";

    print "<h3>\n";
    print "<a href='?ignore=$tablename'>IGNORE</a>&nbsp;&nbsp;\n";
    print "<a href='?scan=$tablename'>AS-BUILT</a>&nbsp;&nbsp;\n";
    print "</h3>\n";
}

if ($cmd_scan) {
    $tablename = $cmd_scan;
    print "<h2>SCAN AS-BUILT $tablename</h2>\n";
    $asbuilt_file = $data_dir . $tablename . "_asbuilt.sql";
    $asbuilt_data = get_create_sql($tablename);
    safe_put_contents($asbuilt_file, $asbuilt_data);
}

if ($cmd_design) {
    $tablename = $cmd_design;
    print "<h2>DESIGN $tablename</h2>\n";
    $design_file  = $data_dir . $tablename . "_design.sql";
    $design_data = get_create_sql($tablename);
    safe_put_contents($design_file, $design_data);
}

if ($cmd_check) {
    $tablename = $cmd_check;
    print "<h2>CHECK $tablename</h2>\n";
    $asbuilt_file = $data_dir . $tablename . "_asbuilt.sql";
    $design_file  = $data_dir . $tablename . "_design.sql";
}

// ========== BEGIN LIST OF TABLES ========== //

$sql1 = 'SHOW TABLES FROM ' .$db_dbname;
$res1 = mysql_query($sql1)
    or die('Query 1 error:<br />' .mysql_error());
$count = 0;

print "<table border=1 cellpadding=1 cellspacing=0 width='90%'>\n";
print "<tr style='background-color:silver; text-align:center; font-weight:bold;'>\n";
print "<td>TABLE NAME</td>\n";
print "<td>AS-BUILT FILE</td>\n";
print "<td>COMMANDS</td>\n";
print "<td>DESIGN FILE</td>\n";
print "<td>COMMANDS</td>\n";
print "</tr>\n";

while ( $row = mysql_fetch_row($res1) ) {
    $count++;

    $tablename = $row[0];

    $ignore_file  = $data_dir . $tablename . ".ign";
    $asbuilt_file = $data_dir . $tablename . "_asbuilt.sql";
    $design_file  = $data_dir . $tablename . "_design.sql";

    $ignore_exists  = file_exists($ignore_file);

    $asbuilt_exists = file_exists($asbuilt_file);
    $asbuilt_size   = @filesize($asbuilt_file);
    $asbuilt_mtime  = @filemtime($asbuilt_file);

    $design_exists  = file_exists($design_file);
    $design_size    = @filesize($design_file);
    $design_mtime   = @filemtime($design_file);

    if ($ignore_exists) {
        print "<!-- ignore table $tablename -->\n";
        continue;   # go to next loop
    }
    # else, $ignore_file does not exist

    print "<tr>\n";
    print "<td valign='top' align='center'>$tablename</td>\n";
    print "<td>\n";

    if (! $asbuilt_exists) {
        print "None\n";
    } else {
        print "Scanned:\n";

        print $asbuilt_size . "&nbsp;bytes\n";
        print "<br/>\n";

        $asbuilt_age = ($NOW - $asbuilt_mtime);
        # print "NOW: $NOW<br/>TIME: $asbuilt_mtime<br/>\n";

        print elapsed_time_format($asbuilt_age) . "\n";
    }

    print "</td>\n";
    print "<td>\n";

    if (! $asbuilt_exists) {
        print "<a href='?ignore=$tablename'>IGNORE</a>&nbsp;&nbsp;\n";
        print "<a href='?view=$tablename'>VIEW</a>&nbsp;&nbsp;\n";
        print "<a href='?scan=$tablename'>AS-BUILT</a>&nbsp;&nbsp;\n";
    } else {
        print "<a href='?ignore=$tablename'>IGNORE</a>&nbsp;&nbsp;\n";
        print "<a href='?view=$tablename'>VIEW</a>&nbsp;&nbsp;\n";
        print "<span style='color:grey;'>AS-BUILT</span>&nbsp;&nbsp;\n";
    }

    print "</td>\n";
    print "<td>\n";

    if (! $design_exists) {
        print "None\n";
    } else {
        /*
        if (! $asbuilt_exists) {
            $bgcolor = "";
        } else {
            $bgcolor = "pink";
        }
        */
        print "Scanned:\n";

        print $design_size . "&nbsp;bytes\n";
        print "<br/>\n";

        $design_age = ($NOW - $design_mtime);
        # print "NOW: $NOW<br/>TIME: $asbuilt_mtime<br/>\n";

        print elapsed_time_format($design_age) . "\n";
    }

    print "</td>\n";
    print "<td>\n";

    if (! $asbuilt_exists) {
        print "<span style='color:grey;'>DESIGN</span>&nbsp;&nbsp;\n";
        print "<span style='color:grey;'>CHECK</span>&nbsp;&nbsp;\n";
    } else {
        # asbuilt does exist
        if (! $design_exists) {
            print "<a href='?design=$tablename' title='COPY AS-BUILT TO DESIGN'>DESIGN</a>&nbsp;&nbsp;\n";
            print "<span style='color:grey;'>CHECK</span>&nbsp;&nbsp;\n";
        } else {
            if ($asbuilt_size != $design_size) {
                print "DIFF:&nbsp;";
                print "<a href='?design=$tablename' title='COPY AS-BUILT TO DESIGN'>DESIGN</a>&nbsp;&nbsp;\n";
                print "<a href='?check=$tablename'>CHECK</a>&nbsp;&nbsp;\n";
        #   } elseif (files are different) {
        #       print "DIFF:&nbsp;";
        #       print "<a href='?design=$tablename' title='COPY AS-BUILT TO DESIGN'>DESIGN</a>&nbsp;&nbsp;\n";
        #       print "<a href='?check=$tablename'>CHECK</a>&nbsp;&nbsp;\n";
            } else {
                print "SAME:&nbsp;";
                print "<span style='color:grey;'>DESIGN</span>&nbsp;&nbsp;\n";
                print "<span style='color:grey;'>CHECK</span>&nbsp;&nbsp;\n";
            }
        }
    }

    print "</td>\n";

    // print "<pre>" . get_create_sql($tablename) . "</pre>\n";
    
    print "</tr>\n";

    // break;
}
print "<tr><td colspan=5 style='text-align:center'>Total of $count tables found</td></tr>\n";
print "</table>\n";

function get_create_sql($tablename) {
    $all_table_defs = array();
    $sql2 = 'SHOW CREATE TABLE ' . $tablename;
    $res2 = mysql_query($sql2)
        or die('Query 2 error:<br />' .mysql_error());
    while ( $table_def= mysql_fetch_row($res2) )
    {
        for ($i=1; $i<count($table_def); $i++)
        {
            array_push($all_table_defs, $table_def[$i]);
            # could do this more efficiently, don't care
        }
    }
    return implode("\n", $all_table_defs);
} // end function get_create_sql

function safe_put_contents($file, $data) {
    global $php_errormsg;

    $temp_file = $file . ".tmp";
    if (! file_put_contents($temp_file, $data)) {
        die("can't write to $temp_file: $php_errormsg");
    }
    if (file_exists($file)) {
        if (! unlink($file)) {
            die("can't unlink $file: $php_errormsg");
        }
    }
    if (! rename($temp_file, $file)) {
        die("can't rename $temp_file to $file: $php_errormsg");
    }
    print "Successfully wrote data to $file<br/>\n";
} // end function safe_put_contents

function elapsed_time_format($sec)
{
    $min = floor($sec/ 60); $sec -= $min*60;
    $hr  = floor($min/ 60); $min -= $hr *60;
    $day = floor($hr / 24); $hr  -= $day*24;
    $yr  = floor($day/365); $day -= $yr *30;
    $mon = floor($day/ 30); $day -= $mon*30; # yes, also pulling from day

    $ret = "";

    if ( $yr ) { $ret .= "$yr  yr  "; }
    if ( $mon) { $ret .= "$mon mon "; }
    if ( $day) { $ret .= "$day dy  "; }
    if ( $hr ) { $ret .= "$hr  hr  "; }
    if ( $min) { $ret .= "$min min "; }
    if ( $sec) { $ret .= "$sec sec "; }
    if (!$ret) { $ret .= "$sec sec "; }

    return $ret;
}
?>
</body>
</html>