<?php
/*****************************************************************
**  Administrative page for maintaining users of the timer system
**
**  Author: Michael Holland - Sept. 2014
**
**	Revisions:
**		MH-20141020 Initial build
**
*****************************************************************/

//  Page setup
$pagetitle = "User Administration";
include 'authadmin.php';
?>
<h1>User Administration</h1>

<div id="inner">
<table align="center">
<?php
//  connect to user db and list users
$adminclass->createUserDBConnection();
$sql = "Select * from users order by user_name";
$shade = 0;
foreach ($adminclass->user_db_connection->query($sql) as $row) {
	if ($shade == 0) {
		$class = "even";
		$shade++;
	} else {
		$class= "odd";
		$shade = 0;
	}
	echo "\t<tr class=\"" . $class . "\"><td><a href=\"index.php?action=delete&user=" . $row['user_name'] . "\"><i class=\"fa fa-trash trashicon\"></i></a></td>\n";
	echo "\t\t<td>" . $row['user_name'] . "</td>\n";
	echo "\t\t<td>" . $row['user_email'] . "</td></tr>\n";
}


?>
</table>
<p><a href="index.php?action=register" class="button-go pure-button"><i class="fa fa-user"></i> Add Generator User</a> &nbsp;
<a href="index.php?action=register&type=admin" class="button-go pure-button"><i class="fa fa-users"></i> Add Admin User</a></p>
</div>
<?php include('template/footer.php');  ?>