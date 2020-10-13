<?php

$serverName = getenv('DATABASE_HOST');

$serverName = getenv('DATABASE_HOST');
$username = getenv('DATABASE_USER');
$password = getenv('DATABASE_PASSWORD');
$databaseName = getenv('DATABASE_NAME');

$filename = "backup-" . date("d-m-Y") . ".sql.gz";
$mime = "application/x-gzip";

header( "Content-Type: " . $mime );
header( 'Content-Disposition: attachment; filename="' . $filename . '"' );

$cmd = "mysqldump -P 3310 -h $serverName -u $username --password=$password -p $databaseName | gzip --best";   

passthru( $cmd );

exit(0);
