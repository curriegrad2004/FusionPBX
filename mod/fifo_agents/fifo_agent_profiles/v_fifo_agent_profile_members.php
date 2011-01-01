<?php
require_once "root.php";
require_once "includes/config.php";
require_once "includes/checkauth.php";
if (ifgroup("admin") || ifgroup("superadmin")) {
	//access granted
}
else {
	echo "access denied";
	exit;
}
require_once "includes/header.php";
require_once "includes/paging.php";

$orderby = $_GET["orderby"];
$order = $_GET["order"];

	echo "<div align='center'>";
	echo "<table width='100%' border='0' cellpadding='0' cellspacing='2'>\n";
	echo "<tr class='border'>\n";
	echo "	<td align=\"center\">\n";
	echo "		<br>";


	echo "<table width='100%' border='0'>\n";
	echo "<tr>\n";
	echo "<td width='50%' nowrap='nowrap' align='left'><b>Agent Member List</b></td>\n";
	echo "<td width='50%' align='right'>&nbsp;</td>\n";
	echo "</tr>\n";
	echo "</tr></table>\n";


	$sql = "";
	$sql .= " select * from v_fifo_agent_profile_members ";
	$sql .= " where v_id = '$v_id' ";
	$sql .= " and fifo_agent_profile_id = '$fifo_agent_profile_id' ";
	if (strlen($orderby)> 0) { $sql .= "order by $orderby $order "; }
	$prepstatement = $db->prepare(check_sql($sql));
	$prepstatement->execute();
	$result = $prepstatement->fetchAll();
	$numrows = count($result);
	unset ($prepstatement, $result, $sql);
	$rowsperpage = 100;
	$param = "&id=".$_GET['id'];
	$page = $_GET['page'];
	if (strlen($page) == 0) { $page = 0; $_GET['page'] = 0; } 
	list($pagingcontrols, $rowsperpage, $var3) = paging($numrows, $param, $rowsperpage); 
	$offset = $rowsperpage * $page; 

	$sql = "";
	$sql .= " select * from v_fifo_agent_profile_members ";
	$sql .= " where v_id = '$v_id' ";
	$sql .= " and fifo_agent_profile_id = '$fifo_agent_profile_id' ";
	if (strlen($orderby)> 0) { $sql .= "order by $orderby $order "; }
	$sql .= " limit $rowsperpage offset $offset ";
	$prepstatement = $db->prepare(check_sql($sql));
	$prepstatement->execute();
	$result = $prepstatement->fetchAll();
	$resultcount = count($result);
	unset ($prepstatement, $sql);


	$c = 0;
	$rowstyle["0"] = "rowstyle0";
	$rowstyle["1"] = "rowstyle1";

	echo "<div align='center'>\n";
	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n";

	echo "<tr>\n";
	echo thorderby('fifo_name', 'FIFO Name', $orderby, $order);
	echo thorderby('agent_username', 'Agent', $orderby, $order);
	echo thorderby('agent_priority', 'Agent Priority', $orderby, $order);
	echo "<td align='right' width='42'>\n";
	echo "	<a href='v_fifo_agent_profile_members_edit.php?fifo_agent_profile_id=".$row[fifo_agent_profile_id]."' alt='add'><img src='".$v_icon_add."' width='17' height='17' border='0' alt='add'></a>\n";
	//echo "	<input type='button' class='btn' name='' alt='add' onclick=\"window.location='v_fifo_agent_profile_members_edit.php'\" value='+'>\n";
	echo "</td>\n";
	echo "<tr>\n";

	if ($resultcount == 0) { //no results
	}
	else { //received results
		foreach($result as $row) {
			//print_r( $row );
			echo "<tr >\n";
			echo "	<td valign='top' class='".$rowstyle[$c]."'>".$row[fifo_name]."</td>\n";
			echo "	<td valign='top' class='".$rowstyle[$c]."'>".$row[agent_username]."</td>\n";
			echo "	<td valign='top' class='".$rowstyle[$c]."'>".$row[agent_priority]."</td>\n";
			echo "	<td valign='top' align='right'>\n";
			echo "		<a href='v_fifo_agent_profile_members_edit.php?fifo_agent_profile_id=".$row[fifo_agent_profile_id]."&id=".$row[fifo_agent_profile_member_id]."' alt='edit'><img src='".$v_icon_edit."' width='17' height='17' alt='edit' border='0'></a>\n";
			echo "		<a href='v_fifo_agent_profile_members_delete.php?fifo_agent_profile_id=".$row[fifo_agent_profile_id]."&id=".$row[fifo_agent_profile_member_id]."' alt='delete' onclick=\"return confirm('Do you really want to delete this?')\"><img src='".$v_icon_delete."' width='17' height='17' alt='delete' border='0'></a>\n";
			//echo "		<input type='button' class='btn' name='' alt='edit' onclick=\"window.location='v_fifo_agent_profile_members_edit.php?id=".$row[fifo_agent_profile_member_id]."'\" value='e'>\n";
			//echo "		<input type='button' class='btn' name='' alt='delete' onclick=\"if (confirm('Are you sure you want to delete this?')) { window.location='v_fifo_agent_profile_members_delete.php?id=".$row[fifo_agent_profile_member_id]."' }\" value='x'>\n";
			echo "	</td>\n";
			echo "</tr>\n";
			if ($c==0) { $c=1; } else { $c=0; }
		} //end foreach
		unset($sql, $result, $rowcount);
	} //end if results


	echo "<tr>\n";
	echo "<td colspan='4' align='left'>\n";
	echo "	<table width='100%' cellpadding='0' cellspacing='0'>\n";
	echo "	<tr>\n";
	echo "		<td width='33.3%' nowrap>&nbsp;</td>\n";
	echo "		<td width='33.3%' align='center' nowrap>$pagingcontrols</td>\n";
	echo "		<td width='33.3%' align='right'>\n";
	echo "			<a href='v_fifo_agent_profile_members_edit.php?fifo_agent_profile_id=".$row[fifo_agent_profile_id]."' alt='add'><img src='".$v_icon_add."' width='17' height='17' border='0' alt='add'></a>\n";
	//echo "		<input type='button' class='btn' name='' alt='add' onclick=\"window.location='v_fifo_agent_profile_members_edit.php'\" value='+'>\n";
	echo "		</td>\n";
	echo "	</tr>\n";
 	echo "	</table>\n";
	echo "</td>\n";
	echo "</tr>\n";


	echo "</table>";
	echo "</div>";
	echo "<br><br>";
	echo "<br><br>";


	echo "</td>";
	echo "</tr>";
	echo "</table>";
	echo "</div>";
	echo "<br><br>";


require_once "includes/footer.php";
unset ($resultcount);
unset ($result);
unset ($key);
unset ($val);
unset ($c);
?>