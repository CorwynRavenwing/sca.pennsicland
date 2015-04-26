<?
$template_body = "";

function find_top_directory() {
  global $top_directory_answer;

  if ($top_directory_answer) {
    $retVal = $top_directory_answer;
  } else {
    $file = $_SERVER['SCRIPT_NAME'];               #print("file: $file<br/>\n");
    $dir = dirname($file);                         #print("dir1: $dir<br/>\n");
    $dir = str_replace("/pennsicland", "", $dir);  #print("dir2: $dir<br/>\n");
    $dir = trim($dir, "/");                        #print("dir3: $dir<br/>\n");
    $array = split("/", $dir);                     #print("count1: " . count($array) . "<br/>\n");
    $dummy = array_pop($array);                    #print("dummy: $dummy<br/>\n");
                                                   #print("count2: " . count($array) . "<br/>\n");
    $retVal = str_repeat("../", count($array) );   #print("retval1: $retVal<br/>\n");

    if (!$retVal) { $retVal = "."; }               #print("retval2: $retVal<br/>\n");
    // print "\n<!-- find_top_directory: root '$retVal' -->\n";

    $top_directory_answer = $retVal;
  } // endif

  return $retVal;
} // end function find_top_directory

function template_load($file, $use_template_comments = 1) {
    global $template_body;
    $root = find_top_directory();

    $template_begin = "";
    $template_end   = "";

    if ($use_template_comments) {
        $template_begin = "<!-- TEMPLATE $file BEGIN -->\n";
        $template_end   = "\n<!-- TEMPLATE $file END -->\n";
    }

    $file_contents  = file_get_contents("$root/template/$file");
    # $file_contents  = str_replace("\\n", "\n", $file_contents);    // replace literal \n with carriage return [Corwyn P42]
    # ^^^ turns out not to be necessary: I was just passing the result through mysql_real_replace_string() when I shouldn't have [Corwyn P42]
    $template_body =
        $template_begin
      . $file_contents
      . $template_end;
} // end function template_load

function template_param($param, $value) {
  global $template_body;
  $template_body = str_ireplace ( "<!-- TMPL_VAR NAME=$param -->", $value, $template_body );
  $template_body = str_ireplace ( "<TMPL_VAR NAME=$param>",        $value, $template_body );
} // end function template_param

function template_output() {
  global $template_body;
  return $template_body;
} // end function template_output

function template_save() {
  global $template_body;
  $body = $template_body;
  $template_body = "";
  return $body;
} // end function template_save

function template_restore($body) {
  global $template_body;
  $template_body = $body;
} // end function template_restore

?>