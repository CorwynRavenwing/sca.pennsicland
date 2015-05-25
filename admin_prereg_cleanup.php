<?
require_once("include/nav.php");
require_once("include/cooper.php");

nav_start_admin();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
  "Land Admin"      => "admin.php",
);

nav_head( "Fix Orphans", $crumb );

nav_admin_menu();  // special Admin menu nav

nav_admin_leftnav();  // special Admin left nav

nav_right_begin();

if (! $r_admin) {
  print "<h2>Please log on as Pennsic Land staff first.</h2>\n";
} else {
  # no template

  if ($w_admin) {
    # only write-admins can make these changes.

    $create_group = @$_GET['create_group'];
    if ( $create_group ) {

      $new_groupid = create_group($create_group);

      $on_site_representative  = "Admin Account: $create_group";
      $compression_percentage  = 0;
      $other_group_information = "Group created by " . basename( $_SERVER['PHP_SELF'] ) ;
      set_group_data($new_groupid, $on_site_representative, $compression_percentage, $other_group_information );

      print("<h2>Creating group $create_group (id $new_groupid)</h2>");
    } // endif create_group

    $create_agent = @$_GET['create_agent'];
    $link_group   = @$_GET['link_group'];
    if ( $create_agent or $link_group ) {

      $requested_user_name = "admin_" . create_random(10);
      $requested_password = create_random(8);      // and store it NOWHERE

      $create_agent = "Admin Account: $create_agent";
      $legal_name = $create_agent;
      $alias = $create_agent;

      $street_1 = "ADMIN";
      $street_2 = "ADMIN";
      $city = "ADMIN";
      $state = "ADMIN";
      $postal_code = "ADMIN";
      $country = "ADMIN";
      $phone = "ADMIN";
      $extension = "";
      $email_address = "landweb@pennsicwar.org";
      $password_hint = "create_random(8)";
      $password_answer = create_random(8);

      create_user($requested_user_name,$requested_password,$legal_name,$alias,
        $street_1,$street_2,$city,$state,$postal_code,$country,
        $phone,$extension,$email_address,$password_hint,$password_answer
        ) or die ("error creating new user: " . mysql_error() );

      $new_user_id = user_id_by_username( $requested_user_name );

      $new_groupid = group_id_by_name($link_group);

      register_group($new_groupid, $new_user_id)
        or print("ERROR: failed to register group $link_group ($new_groupid) to userid $new_user_id");

      print("<h2>Creating user $create_agent (id $new_user_id): linked to group $link_group (id $new_groupid)</h2>");
    } // endif $create_agent or $link_group

  } // endif w_admin

  $sql = "SELECT
        c.group_name,
        c.pre_registration_count
      FROM cooper_prereg_count AS c
        LEFT JOIN land_groups AS g USING(group_name)
      WHERE g.group_name IS NULL
  ";
  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);

  $total = 0;
  if (mysql_num_rows($query)) {
    ?>
<h2>
  <font color='red'>
    Groups found on the Coopers site that don't exist on this site:
  </font>
</h2>
<table border='1' bordercolor='red'>
  <tr bgcolor='silver'>
    <td>Group Name</td>
    <td>People</td>
    <td>CREATE</td>
  </tr>
    <?
    $count = 0;
    while ($result = mysql_fetch_assoc($query)) {
      $group_name = $result['group_name'];
      $group_count = $result['pre_registration_count'];
      $count++;
      $total += $group_count;
      ?>
  <tr bgcolor='#FFDDDD'>
    <td align='left'><b><?=$group_name?></b></td>
    <td align='right'>
      <a href="admin_prereg.php?group=<?=$group_name?>">
        <b><?=$group_count?></b>
      </a>
    </td>
    <td align='center'><a href='?create_group=<?=$group_name?>'>create</a></td>
  </tr>
      <?
    } // next result

    if ($count) {
      ?>
  <tr>
    <td colspan="4" align="center">Total of <b><?=$total?></b> people in <b><?=$count?></b> groups.</td>
  </tr>
      <?
    } else {
      ?>
  <tr>
    <td colspan="4" align="center">(none)</td>
  </tr>
      <?
    } // endif count
    ?>
</table>
    <?
  } else {
    print "<h2><font color='green'>All groups on the Coopers site also exist on this site.</font></h2>\n";
  } // endif num_rows

  $sql = "SELECT
        group_id,
        group_name,
        sum(pre_registration_count) AS campers,
        on_site_representative,
        pre_registration_count > 0 AS has_campers,
        user_id != 0 AS has_agent
      FROM land_groups
      GROUP BY group_name
      HAVING has_campers AND ! has_agent
  ";

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);

  $total = 0;
  if (mysql_num_rows($query)) {
    ?>
<h2><font color='blue'>Groups which have preregistered campers BUT have no land agent:</font></h2>

<table border='1' bordercolor='blue'>
  <tr bgcolor='silver'>
    <td>Group Name</td>
    <td>People</td>
    <td>On-site Rep Name</td>
    <td>
      <a title="Register this group with an administrative land agent, making their land grab OUR RESPONSIBILITY">
        FAKE&nbsp;REGISTER
      </a>
    </td>
  </tr>
    <?
    foreach ( admin_group_type_list() as $this_type => $type_description ) {
      ?>
  <tr bgcolor="#858599"> <!-- dark blue -->
    <td colspan="4" align="center" style="font-weight:bold">
      <a title="<?=$type_description?>">
        Type <?=$this_type?>
      </a>
    </td>
  </tr>
      <?
      $count = 0;
      while ($result = mysql_fetch_assoc($query)) {
        $group_id = $result['group_id'];
        $group_name = $result['group_name'];
        $group_count = $result['campers'];
        $rep_name = $result['on_site_representative'];
        $group_type = admin_group_type($group_name);
        if ($group_type == $this_type) {
          $count++;
          $total += $group_count;
          ?>
  <tr bgcolor='#DDDDFF'> <!-- light blue -->
    <td align='left'>
      <a href="admin_groups.php?id=<?=$group_id?>">
        <b><?=$group_name?></b>
      </a>
    </td>
    <td align='right'>
      <a href="admin_prereg.php?id=<?=$group_id?>">
        <b><?=$group_count?></b>
      </a>
    </td>
    <td align='left'><b><?=$rep_name?></b></td>
    <td align='center'>
      <a href='?create_agent=<?=$rep_name?>&link_group=<?=$group_name?>'>fake&nbsp;register</a>
    </td>
  </tr>
          <?
        } // endif type
      } // next result

      if ($count) {
        ?>
  <tr>
    <td colspan="4" align="center">Total of <b><?=$total?></b> people in <b><?=$count?></b> groups.</td>
  </tr>
        <?
      } else {
        ?>
  <tr>
    <td colspan="4" align="center">(none)</td>
  </tr>
        <?
      } // endif count

      mysql_data_seek( $query, 0 );
    } // next type
    ?>
</table>
    <?
  } else {
    print "<h2><font color='green'>All groups with preregistered campers also have a land agent.</font></h2>\n";
  }

  $sql = "SELECT
        group_id,
        group_name,
        sum(pre_registration_count) AS campers,
        on_site_representative,
        pre_registration_count > 0 AS has_campers,
        g.user_id != 0 AS has_agent,
        (u.alias LIKE 'Admin Account:%') AS admin_account
      FROM land_groups AS g
        LEFT JOIN user_information AS u USING(user_id)
      GROUP BY group_name
      HAVING has_campers AND has_agent AND admin_account
  ";
  # was    (on_site_representative LIKE 'Admin Account:%') AS admin_account

  $query = mysql_query($sql)
    or die('Query failed: ' . mysql_error() . "<br/>SQL=$sql<br/>at file " . __FILE__ . " line " . __LINE__);

  $total = 0;
  if (mysql_num_rows($query)) {
    ?>
<h2><font color='#ffa500'><!-- dark orange -->Groups which have preregistered campers BUT we are acting as their land agent:</font></h2>

<table border='1' bordercolor='#ffa500'><!-- dark orange -->
  <tr bgcolor='silver'>
    <td>Group Name</td>
    <td>People</td>
    <td>On-site Rep Name</td>
    <td>Group Type</td>
  </tr>
    <?
    foreach ( admin_group_type_list() as $this_type => $type_description ) {
      ?>
  <tr bgcolor="#b37300"> <!-- dark orange -->
    <td colspan="4" align="center" style="font-weight:bold">
      <a title="<?=$type_description?>">
        Type <?=$this_type?>
      </a>
    </td>
  </tr>
      <?
      $count = 0;
      while ($result = mysql_fetch_assoc($query)) {
        $group_id = $result['group_id'];
        $group_name = $result['group_name'];
        $group_count = $result['campers'];
        $rep_name = $result['on_site_representative'];
        $group_type = admin_group_type($group_name);
        if ($group_type == $this_type) {
          $count++;
          $total += $group_count;
          ?>
  <tr bgcolor='#ffd280'><!-- light orange -->
    <td align='left'>
      <a href="admin_groups.php?id=<?=$group_id?>">
        <b><?=$group_name?></b>
      </a>
    </td>
    <td align='right'>
      <a href="admin_prereg.php?id=<?=$group_id?>">
        <b><?=$group_count?></b>
      </a>
    </td>
    <td align='left'><b><?=$rep_name?></b></td>
    <td align='center'><b><?=$group_type?></b></td>
  </tr>
          <?
        } // endif type
      } // next result

      if ($count) {
        ?>
  <tr>
    <td colspan="4" align="center">Total of <b><?=$total?></b> people in <b><?=$count?></b> groups.</td>
  </tr>
        <?
      } else {
        ?>
  <tr>
    <td colspan="4" align="center">(none)</td>
  </tr>
        <?
      } // endif count

      mysql_data_seek( $query, 0 );
    } // next type
    ?>
</table>
    <?
  } else {
    print "<h2><font color='green'>No groups with preregistered campers have Land Staff acting as land agent.</font></h2>\n";
  }
  ?>
<br/>
  <?
#  @ob_flush();
#  fix_cooper_data();
  ?>
<h4>Done!</h4>
  <?
} // endif r_admin

nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();

function admin_group_type_list() {
  return array(
    "Admin"    => "Groups used for administrative purposes",
    "FICTIONAL"  => "Groups that collectively mean 'I am in the wrong place'",
    "ORPHAN"  => "Normal groups missing their land agents",
  );
} // end function admin_group_type_list

function admin_group_type($group) {
  if ($group == "Individual Camping")    { return "Admin"; }
  if ($group == "MERCHANT")      { return "Admin"; }
  if ($group == "RV CAMPING")      { return "Admin"; }

  if ($group == "Landgroup not Listed")    { return "FICTIONAL"; }
  if ($group == "None Selected")      { return "FICTIONAL"; }
  if ($group == "Not filled in")      { return "FICTIONAL"; }
  if ($group == "Did not contact land agent")  { return "FICTIONAL"; }

  /* otherwise */
  return "ORPHAN";
} // end admin_group_type
?>