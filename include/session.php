<?
# session.php: functions regarding the _SESSION object

$session_path = $_SERVER['DOCUMENT_ROOT'] . "/phpsession";

if (! file_exists($session_path) ) {
  print "<h4>Directory $session_path not found</h4>\n";
} else {
  # mkdir( $session_path, 0777 );
  session_save_path( $session_path );
  ini_set("session.save_handler", "files");

  $sessionExpireMinutes = 8*60;        // eight hours in minutes
  $sessionExpireSeconds = $sessionExpireMinutes*60;  // ... in seconds

  ini_set('session.gc_maxlifetime', $sessionExpireSeconds);
  session_set_cookie_params($sessionExpireSeconds);

  if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), $_COOKIE[session_name()], time() + $sessionExpireSeconds, "/");
  }

  session_cache_expire($sessionExpireMinutes);

  session_start();
} // endif session_path

function session_logout() {
  session_destroy();
  unset($_SESSION);
} // end function session_logout

function current_user_sessions_userids() {
  $files = current_user_sessions_files();

  $retVal = array();

  foreach ($files as $f) {
    $x = file_get_contents($f);

    $x = ";$x";
    $x = preg_replace("/.*;user_id\|N;.*/","",$x);
    $x = preg_replace("/.*;user_id\|i:0;.*/","",$x);
    $x = preg_replace("/.*;user_id\|s:[0-9]*:\"([0-9]*)\";.*/","$1",$x);

    if ($x) {
      @$retVal[ $x ]++;
    }
  } // next files

  return array_keys($retVal);
} // end function current_user_sessions_userids

function current_user_sessions_files() {
  global $session_path;
  $query = "sess_";

  $retVal = array();

  if ($dh = opendir($session_path)) {
    while (($file = readdir($dh)) !== false) {
      $filename = $session_path . "/" . $file;
      if ( filetype($filename) == "file" ) {
        if ( substr($string, 0, strlen($query)) === $query ) ) {
          // echo "<!-- session filename: $file -->\n";
          array_push($retVal, $filename);
        }
      }
    }
    closedir($dh);
  } // endif dh

  return $retVal;
} // end function current_user_sessions_files

function current_user_sessions() {
  $userids = current_user_sessions_userids();

  return count($userids);
} // end function current_user_sessions
?>