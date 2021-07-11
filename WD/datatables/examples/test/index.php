<?php

require 'vendor/autoload.php';

$api = new CloudTables\Api('yzlxp8cu34', 'pu0Ohkw7dgITx4sRoycjg5pL', [
	clientId => 'Unique client id',
	clientName => 'Name'
]);

?><!doctype html>
<html>
	<head>
		<title>Test</title>
	</head>
	<body>
		<p>before</p>

		<?=$ctApi->scriptTag('84d32088-99ba-11ea-b835-0f59341fb54a', 'd')?>

		<p>After</p>
	</body>
</html>
