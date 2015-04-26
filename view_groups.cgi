#!/usr/local/bin/perl

# Release 1

#Displays the groups that are registered 
#with the Pennsic Land system 
#Copyright 2002 Robert P Anders

use POSIX;
use DBI;
use HTML::Template;
use CGI;

#Program Variables 

$current_year   = '2008';
$pennsic_number = '37';
$pennsic_roman  = 'XXXVII';

$webmaster_name      = 'Lord Corwyn Ravenwing (Warren Harmon)';
$webmaster_email     = 'landweb (at) pennsicwar (dot) com';
$landone_email       = 'land (at) pennsicwar (dot) com';

my $dsn          = 'DBI:mysql:pennsic_land:db.sca.org';
my $db_user_name = 'pwlcgi';
my $db_password  = 'pw14nd';

my $SQLquery;
my $grouplist;

my $template = undef;   

#set template page

my $cgi = new CGI();

#Start return page header and open database
print $cgi->header;

my $dbh = DBI->connect($dsn, $db_user_name, $db_password) || die $dbh->errstr;

	$template = HTML::Template->new( filename=>"registered_groups_template.htm", 
	                                 path => [ '/net/w/0/htdocs/userdirs/hamilton/land/' ]);
	
	$SQLquery = "SELECT Group_Name, Block_Name 
	               FROM land_groups LEFT JOIN land_blocks 
							  ON land_groups.final_block_location = land_blocks.block_id
	              WHERE land_groups.User_ID != 0 ORDER BY Group_Name"; 

	$qu = $dbh->prepare( $SQLquery );
	$qu->execute || die $dbh->errstr;

	$grouplist = "<center><table width=\"60%\">";
	
	$groups = 0;
	while( @resultArray = $qu->fetchrow_array() )
	{
		$groups++;
		$grouplist .= "<tr>\n";
		$grouplist .= "    <td align=\"left\">\n";
		$grouplist .= "        $resultArray[0]\n";
		$grouplist .= "    <\/td>\n";
		$grouplist .= "    <td>\n";
		$grouplist .= "        &nbsp\n";
		#$grouplist .= "       $resultArray[1]\n";
		$grouplist .= "    <\/td>\n";
		$grouplist .= "<\/tr>\n";
	}

	$grouplist .= "<\/table></center>";
	$grouplist .= "<h2>Total of $groups registered groups.</h2>\n";

	$filename = "view_groups_by_alpha.html";
	
	set_template_globals();

	$template->param( registered_groups => $grouplist  );
	
	$page = $template->output();

# 	open( OUT, ">$filename" ) or die $!;

	print $page;

# 	close( OUT );

# 	my $mode = 0755;	
# 	chmod( $mode, $filename );

#print "SUCCESS";

sub safe_template_param($;$;$)
{
        # set a value if it exists, but don't die if it doesn't
        
        my($template, $label, $value) = @_;

        print "<!-- debug: called safe_param($template,$label,$value) -->\n";   
        if ( $template->query(name => $label) ) {
                print "    <!-- debug: set value of parameter '$label' to '$value' -->\n";
                $template->param($label => $value);
        } else {
                print "    <!-- debug: no such parameter as '$label' in template -->\n";
        }
}

sub set_template_globals()
{
        # set variables that are global to all templates
        safe_template_param( $template, 'current_year'    , $current_year    );
        safe_template_param( $template, 'pennsic_number'  , $pennsic_number  );
        safe_template_param( $template, 'pennsic_roman'   , $pennsic_roman   );
        safe_template_param( $template, 'webmaster_name'  , $webmaster_name  );
        safe_template_param( $template, 'webmaster_email' , $webmaster_email );
        safe_template_param( $template, 'landone_email'   , $landone_email   );
}
