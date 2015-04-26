<html>
<head>
<title>Numbers</title>
</head>
<body>
<?
require_once("../include/connect.php");

$check_query = "select max(n) AS N from numbers";
$result = mysql_query( $check_query )
    or print("error doing query: " . mysql_error() );
$row = mysql_fetch_assoc($result);
$max_n = $row['N'];
print("before: N = $max_n<br/>\n");
flush();

for($i = 1; $i<100000; $i++) {
    $query = "insert into numbers (divisors) values (0)";
    mysql_query( $query )
        or print("error doing query: " . mysql_error() );
    if (!($i % 100)) {
        print(": ");
        flush();
    }
}

$check_query = "select max(n) AS N from numbers";
$result = mysql_query( $check_query )
    or print("error doing query: " . mysql_error() );
$row = mysql_fetch_assoc($result);
$max_n = $row['N'];
print("after: N = $max_n<br/>\n");
flush();

?>

  body goes here

<?
?>
</body>
</html>