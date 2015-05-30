<?
require_once("include/nav.php");

nav_start_admin();

$crumb = array(
	"Zoning and Planning (Land)"	=> "http://land.pennsicwar.org/",
	"Land Admin"			=> "admin.php",
);

nav_head( "User Logins", $crumb );

nav_admin_menu();	// special Admin menu nav

nav_admin_leftnav();	// special Admin left nav

nav_right_begin();

if (! $r_admin) {
	print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
	# template_load("admin_main_page.htm");
	# template_param("admin_message",				$ADM_MSG );
	# print template_output();
	
	# print("<h3>_SERVER:<pre>"); print_r($_SERVER); print("</pre></h3>\n");
	?>
<style type="text/css">

.login_success {
	color:green;
	font-weight:bold;
}

.login_failure {
	color:red;
	font-weight:bold;
}

.login_other {
	color:blue;
	font-weight:bold;
}

</style>

	<?
	$input_id = @$_GET['id'];
	$limit    = @$_GET['limit'];
	?>
	
<table border='1'>
	<tr style='font-weight:bold; text-align:center'>
		<td colspan='10'>
			Recent Logins and Login Attempts
	<? if ($input_id) { ?>
			for id <?=$input_id?> (<a href="?limit=<?=$limit?>">Show all</a>)
	<? } // endif ?>
		</td>
	</tr>
	<tr bgcolor='silver'>
		<td>user_name</td>
		<td>access_type</td>
		<td>login_time</td>
		<td>host</td>
		<td>referrer</td>
		<td>user agent</td>
		<td>method</td>
	</tr>
	<?
	if (!$limit) { $limit = 10; }
	
	$query = query_login_history($input_id, $limit);
	$n=0;
	while ( $result = mysql_fetch_assoc($query) ) {
		$login_id	= $result['login_id'];
		$login_userid	= $result['user_id'];
		$access_type	= $result['access_type'];
		$login_time	= $result['login_time'];
		$http_refferer	= $result['http_refferer'];
		$http_user_agent= $result['http_user_agent'];
		$request_method	= $result['request_method'];
		$remote_addr	= $result['remote_addr'];
		$remote_host	= $result['remote_host'];
		
		$user_rec	= user_record($login_userid);
		$login_username	= $user_rec['user_name'];
		
		$short_refer = preg_replace("/^.*\//", "",     $http_refferer);
		$short_refer = preg_replace("/\?.*/",  "?...", $short_refer);
		$short_agent = preg_replace("/ .*/",   "",     $http_user_agent);
		
		switch ($access_type) {
			case "successful_login":
				$login_class = "login_success";
				break;
			
			case "password_invalid":
				$login_class = "login_failure";
				break;
			
			default:
				$login_class = "login_other";
				break;
		} // end switch access_type
		
		$n++;
		?>
	<tr>
		<!-- <?=$login_id?> -->
		<td><a href="?id=<?=$login_userid?>&amp;limit=<?=$limit?>"><?=$login_username?></a></td>
		<td class="<?=$login_class?>"><nobr>				<?=$access_type?></nobr></td>
		<td nowrap='nowrap'>						<?=$login_time?>	</td>
		<td><a title="<?=$remote_host?>">				<?=$remote_addr?></a>	</td>
		<td><a title="<?=$http_refferer?>">				<?=$short_refer?></a>	</td>
		<td><a title="<?=$http_user_agent?>">				<?=$short_agent?></a>	</td>
		<td>								<?=$request_method?>	</td>
	</tr>
		<?
	} // end while
	?>
	<tr style='font-weight:bold; text-align:center'>
		<td colspan='10'>
			<a href="?id=<?=$input_id?>&amp;limit=<?=round($limit/2,0)?>">(-)</a>
			Total of <?=$n?> records shown.
			<a href="?id=<?=$input_id?>&amp;limit=<?=round($limit*2,0)?>">(+)</a>
		</td>
	</tr>
</table>

<hr/>

<table border='1'>
	<tr style='font-weight:bold; text-align:center'>
		<td colspan='10'>
			Currently Active User Sessions
		</td>
	</tr>
	<tr bgcolor='silver'>
		<td>user_id</td>
		<td>user_name</td>
		<td>SCA name</td>
	</tr>
	<?
	
	$current_userids = current_user_sessions_userids();
	
	$files = current_user_sessions_files();
	
	foreach ($current_userids as $uid) {
		$user_data = user_record($uid);
		?>
	<tr>
		<td><?=$uid?></td>
		<td><?=$user_data['user_name']?></td>
		<td><?=$user_data['alias']?></td>
	</tr>
		<?
	} // next uid
	?>
</table>
	<?
} // endif admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>
