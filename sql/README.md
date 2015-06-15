README.md for directory sql/

This directory contains three kinds of files:

TABLENAME.ign		# a flag: if exists, ignore this table
TABLENAME_asbuilt.sql	# SQL create statement for the table as-is
TABLENAME_design.sql	# SQL create statement for the table as it should be

program ../admin_sql.php controls these tables.
