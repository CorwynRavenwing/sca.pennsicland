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

$cmd_ignore = $_GET['ignore'];
$cmd_scan   = $_GET['scan'];
$cmd_design = $_GET['design'];
$cmd_check  = $_GET['check'];

if ($cmd_ignore) {
    $tablename = $cmd_ignore;
    print "<h2>IGNORE $tablename</h2>\n";
    $ignore_file  = $data_dir . $tablename . ".ign";
    safe_put_contents($ignore_file, "IGNORE TABLE");
}

if ($cmd_scan) {
    $tablename = $cmd_scan;
    print "<h2>SCAN $tablename</h2>\n";
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





$sql1 = 'SHOW TABLES FROM ' .$db_dbname;
$res1 = mysql_query($sql1)
    or die('Query 1 error:<br />' .mysql_error());

print "<table border=1 cellpadding=1 cellspacing=0 width='90%'>\n";
while ( $row = mysql_fetch_row($res1) )
{
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

    print "<tr>\n";
    print "<td valign='top' align='center'>$tablename</td>\n";
    print "<td>\n";

    if ( (! $asbuilt_exists) and (! $design_exists) ) {
        # no files exist, ask what to do with this table

        print "No data file:\n";
        print "<a href='?ignore=$tablename'>IGNORE</a>\n";
        print "<a href='?scan=$tablename'>SCAN</a>\n";

        continue;
    }

    print "<pre>" . get_create_sql($tablename) . "</pre>\n";
    
    print "</td>\n";
    print "</tr>\n";

    break;
}
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
    print "Successfully wrote data to $file";
} // end function safe_put_contents
?>
</body>
</html>