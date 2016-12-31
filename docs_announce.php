<?
require_once("include/nav.php");

nav_start();

$crumb = array(
  "Zoning and Planning (Land)"  => "http://land.pennsicwar.org/",
);

$title = "Announcements";

nav_head($title, $crumb);

nav_menu();

// nav_leftnav();

nav_right_begin();

// these should all be done prgrammatically:
$land_one_email = "pennsic.land1@gmail.com";
$land_one_name  = "Sir Gunther KegSlayer";

$registration_start_short = "January 1, 2017";
$proxy_deadline_short     = "July 21, 2017";
$pennsic_landgrab_long    = "Friday, July 28, 2017";
$pennsic_landgrab_short   = "Friday, July 28";
$pennsic_stop_long        = "Sunday, August 13, 2017";
?>
<!-- FreeFind Begin No Index -->

<!-- FreeFind End No Index -->

<div class="clear" style="padding-top: 10px; padding-left:20px"> <!-- whole page -->

<h1>Land Announcements</h1>

Greetings from the Pennsic <?=$pennsic_number?> Land Staff!
<br />
<br />
We are working hard to continue the trend of the last few years and make this year even easier for all Land Agents.
<br />
To get started, you will need a Land login for the <a href="http://land.pennsicwar.org/">Pennsic Land website</a>.
<ul>
<li>If you already have a login from a previous year, you can use it again. If you do not remember the password, you can reset it from the link on the login page.</li>
<li>If you are a new land agent, you can create a login.</li>
</ul>
Once you have your login, you will be able to access the Land website and check that all your contact information is up-to-date.
<br />
<br />
<b>Starting <?=$registration_start_short?></b>, you will be able to register your camp group, as in previous years, and select your top 4 block choices.  <u>You must register your camp group before people can pre-reg for your group. You will receive a group registration confirmation email</u>.
<br />
<br />
<b>Throughout the pre-registration period</b>, you can log into the Land website to review the list of people who have pre-registered for your group. Youwill only be able to see people who entered your camp group's name when they pre-registered, so make sure everyone has the proper spelling and information they need to pre-register with your group.
<br />
<br />
<b>Once pre-registration ends</b>, the Pennsic Land staff will get to work assigning groups to blocks. This is a pretty big project, so we appreciate your patience while we work through the numbers to get everyone settled. Once weare done, each Land Agent will receive a Block Assignment  email which will contain your group&rsquo;s allocated square footage, a map, a camping authorization card and contact information for the needed to other groups camping on your block. Even if you all know each other, please reach out to the other land agents to come up with a plan for how to configure each group on the block based on the land allocated via pre-registration. Reaching out ahead of time will make land grab easier and faster for everyone once Pennsic opens.
<br />
<br />
<b>Land Grab for Pennsic <?=$pennsic_number?> will start</b> on <?=$pennsic_landgrab_long?> at 9:00 am, and will run until 11:00 pm that night. (The Troll Booth will stay open all night for check-in.) The Land Office will open again Saturday morning at 8:00 am and stay open until all the blocks have been settled. 
<br />
<br />
As soon as your map has been signed off by all groups on the block, processed by the Land Office, and you have received your Camping Authorization Sticker you will be provided with vehicle entry passes to the camp group. Each group will be allowed to bring 5 vehicles into the campground.
<br />
<br />
Every group on your block must have a Land Agent (or proxy - see below) present to sign the block map before any vehicle passes will be distributed to your block. 
<br />
<br />
Once ALL of the land blocks are settled, the gates will open to let all attendees enter.
<br />
<br />
As in previous years, there will be NO fires allowed on the battlefield. (You do not have to sit in your car, though. If you have gone through Troll and have your site token, you will be allowed to walk into the camp group even if your camp is not set up or you did not get a car pass.)
<br />
<br />
If a land agent is unable to be on site <?=$pennsic_landgrab_short?>, you are allowed to <b>assign a proxy</b> who can complete negotiations and sign off on your map with the other Land Agents from your block. Your proxy does not have to be from your camp, but it should be someone who knows your camp land needs, as you are giving them the ability to negotiate for your land on the block. Proxies will be able to sign off on the block map to finalize negotiations just as if they were the land agent.
<br />
<br />
Proxy requests must be emailed to <a href="mailto:<?=$land_one_email?>"><?=$land_one_email?></a> no later than <?=$proxy_deadline_short?>. To request a proxy:
<ul>
<li>Send an email from the registered Land Agent's email address - it must match the one entered into the Land website for the group.</li>
<li>The email should provide both the legal and SCA names (if applicable) for the person who is being identified as the proxy and the name of the group(s) for which they are acting as proxy.</li>
<li>The person being identified as the proxy should be copied on the email.</li>
<li>All other Land Agents for your block should be copied on the email so they know and have contact information for your proxy.</li>
<li>Each group may have one (1) proxy. Proxies can be designated to sign for multiple groups.</li>
<li>In extenuating circumstances (e.g., car trouble, illness, etc.), proxies may be granted later than <?=$proxy_deadline_short?>; please contact <a href="mailto:<?=$land_one_email?>"><?=$land_one_email?></a> as soon as possible if you need an emergency proxy.</li>
</ul>
<br />
<br />
As in years past, all camps must be left clean and all vehicles must be off site <b>no later than noon</b> on <?=$pennsic_stop_long?>. Any vehicles that are still on site, or any camps left with garbage or fires still burning will be docked a year's seniority for that block. Repeat offenses may result in additional seniority years docked.
<br />
<br />
From myself and the Land Staff of Pennsic <?=$pennsic_number?>, we look forward to helping all of you make this Pennsic the very best you can have.
<br />
<br />
<?=$land_one_name?>
<?
nav_right_end();

nav_footer_panix();
nav_footer_disclaimer();

nav_end();
?>