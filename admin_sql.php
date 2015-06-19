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

global $create_table_pattern;
$create_table_pattern = "` ";  // backtick, space

$NOW = time();

$cmd_ignore   = $_GET['ignore'  ];
$cmd_view     = $_GET['view'    ];
$cmd_scan     = $_GET['scan'    ];
$cmd_design   = $_GET['design'  ];
$cmd_check    = $_GET['check'   ];
$cmd_alter    = $_GET['alter'   ];
$cmd_override = $_GET['override'];

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
    print "<a href='?ignore=$tablename'>IGNORE</a>";
    print "&nbsp;&nbsp;";
    print "<a href='?scan=$tablename'>AS-BUILT</a>\n";
    print "</h3>\n";
}

if ($cmd_alter) {
    $tablename = $cmd_alter;
    print "<h2>ALTER $tablename</h2>\n";
    if ($override == "yes") {
        print "<h3>OVERRIDE: EXECUTING DROP COLUMN COMMANDS</h3>\n";
    }
    $alter_file   = $data_dir . $tablename . "_alter.sql";

    $alter_table_data = file_get_contents($alter_file);
    print "<table border=1>\n";
    print "<tr>\n";
    print "<td>Command</td>\n";
    print "<td>Response</td>\n";
    print "</tr>\n";

    $alter_table_rows = explode("\n", $alter_table_data);

    $count_change = 0;
    $count_drop   = 0;
    foreach ($alter_table_rows as $sql) {
        print "<tr>\n";
        print "<td>$sql</td>\n";
        print "<td>";

        if (! $sql) {
            // next loop
            continue;
        } elseif ( ($override != "yes") and (strpos($sql, "DROP COLUMN") !== false) ) {
            $count_drop++;
            $res = "skipping DROP COLUMN command";
        } else {
            $count_change++;
            $res = "";

            $query = mysql_query($sql)
                or die('Query failed: ' . mysql_error() . " at file " . __FILE__ . " line " . __LINE__);
            
            /*
            // no values returned from these sql commands
            while ($result = mysql_fetch_assoc($query)) {
                $res .= implode(" ", $result) . "\n";
            }
            */
        
            $res .= "Total of " . mysql_affected_rows() . " rows affected.";
        }

        print $res;
        print "</td>\n";
        print "</tr>\n";
    }

    print "</table>\n";

    if ($count_change) {
        // columns were changed.
        print "<h3>Automatically recreating as-built file.</h2>\n";
        $cmd_scan  = $tablename;    // fall through and do this also
        $cmd_check = $tablename;    // fall through and do this also
    } elseif ($count_drop) {
        // NO columns were changed
        // BUT there were skipped drop statements
        print "<h3>\n";
        print "<a href='?alter=$tablename&override=yes'>EXECUTE DROP COLUMN COMMANDS</a>\n";
        print "</h3>\n";
    } else {
        // no differences
        print "<h3>Table as-built and design are the same.</h2>\n";
    }
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
    $alter_file   = $data_dir . $tablename . "_alter.sql";

    $design_data = get_create_sql($tablename);
    safe_put_contents($design_file, $design_data);
    safe_unlink($alter_file);
}

if ($cmd_check) {
    $tablename = $cmd_check;
    print "<h2>CHECK $tablename</h2>\n";
    $asbuilt_file = $data_dir . $tablename . "_asbuilt.sql";
    $design_file  = $data_dir . $tablename . "_design.sql";
    $alter_file   = $data_dir . $tablename . "_alter.sql";

    print "<table border=1 cellpadding=1>\n";

    print "<tr style='bacground-color:silver; text-align:center; font-weight:bold;''>";
    print "<td>AS-BUILT</td>\n";
    print "<td>DESIGN</td>\n";
    print "</tr>\n";

    $asbuilt_data = file_get_contents($asbuilt_file);
    $design_data  = file_get_contents($design_file );

    $asbuilt_rows = trim_array( explode("\n", $asbuilt_data) );
    $design_rows  = trim_array( explode("\n", $design_data ) );

    $alter_table_root = "ALTER TABLE `$tablename`";
    $alter_table_data = "";

    $asbuilt_create_array = get_create_array($asbuilt_rows);
    $design_create_array  = get_create_array($design_rows);

    $asbuilt_data_color = "";
    $color = "pink";
    $color2 = "lightblue";
    $prev = "";
    foreach($asbuilt_rows as $r) {
        if (! $r) {
            # next loop
            continue;
        }

        $pos = strpos($r, $create_table_pattern);
        if ($pos !== false) {
            $left = trim( substr($r, 0, $pos+1) );
        #   $right = substr($r, $pos+1);
        } else {
            $left = "";
        }

        $clean_r = trim($r, ",");

        if ( in_array($r, $design_rows) ) {
            # this row is also in the other table
            $c = "";
        } else {
            if (isset($design_create_array[$left])) {
                # this row has a matching $left
                $c = "background-color:$color2;";
                # no addition to alter_table_data here
            } else {
                # no match
                $c = "background-color:$color;";
                $alter_table_data .=
                    "$alter_table_root DROP COLUMN $left;\n";
            }
        }

        $asbuilt_data_color .= "<span style='$c'>$r</span><br/>\n";
        $prev = $left;
    }

    $design_data_color  = "";
    $color = "limegreen";
    $color2 = "lightblue";
    $prev = "";
    foreach($design_rows as $r) {
        if (! $r) {
            # next loop
            continue;
        }

        $pos = strpos($r, $create_table_pattern);
        if ($pos !== false) {
            $left = trim( substr($r, 0, $pos+1) );
        #   $right = substr($r, $pos+1);
        } else {
            $left = "";
        }

        $clean_r = trim($r, ",");

        if ( in_array($r, $asbuilt_rows) ) {
            # this row is also in the other table
            $c = "";
        } else {

            if (isset($asbuilt_create_array[$left])) {
                # this row has a matching $left
                $c = "background-color:$color2;";
                $alter_table_data .=
                    "$alter_table_root MODIFY COLUMN $clean_r;\n";
            } else {
                # no match
                $c = "background-color:$color;";
                $alter_table_data .=
                    "$alter_table_root ADD COLUMN $clean_r " .
                        ($prev ? "AFTER $prev" : "FIRST") . 
                        ";\n";
            }
        }

        $design_data_color .= "<span style='$c'>$r</span><br/>\n";
        $prev = $left;
    }

    print "<tr>\n";
    print "<td>$asbuilt_data_color</td>\n";
    print "<td>$design_data_color</td>\n";
    print "</tr>\n";

    print "<tr>\n";
    print "<td colspan=2>\n";
    print "<pre>$alter_table_data</pre>\n";
    print "</td>\n";
    print "</tr>\n";

    print "</table>\n";

    safe_put_contents($alter_file, $alter_table_data);

    print "<h3>\n";
    print "<a href='?ignore=$tablename'>IGNORE</a>";
    print "&nbsp;&nbsp;";
    print "<a href='?scan=$tablename'>RE-LOAD AS-BUILT</a>\n";
    print "&nbsp;&nbsp;";
    print "<a href='?alter=$tablename'>EXECUTE ALTER-TABLE</a>\n";
    print "</h3>\n";
}

    print "<br/>\n";
    print "<br/>\n";

// ========== BEGIN LIST OF TABLES ========== //

$sql1 = 'SHOW TABLES FROM ' .$db_dbname;
$res1 = mysql_query($sql1)
    or die('Query 1 error:<br />' .mysql_error());
$count = 0;

print "<table border=1 cellpadding=1 cellspacing=0 width='100%'>\n";
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

    /*
    // clean up files that weren't chmod'ed:
    @chmod($ignore_file, 0666);
    @chmod($asbuilt_file, 0666);
    @chmod($design_file, 0666);
    $alter_file   = $data_dir . $tablename . "_alter.sql";
    @chmod($alter_file, 0666);
    */

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
        print "Scanned:&nbsp;";

        print $asbuilt_size . "&nbsp;bytes\n";
        print "<br/>\n";

        $asbuilt_age = ($NOW - $asbuilt_mtime);
        # print "NOW: $NOW<br/>TIME: $asbuilt_mtime<br/>\n";

        print elapsed_time_format($asbuilt_age) . "\n";
    }

    print "</td>\n";
    print "<td>\n";

    if (! $asbuilt_exists) {
        print "<a href='?ignore=$tablename'>IGNORE</a>&nbsp;&nbsp;";
        print "<a href='?view=$tablename'>VIEW</a>&nbsp;&nbsp;";
        print "<a href='?scan=$tablename'>AS-BUILT</a>\n";
    } else {
        print "<span style='color:grey;'>IGNORE</span>\n";
        print "<a href='?view=$tablename'>VIEW</a>&nbsp;&nbsp;";
        print "<span style='color:grey;'>AS-BUILT</span>\n";
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
        print "NONE:&nbsp;";
        print "<span style='color:grey;'>DESIGN</span>&nbsp;&nbsp;";
        print "<span style='color:grey;'>CHECK</span>\n";
    } else {
        # asbuilt does exist
        if (! $design_exists) {
            print "NONE:&nbsp;";
            print "<a href='?design=$tablename' title='COPY AS-BUILT TO DESIGN'>DESIGN</a>&nbsp;&nbsp;";
            print "<span style='color:grey;'>CHECK</span>\n";
        } else {
            if ($asbuilt_size != $design_size) {
                print "DIFF:&nbsp;";
                print "<span style='color:grey;'>DESIGN</span>&nbsp;&nbsp;";
                print "<a href='?check=$tablename'>CHECK</a>\n";
        #   } elseif (files are different) {
        #       print "DIFF:&nbsp;";
        #       print "<span style='color:grey;'>DESIGN</span>&nbsp;&nbsp;";
        #       print "<a href='?check=$tablename'>CHECK</a>\n";
            } else {
                print "SAME:&nbsp;";
                print "<span style='color:grey;'>DESIGN</span>&nbsp;&nbsp;";
                print "<span style='color:grey;'>CHECK</span>\n";
            }
        }
    }

    print "</td>\n";
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
            $line = $table_def[$i];
            $line = trim($line);

            if ( left_match($line, ") ENGINE=") ) {
                $line = ")";
            }

            array_push($all_table_defs, $line);
            # could do this more efficiently, don't care
        }
    }
    return implode("\n", $all_table_defs);
} // end function get_create_sql

function left_match($haystack, $needle) {
    $len = strlen($needle);
    
    return ( substr($haystack, 0, $len ) == $needle );
} // end function left_match

function safe_put_contents($file, $data) {
    global $php_errormsg;

    clearstatcache();

    $data = trim($data);

    # print "DEBUG: saving data '$data' to file '$file'<br/>\n";

    $temp_file = $file . ".tmp";

    safe_unlink($temp_file);
    if ( file_put_contents($temp_file, $data) === false ) {
        die("can't write to $temp_file: $php_errormsg");
    }
    safe_unlink($file);
    if (! rename($temp_file, $file)) {
        die("can't rename $temp_file to $file: $php_errormsg");
    }
    if (! chmod($file, 0666)) {
        print("Warning: can't chmod($file, 0666)<br/>");
    }

    print "Successfully wrote data to $file<br/>\n";
} // end function safe_put_contents

function safe_unlink($file) {
    global $php_errormsg;

    if (file_exists($file)) {
        # print "DEBUG: file $file exists: deleting<br/>\n";
        if (! unlink($file)) {
            die("can't unlink $file: $php_errormsg");
        }
    }
} // end function safe_unlink

function elapsed_time_format($sec) {
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

function get_create_array($rows) {
    global $create_table_pattern;

    $create_array = array();

    foreach ($rows as $r) {
        // print "R: $r<br/>";
        $pos = strpos($r, $create_table_pattern);
        if ($pos !== false) {
            $left  = trim( substr($r, 0, $pos+1) );
            $right = trim( substr($r, $pos+1) );
            // print "LEFT: [$left]<br/>";
            // print "RIGHT: [$right]<br/>";
            $create_array[$left] = $right;
        }
    }

    return $create_array;
} // end function get_create_array

function trim_array( $array ) {
    foreach ($a as $i) {
        $i = trim($i);
    }
    return $a;
} // end function trim_array
?>
</body>
</html>