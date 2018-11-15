<?php
/*

Scroller Test

This is to test a scrolling based function


*/

function scrollerTest()
{
	$html = "
		<span>0..9</span>
		<span>A</span>
		<span>B</span>
		<span>C</span>
		<span>D</span>
		<span>E</span>
		<span>F</span>
		<span>G</span>
		<span>H</span>
		<span>I</span>
		<span>J</span>
		<span>K</span>
		<span>L</span>
		<span>M</span>
		<span>N</span>
		<span>O</span>
		<span>P</span>
		<span>Q</span>
		<span>R</span>
		<span>S</span>
		<span>T</span>
		<span>U</span>
		<span>V</span>
		<span>W</span>
		<span>X</span>
		<span>Y</span>
		<span>Z</span>
";


	echo $html;
}

function pageHeader()
{
?>
<html>
	<head>
		<title>Scrolling Test</title>
	</head>
	<body>
<?php
}

function pageFooter()
{
?>
	</body>
</html>
<?php
}



if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
	pageHeader();
	scrollerTest();
	pageFooter();
	exit(0);
}
else
{
	exit(0);
}


?>