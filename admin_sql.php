<html>
<head>
<title>PW Land Admin SQL</title>
</head>
<body>
<?php
# http://forums.devshed.com/mysql-help-4/create-table-php-strings-165919.html

require_once("include/connect.php");

$data_dir = "sql/";

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
    $ignore_size    = @filesize($ignore_file);
    $ignore_mtime   = @filemtime($ignore_file);

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
    print "<td valign='top' align='center'>" . $tablename . "</td>\n";
    print "<td>\n";

    if ( (! $asbuilt_exists) and (! $design_exists) ) {
        # no files exist, ask what to do with this table

    }


    get_create_sql($tablename);
    
    print "</td>\n";
    print "</tr>\n";

    break;
}
print "</table>\n";

function get_create_sql($tablename) {
    $sql2 = 'SHOW CREATE TABLE ' . $tablename;
    $res2 = mysql_query($sql2)
        or die('Query 2 error:<br />' .mysql_error());
    while ( $table_def= mysql_fetch_row($res2) )
    {
        for ($i=1; $i<count($table_def); $i++)
        {
            print '<pre>' .$table_def[$i]. '</pre>';
        }
    }
} // end function get_create_sql
?>
</body>
</html>