<html>
<head>
<title>Library Thing Test</title>
</head>
<body>
  start 10:44
<?
    $text = "a paragraph goes here";

    $words_array = split(" ", $text);

    $words_count = array();

    foreach($words_array as $word) {
        $len = strlen($word);
        print("word $word, length $len<br/>\n");

        $words_count[ $len ]++;
    }

    print("results:<br/>\n");

    foreach($words_count as $len => $ct) {
        print("$ct words with $len letter");
        print( (($len == 1) ? "" : "s") );
        print("<br/>\n");
    }
?>
end 10:52, total 8 minutes
</body>
</html>