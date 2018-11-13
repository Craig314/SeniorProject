<?php

require_once '../libs/files.php';

$basePath = '/Servers/webdocs';
$currentPath = '/';

$flags = 1 | 2 | 4;
$fileList = $files->getFileList($basePath, $currentPath, $flags);

$accumilator = 0;
echo '<pre class="xdebug-var-dump">
';
$fonts = '<font color="#cc0000">';
$fonte = '</font>';
foreach ($fileList as $kx => $vx)
{
	$quote = (is_string($vx)) ? '\'' : '';
	$length = strlen($vx);
	echo $kx . ' => ' . gettype($vx) . ' ' . $fonts . $quote . $vx
		. $quote . $fonte . ' ' . '<i>(length = ' . $length . ')</i><br>';
	$accumilator += $length;
}

echo 'Array Size: ' . count($fileList) . ' entries.<br>';
echo 'Total Size: ' . $accumilator . ' bytes.<br>';

?>