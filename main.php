<?php
// This file is a total testing mess.
// Not much else to say I guess.

//includes
include './BTree.php';

session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title></title>
	</head>
	<body>
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
