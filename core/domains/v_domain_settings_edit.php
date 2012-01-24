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

//action add or update
	if (isset($_REQUEST["id"])) {
		$action = "update";
		$domain_setting_uuid = check_str($_REQUEST["id"]);
	}
	else {
		$action = "add";
	}

if (strlen($_GET["domain_uuid"]) > 0) {
	$domain_uuid = check_str($_GET["domain_uuid"]);
}

//get http post variables and set them to php variables
	if (count($_POST)>0) {
		$domain_setting_name = check_str($_POST["domain_setting_name"]);
		$domain_setting_value = check_str($_POST["domain_setting_value"]);
	}

if (count($_POST)>0 && strlen($_POST["persistformvar"]) == 0) {

	$msg = '';
	if ($action == "update") {
		$domain_setting_uuid = check_str($_POST["domain_setting_uuid"]);
	}

	//check for all required data
		//if (strlen($domain_uuid) == 0) { $msg .= "Please provide: domain_uuid<br>\n"; }
		//if (strlen($domain_setting_name) == 0) { $msg .= "Please provide: Name<br>\n"; }
		//if (strlen($domain_setting_value) == 0) { $msg .= "Please provide: Value<br>\n"; }
		if (strlen($msg) > 0 && strlen($_POST["persistformvar"]) == 0) {
			require_once "includes/header.php";
			require_once "includes/persistformvar.php";
			echo "<div align='center'>\n";
			echo "<table><tr><td>\n";
			echo $msg."<br />";
			echo "</td></tr></table>\n";
			persistformvar($_POST);
			echo "</div>\n";
			require_once "includes/footer.php";
			return;
		}

	//add or update the database
		if ($_POST["persistformvar"] != "true") {
			if ($action == "add") {
				$sql = "insert into v_domain_settings ";
				$sql .= "(";
				$sql .= "domain_uuid, ";
				$sql .= "domain_uuid, ";
				$sql .= "domain_uuid, ";
				$sql .= "domain_setting_name, ";
				$sql .= "domain_setting_value ";
				$sql .= ")";
				$sql .= "values ";
				$sql .= "(";
				$sql .= "'$domain_uuid', ";
				$sql .= "'$domain_uuid', ";
				$sql .= "'$domain_uuid', ";
				$sql .= "'$domain_setting_name', ";
				$sql .= "'$domain_setting_value' ";
				$sql .= ")";
				$db->exec(check_sql($sql));
				unset($sql);

				require_once "includes/header.php";
				echo "<meta http-equiv=\"refresh\" content=\"2;url=v_domains_edit.php?id=$domain_uuid\">\n";
				echo "<div align='center'>\n";
				echo "Add Complete\n";
				echo "</div>\n";
				require_once "includes/footer.php";
				return;
			} //if ($action == "add")

			if ($action == "update") {
				$sql = "update v_domain_settings set ";
				$sql .= "domain_uuid = '$domain_uuid', ";
				$sql .= "domain_uuid = '$domain_uuid', ";
				$sql .= "domain_setting_name = '$domain_setting_name', ";
				$sql .= "domain_setting_value = '$domain_setting_value' ";
				$sql .= "where domain_uuid = '$domain_uuid' ";
				$sql .= "and domain_setting_uuid = '$domain_setting_uuid'";
				$db->exec(check_sql($sql));
				unset($sql);

				require_once "includes/header.php";
				echo "<meta http-equiv=\"refresh\" content=\"2;url=v_domains_edit.php?id=$domain_uuid\">\n";
				echo "<div align='center'>\n";
				echo "Update Complete\n";
				echo "</div>\n";
				require_once "includes/footer.php";
				return;
			} //if ($action == "update")
		} //if ($_POST["persistformvar"] != "true") 
} //(count($_POST)>0 && strlen($_POST["persistformvar"]) == 0)

//pre-populate the form
	if (count($_GET)>0 && $_POST["persistformvar"] != "true") {
		$domain_setting_uuid = $_GET["id"];
		$sql = "";
		$sql .= "select * from v_domain_settings ";
		$sql .= "where domain_uuid = '$domain_uuid' ";
		$sql .= "and domain_setting_uuid = '$domain_setting_uuid' ";
		$prep_statement = $db->prepare(check_sql($sql));
		$prep_statement->execute();
		$result = $prep_statement->fetchAll();
		foreach ($result as &$row) {
			$domain_setting_name = $row["domain_setting_name"];
			$domain_setting_value = $row["domain_setting_value"];
			break; //limit to 1 row
		}
		unset ($prep_statement);
	}

//show the header
	require_once "includes/header.php";

//show the content
	echo "<div align='center'>";
	echo "<table width='100%' border='0' cellpadding='0' cellspacing=''>\n";

	echo "<tr class='border'>\n";
	echo "	<td align=\"left\">\n";
	echo "	  <br>";

	echo "<form method='post' name='frm' action=''>\n";
	echo "<div align='center'>\n";
	echo "<table width='100%'  border='0' cellpadding='6' cellspacing='0'>\n";
	echo "<tr>\n";
	if ($action == "add") {
		echo "<td align='left' width='30%' nowrap='nowrap'><b>Domain Setting Add</b></td>\n";
	}
	if ($action == "update") {
		echo "<td align='left' width='30%' nowrap='nowrap'><b>Domain Setting Edit</b></td>\n";
	}
	echo "<td width='70%' align='right'><input type='button' class='btn' name='' alt='back' onclick=\"window.location='v_domains_edit.php?id=$domain_uuid'\" value='Back'></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td colspan='2'>\n";
	echo "Settings used for each domain.<br /><br />\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	Name:\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='domain_setting_name' maxlength='255' value=\"$domain_setting_name\">\n";
	echo "<br />\n";
	echo "Enter the name.\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap='nowrap'>\n";
	echo "	Value:\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "	<input class='formfld' type='text' name='domain_setting_value' maxlength='255' value=\"$domain_setting_value\">\n";
	echo "<br />\n";
	echo "Enter the value.\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "	<tr>\n";
	echo "		<td colspan='2' align='right'>\n";
	echo "				<input type='hidden' name='domain_uuid' value='$domain_uuid'>\n";
	if ($action == "update") {
		echo "				<input type='hidden' name='domain_setting_uuid' value='$domain_setting_uuid'>\n";
	}
	echo "				<input type='submit' name='submit' class='btn' value='Save'>\n";
	echo "		</td>\n";
	echo "	</tr>";
	echo "</table>";
	echo "</form>";

	echo "	</td>";
	echo "	</tr>";
	echo "</table>";
	echo "</div>";

//include the footer
	require_once "includes/footer.php";
?>