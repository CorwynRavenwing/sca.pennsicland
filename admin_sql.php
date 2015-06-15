<?php
# http://forums.devshed.com/mysql-help-4/create-table-php-strings-165919.html

require_once("include/connect.php");

$sql1 = 'SHOW TABLES FROM ' .$db_dbname;
$res1 = mysql_query($sql1)
    or die('Query 1 error:<br />' .mysql_error());

echo '<table border=1 cellpadding=1 cellspacing=0 width="90%">';
while ( $row = mysql_fetch_row($res1) )
{
    echo '<tr>';
    echo '<td valign="top" align="center">' .$row[0]. '</td>';
    echo '<td>';
    /*
    $sql2 = 'SHOW CREATE TABLE ' .$row[0];
    $res2 = mysql_query($sql2)
        or die('Query 2 error:<br />' .mysql_error());
    while ( $table_def= mysql_fetch_row($res2) )
    {
        for ($i=1; $i<count($table_def); $i++)
        {
            echo '<pre>' .$table_def[$i]. '</pre>';
        }
    }
    */
    echo '</td>';
    echo '</tr>';
}
echo '</table>';
?>