package CooperPreRegistration;

sub load_preregistrations_by_group_name { /* moved to cooper.php */ }

sub fix_cooper_data() { /* moved to cooper.php */ } # end sub fix_cooper_data

sub find_cooper_group_by_penn($) { /* moved to cooper.php */ }

sub create_cooper_record($;$;$;$;$) { /* moved to cooper.php */ }

sub touch_cooper_record($) { /* moved to cooper.php */ }

sub reject_untouched_cooper_records($) { /* moved to cooper.php */ }

sub move_cooper_record($;$) { /* moved to cooper.php */ }

sub load_preregistrations_by_group_id
{
	my $self = shift;
	
	my $arg1 = shift;
	my $arg2 = shift;
	
	setup_GS_url();
	
	my $agent   = new LWP::UserAgent;
	my $request = POST( $GS_URL,
		Content_Type => 'form-data',
		Content => [
			username  => $userid,
			password  => $password,
			function  => 'registered_persons_by_group_id',
			arg1      => $arg1
		]
	);

	my $response = $agent->request($request);
	die "request failed" unless $response->is_success;
	
	if( $response->content eq 'log-in failed' )
	{
		die "remote system unavailable";
	}
	elsif( $response->content eq 'FAIL' )
	{
		die "remote system FAIL";
	}
	else
	{
		my @records = split( "\n", $record );
		
		foreach ( @record )
		{
			print $_ . "<br>";
		}
		die;
	}
}

sub load_all_matched_preregistrations
{
	my $self = shift;
	
	my $sqlQuery = "SELECT cooper_id
                      FROM land_correlation
                     WHERE group_id != 0";

	my $query = $main::dbh->prepare( $sqlQuery );
	
	$query->execute( $_[0] ) || die $main::dbh->errstr;
	
	my @matches;
	my $a;
	
	while( $a = $query->fetchrow_array() )
	{
		print "$a<br>";
		push( @matches, $a );
	}
	
	die $self->dump;
	
	$self->retrieve_multiple_records_by_field("id", @matches);
}

sub load_preregistrations_with_no_match
{
	my $self = shift;
	
	my $sqlQuery = "SELECT cooper_id
					  FROM land_correlation
					 WHERE group_id = 0";
	
	my $query = $main::dbh->prepare( $sqlQuery );
	
	$query->execute( ) || die $dbh->errstr;
	
	my @matches;
	my $a;
	
	while( $a = $query->fetchrow_array() )
	{
		push( @matches, $a );
	}
	
	$self->retrieve_multiple_records_by_field("id", @matches);
}

sub load_preregistrations_with_fuzzy_match
{
	my $self = shift;
	
	my $sqlQuery = "SELECT cooper_id
					  FROM land_correlation
					 WHERE fuzzy_match = \'1\'";
	
	my $query = $main::dbh->prepare( $sqlQuery );
	
	$query->execute( ) || die $main::dbh->errstr;
	
	my @matches;
	my $a;
	
	while( $a = $query->fetchrow_array() )
	{
		push( @matches, $a );
	}
	
	$self->retrieve_multiple_records_by_field("id", @matches);
}

sub get_preregistrations_count
{
	my $self = shift;
	
	my $sqlQuery = "SELECT COUNT(*)
					  FROM land_correlation
					 WHERE group_id = ?";
	
	my $query = $main::dbh->prepare( $sqlQuery );
	
	$query->execute( $_[0] ) || die $main::dbh->errstr;
	
	return $query->fetchrow_array();
}

sub get_group_id
{
	my $self = shift;
	
	my $sqlQuery = "SELECT group_id
					  FROM land_correlation
					 WHERE cooper_id = ?";
	
	my $query = $main::dbh->prepare( $sqlQuery );
	
	$query->execute( $self->{fields}{id}{value} ) || die $main::dbh->errstr;
	
	return $query->fetchrow_array();
}

sub set_group_id
{
	my $self = shift;
	my $group_id = shift;
	
	my $sqlQuery = "UPDATE land_correlation SET group_id = ?
					 WHERE cooper_id = ?";
	
	my $query = $main::dbh->prepare( $sqlQuery );
	
	$query->execute( $group_id, $self->{fields}{id}{value} ) || die $main::dbh->errstr;
	
	return 1;
}

sub update_preregistration_groupname
{
	my $self = shift;
	my $group_id   = shift;
	my $groupname  = shift;
	
	my $sqlQuery = "UPDATE cooper_preregistration
					   SET group_name = ?
					 WHERE id = ?";
	
	my $query = $main::dbh->prepare( $sqlQuery );
	
	$query->execute( $group_id, $groupname ) || die $dbh->errstr;
	
	return 1;
}

sub add_group_to_list
{
	my $self = shift;
	
	my $groupname = shift;
	my $group_id  = shift;
	
	my $sqlQuery = "SELECT group_name
					  FROM land_groups_2
					 WHERE group_name = ?";
	
	my $query = $main::dbh->prepare( $sqlQuery );
	
	$query->execute( $groupname ) || die $dbh->errstr;
	
	my $data;
	
	if( $data = $query->fetchrow_array() )
	{
		$self->{'error_type'}   = "not unique";
		$self->{'error_string'} = "Group name already in table";
		return( undef );
	}
	else
	{
		$sqlQuery = "INSERT INTO land_groups_2 (group_id, group_name, count_change) VALUES(?,?)";
		my $query = $main::dbh->prepare( $sqlQuery );
		$query->execute( $groupname, $group_id ) || die $dbh->errstr;
		
		return 1;
	}
}

sub remove_group_from_list
{
	my $self = shift;
	my $group_id  = shift;
	
	my $sqlQuery = "DELETE
					  FROM land_groups_2
					 WHERE group_id  = ?";
	
	my $query = $main::dbh->prepare( $sqlQuery );
	
	$query->execute( $group_id) || die $dbh->errstr;
	
	return 1;
}

sub rename_group
{
	my $self = shift;
	
	my $group_id  = shift;
	my $groupname = shift;
	
	my $sqlQuery = "UPDATE land_groups_2
					   SET group_name = ?
					 WHERE group_id  = ?";
	
	my $query = $main::dbh->prepare( $sqlQuery );
	
	$query->execute( $group_id, $groupname ) || die $dbh->errstr;
	
	return 1;
}

sub match_prereg_to_groups { /* moved to cooper.php */ }

# old version: returns list of (id, count).  Runs too long and crashes.
sub get_preregistration_count_list {}

sub get_preregistration_count_list_NEW { /* moved to cooper.php */ }

sub cooper_create_group() { /* moved to cooper.php */ }

1;