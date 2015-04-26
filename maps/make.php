<html>
<head>
<title>Maps Makefile</title>
</head>
<body>

<h2>Maps Makefile</h2>

<?
$dir = "./";
$pennsic_pdfs = array();
$details_pdfs = array();

// Open a known directory, and proceed to read its contents
if (! is_dir($dir)) {
    print("<h2>Error: $dir is not a directory.</h2>\n");
} else {
     if ($dh = opendir($dir)) {
          while (($file = readdir($dh)) !== false) {
              if ( filetype($dir . $file) == "file" ) {
                   if (preg_match("/_L[.]pdf$/i", $file)) {
                        array_push($details_pdfs, $dir . $file);
                   # } elseif (preg_match("/^pennsic[0-9]*[.]pdf$/i", $file)) {
                   } elseif (preg_match("/^pennsic.*[.]pdf$/i", $file)) {
                        array_push($pennsic_pdfs, $dir . $file);
                   } else {
                        # print("skip file $file<br/>\n");
                   }
              }
        }
        closedir($dh);
    }

    ?>
<table border='1'>
    <?
    foreach ($pennsic_pdfs as $pdf) {
         print("<tr>");
         print("<td>pennsic pdf $pdf</td>");
         $pdf_mtime = filemtime($pdf);
         print("<td>($pdf_mtime)</td>");
         $png = str_replace(".pdf", "_L.png", $pdf);
         $gif = str_replace(".pdf", ".gif", $pdf);
         $png_mtime = @filemtime($png);  if (!$png_mtime) { $png_mtime = 0; }
         $gif_mtime = @filemtime($gif);  if (!$gif_mtime) { $gif_mtime = 0; }
         print("<td>$png ($png_mtime)</td>");
         if ($png_mtime >= $pdf_mtime) {
              print("<td><span style='color:red'>OK</span></td>");
         } else {
              print("<td><span syyle='oolor:green'>FIX ME</span></td>");
              # outside of a <td> so it falls before/after table
              print("time convert -density 600x600 -quality 90 -geometry 1500x $pdf $png.tmp.png<br/>\n");
              print("mv -fv $png.tmp.png $png<br/>\n");
         }
         print("</tr>\n");
         print("<tr>");
         print("<td>&nbsp;</td>");
         print("<td>&nbsp;</td>");
         print("<td>$gif ($gif_mtime)</td>");
         if ($gif_mtime >= $pdf_mtime) {
              print("<td><span style='color:red'>OK</span></td>");
         } else {
              print("<td><span syyle='oolor:green'>FIX ME</span></td>");
              # outside of a <td> so it falls before/after table
              print("time convert -density 600x600 -quality 90 -geometry 750x $pdf $gif.tmp.gif<br/>\n");
              print("mv -fv $gif.tmp.gif $gif<br/>\n");
         }
         print("</tr>\n");
    } // next pennsic_pdfs

    foreach ($details_pdfs as $pdf) {
         print("<tr>");
         print("<td>details pdf $pdf</td>");
         $pdf_mtime = filemtime($pdf);
         print("<td>($pdf_mtime)</td>\n");
         $png = str_replace("_L.pdf", "_S.png", $pdf);
         $png_mtime = @filemtime($png);  if (!$png_mtime) { $png_mtime = 0; }
         print("<td>$png ($png_mtime)</td>");
         if ($png_mtime >= $pdf_mtime) {
              print("<td><span style='color:red'>OK</span></td>");
         } else {
              print("<td><span syyle='oolor:green'>FIX ME</span></td>");
              # outside of a <td> so it falls before/after table
              print("time convert -density 600x600 -quality 90 -geometry 750x $pdf $png.tmp.png<br/>\n");
              print("mv -fv $png.tmp.png $png<br/>\n");
         }
         print("</tr>\n");

    } // next details_pdfs

    print("</table>\n");
}
/*
            echo "filename: $file:"
                 . " filetype: " . filetype($dir . $file)
                 . " filemtime: " . filemtime($dir . $file)
                 . "<br/>\n";

*/
?>

<h3>Done.</h3>

</body>
</html>