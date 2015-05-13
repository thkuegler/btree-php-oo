<?php
//includes
include './BTree.php';

session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title></title>
		<link type="text/css" href="./Jit/css/base.css" rel="stylesheet" />
		<link type="text/css" href="./Jit/css/Spacetree.css" rel="stylesheet" />
		<!--[if IE]><script language="javascript" type="text/javascript" src="../../Extras/excanvas.js"></script><![endif]-->
		<script type="text/javascript" src="./Jit/jit.js"></script>
		<script type="text/javascript" src="./Jit/js/base.js"></script>
	</head>
	<body onload="init()">
		<?php
		define('MIN_NUM_CHILDREN', 3);

		if (!empty($_SESSION['test'])) {
			$test = $_SESSION['test'];
		} else {
			$test = new BTree();

			// $test->insertData(616, new BTreeData());
			// $test->insertData(781, new BTreeData());
			// $test->insertData(800, new BTreeData());
			// $test->insertData(835, new BTreeData());
			// $test->insertData(847, new BTreeData());
			// $test->insertData(851, new BTreeData());
			// $test->insertData(900, new BTreeData());
			// $test->insertData(950, new BTreeData());
			// $test->insertData(1000, new BTreeData());
			// $test->insertData(1050, new BTreeData());
			// $test->insertData(1100, new BTreeData());
			// $test->deleteData(1100);
			// $test->deleteData(1050);
			// $test->deleteData(1000);
			// $test->deleteData(835);
			// $test->deleteData(950);
			// $test->deleteData(900);
			// $test->deleteData(800);
			// $test->deleteData(851);
			// $test->deleteData(835);
		}
		?>

		<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
			<input type="text" name="in" autofocus />
			<br>
			<input type="submit" name="sub" value="OK" />
			<input type="submit" name="del" value="Löschen" />
		</form>

		<div id="container">

			<div id="left-container">

				<div class="text">
					<h4> Tree Animation </h4>

					A static JSON Tree structure is used as input for this animation.
					<br />
					<br />
					<b>Click</b> on a node to select it.
					<br />
					<br />
					You can <b>select the tree orientation</b> by changing the select box in the right column.
					<br />
					<br />
					You can <b>change the selection mode</b> from <em>Normal</em> selection (i.e. center the selected node) to <em>Set as Root</em>.
					<br />
					<br />
					<b>Drag and Drop the canvas</b> to do some panning.
					<br />
					<br />
					Leaves color depend on the number of children they actually have.

				</div>

				<div id="id-list"></div>

				<div style="text-align:center;">
					<a href="example1.js">See the Example Code</a>
				</div>
			</div>

			<div id="center-container">
				<div id="infovis"></div>
			</div>

			<div id="right-container">

				<h4>Tree Orientation</h4>
				<table>
					<tr>
						<td><label for="r-left">Left </label></td>
						<td>
						<input type="radio" id="r-left" name="orientation" checked="checked" value="left" />
						</td>
					</tr>
					<tr>
						<td><label for="r-top">Top </label></td>
						<td>
						<input type="radio" id="r-top" name="orientation" value="top" />
						</td>
					</tr>
					<tr>
						<td><label for="r-bottom">Bottom </label></td>
						<td>
						<input type="radio" id="r-bottom" name="orientation" value="bottom" />
						</td>
					</tr>
					<tr>
						<td><label for="r-right">Right </label></td>
						<td>
						<input type="radio" id="r-right" name="orientation" value="right" />
						</td>
					</tr>
				</table>

				<h4>Selection Mode</h4>
				<table>
					<tr>
						<td><label for="s-normal">Normal </label></td>
						<td>
						<input type="radio" id="s-normal" name="selection" checked="checked" value="normal" />
						</td>
					</tr>
					<tr>
						<td><label for="s-root">Set as Root </label></td>
						<td>
						<input type="radio" id="s-root" name="selection" value="root" />
						</td>
					</tr>
				</table>

			</div>

			<div id="log"></div>
		</div>

		<?php
		if (isset($_POST['sub'])) {
			$test -> insertData($_POST['in'], new BTreeData());
		}

		if (isset($_POST['del'])) {
			$test -> deleteData($_POST['in']);
		}

		$mem1 = memory_get_usage();
		$start = microtime(true);
		for ($i = 1500; $i > 1000; $i--) {
			$test -> insertData($i, new BTreeData());
		}
		$end = microtime(true);
		$mem2 = memory_get_usage();
		$time = $end - $start;
		$result = $mem2 - $mem1;
		echo 'Start: ' . $mem1 . ', Ende: ' . $mem2 . ', Differenz ' . $result . '<br>';
		echo $time . '<br>';

		// echo ($test->printTree());
		// echo '<br>';
		//print_r($test);
		//$_SESSION['test'] = $test;
		?>
	</body>
</html>