<?php
/*
	FusionPBX
	Version: MPL 1.1

	The contents of this file are subject to the Mozilla Public License Version
	1.1 (the "License"); you may not use this file except in compliance with
	the License. You may obtain a copy of the License at
	http://www.mozilla.org/MPL/

	Software distributed under the License is distributed on an "AS IS" basis,
	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
	for the specific language governing rights and limitations under the
	License.

	The Original Code is FusionPBX

	The Initial Developer of the Original Code is
	Mark J Crane <markjcrane@fusionpbx.com>
	Portions created by the Initial Developer are Copyright (C) 2008-2012
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/
include "root.php";
require_once "includes/config.php";
require_once "includes/checkauth.php";
if (permission_exists('public_includes_add')) {
	//access granted
}
else {
	echo "access denied";
	exit;
}
require_once "includes/header.php";
require_once "includes/paging.php";

//get the http get values and set them as php variables
	$order_by = $_GET["order_by"];
	$order = $_GET["order"];
	$action = $_GET["action"];

//get the http post values and set them as php variables
	if (count($_POST)>0) {
		$dialplan_name = check_str($_POST["dialplan_name"]);
		$limit = check_str($_POST["limit"]);
		$public_order = check_str($_POST["public_order"]);
		$condition_field_1 = check_str($_POST["condition_field_1"]);
		$condition_expression_1 = check_str($_POST["condition_expression_1"]);
		$condition_field_2 = check_str($_POST["condition_field_2"]);
		$condition_expression_2 = check_str($_POST["condition_expression_2"]);

 		$action_1 = check_str($_POST["action_1"]);
		//$action_1 = "transfer:1001 XML default";
		$action_1_array = explode(":", $action_1);
		$action_application_1 = array_shift($action_1_array);
		$action_data_1 = join(':', $action_1_array);

 		$action_2 = check_str($_POST["action_2"]);
		//$action_2 = "transfer:1001 XML default";
		$action_2_array = explode(":", $action_2);
		$action_application_2 = array_shift($action_2_array);
		$action_data_2 = join(':', $action_2_array);

		//$action_application_1 = check_str($_POST["action_application_1"]);
		//$action_data_1 = check_str($_POST["action_data_1"]);
		//$action_application_2 = check_str($_POST["action_application_2"]);
		//$action_data_2 = check_str($_POST["action_data_2"]);

		if (ifgroup("superadmin") && $action == "advanced") {
			//allow users in the superadmin group advanced control
		}
		else {
			if (strlen($condition_field_1) == 0) { $condition_field_1 = "destination_number"; }
			if (strlen($condition_expression_1) < 8) { $msg .= "The destination number must be 7 or more digits.<br>\n"; }
			if (is_numeric($condition_expression_1)) { 
				//the number is numeric 
				$condition_expression_1 = '^'.$condition_expression_1.'$';
			}
			else {
				$msg .= "The destination number must be numeric.<br>\n";
			}
		}
		$dialplan_enabled = check_str($_POST["dialplan_enabled"]);
		$dialplan_description = check_str($_POST["dialplan_description"]);
		if (strlen($dialplan_enabled) == 0) { $dialplan_enabled = "true"; } //set default to enabled
	}

if (count($_POST)>0 && strlen($_POST["persistformvar"]) == 0) {
	//check for all required data
		if (strlen($domain_uuid) == 0) { $msg .= "Please provide: domain_uuid<br>\n"; }
		if (strlen($dialplan_name) == 0) { $msg .= "Please provide: Extension Name<br>\n"; }
		if (strlen($condition_field_1) == 0) { $msg .= "Please provide: Condition Field<br>\n"; }
		if (strlen($condition_expression_1) == 0) { $msg .= "Please provide: Condition Expression<br>\n"; }
		if (strlen($action_application_1) == 0) { $msg .= "Please provide: Action Application<br>\n"; }
		//if (strlen($limit) == 0) { $msg .= "Please provide: Limit<br>\n"; }
		//if (strlen($dialplan_enabled) == 0) { $msg .= "Please provide: Enabled True or False<br>\n"; }
		//if (strlen($dialplan_description) == 0) { $msg .= "Please provide: Description<br>\n"; }
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

	//remove the invalid characters from the extension name
		$dialplan_name = str_replace(" ", "_", $dialplan_name);
		$dialplan_name = str_replace("/", "", $dialplan_name);

	//start the atomic transaction
		$count = $db->exec("BEGIN;"); //returns affected rows

	//add the main dialplan entry
		$dialplan_uuid = uuid();
		$sql = "insert into v_dialplans ";
		$sql .= "(";
		$sql .= "domain_uuid, ";
		$sql .= "dialplan_uuid, ";
		$sql .= "dialplan_name, ";
		$sql .= "dialplan_order	, ";
		$sql .= "dialplan_context, ";
		$sql .= "dialplan_enabled, ";
		$sql .= "dialplan_description ";
		$sql .= ") ";
		$sql .= "values ";
		$sql .= "(";
		$sql .= "'$domain_uuid', ";
		$sql .= "'$dialplan_uuid', ";
		$sql .= "'$dialplan_name', ";
		$sql .= "'$public_order', ";
		$sql .= "'public', ";
		$sql .= "'$dialplan_enabled', ";
		$sql .= "'$dialplan_description' ";
		$sql .= ")";
		$db->exec(check_sql($sql));
		unset($sql);

	//add condition public context
		$dialplan_detail_uuid = uuid();
		$sql = "insert into v_dialplan_details ";
		$sql .= "(";
		$sql .= "domain_uuid, ";
		$sql .= "dialplan_uuid, ";
		$sql .= "dialplan_detail_uuid, ";
		$sql .= "tag, ";
		$sql .= "field_type, ";
		$sql .= "field_data, ";
		$sql .= "field_order ";
		$sql .= ") ";
		$sql .= "values ";
		$sql .= "(";
		$sql .= "'$domain_uuid', ";
		$sql .= "'$dialplan_uuid', ";
		$sql .= "'$dialplan_detail_uuid', ";
		$sql .= "'condition', ";
		$sql .= "'dialplan_context', ";
		$sql .= "'public', ";
		$sql .= "'10' ";
		$sql .= ")";
		$db->exec(check_sql($sql));
		unset($sql);

	//add condition 1
		$dialplan_detail_uuid = uuid();
		$sql = "insert into v_dialplan_details ";
		$sql .= "(";
		$sql .= "domain_uuid, ";
		$sql .= "dialplan_uuid, ";
		$sql .= "dialplan_detail_uuid, ";
		$sql .= "tag, ";
		$sql .= "field_type, ";
		$sql .= "field_data, ";
		$sql .= "field_order ";
		$sql .= ") ";
		$sql .= "values ";
		$sql .= "(";
		$sql .= "'$domain_uuid', ";
		$sql .= "'$dialplan_uuid', ";
		$sql .= "'$dialplan_detail_uuid', ";
		$sql .= "'condition', ";
		$sql .= "'$condition_field_1', ";
		$sql .= "'$condition_expression_1', ";
		$sql .= "'20' ";
		$sql .= ")";
		$db->exec(check_sql($sql));
		unset($sql);

	//add condition 2
		if (strlen($condition_field_2) > 0) {
			$dialplan_detail_uuid = uuid();
			$sql = "insert into v_dialplan_details ";
			$sql .= "(";
			$sql .= "domain_uuid, ";
			$sql .= "dialplan_uuid, ";
			$sql .= "dialplan_detail_uuid, ";
			$sql .= "tag, ";
			$sql .= "field_type, ";
			$sql .= "field_data, ";
			$sql .= "field_order ";
			$sql .= ") ";
			$sql .= "values ";
			$sql .= "(";
			$sql .= "'$domain_uuid', ";
			$sql .= "'$dialplan_uuid', ";
			$sql .= "'$dialplan_detail_uuid', ";
			$sql .= "'condition', ";
			$sql .= "'$condition_field_2', ";
			$sql .= "'$condition_expression_2', ";
			$sql .= "'30' ";
			$sql .= ")";
			$db->exec(check_sql($sql));
			unset($sql);
		}

	//set domain
		if (count($_SESSION["domains"]) > 1) {
			$dialplan_detail_uuid = uuid();
			$sql = "insert into v_dialplan_details ";
			$sql .= "(";
			$sql .= "domain_uuid, ";
			$sql .= "dialplan_uuid, ";
			$sql .= "dialplan_detail_uuid, ";
			$sql .= "tag, ";
			$sql .= "field_type, ";
			$sql .= "field_data, ";
			$sql .= "field_order ";
			$sql .= ") ";
			$sql .= "values ";
			$sql .= "(";
			$sql .= "'$domain_uuid', ";
			$sql .= "'$dialplan_uuid', ";
			$sql .= "'$dialplan_detail_uuid', ";
			$sql .= "'action', ";
			$sql .= "'set', ";
			$sql .= "'domain=".$v_domain."', ";
			$sql .= "'40' ";
			$sql .= ")";
			$db->exec(check_sql($sql));
			unset($sql);
		}

	//set domain_name
		if (count($_SESSION["domains"]) > 1) {
			$dialplan_detail_uuid = uuid();
			$sql = "insert into v_dialplan_details ";
			$sql .= "(";
			$sql .= "domain_uuid, ";
			$sql .= "dialplan_uuid, ";
			$sql .= "dialplan_detail_uuid, ";
			$sql .= "tag, ";
			$sql .= "field_type, ";
			$sql .= "field_data, ";
			$sql .= "field_order ";
			$sql .= ") ";
			$sql .= "values ";
			$sql .= "(";
			$sql .= "'$domain_uuid', ";
			$sql .= "'$dialplan_uuid', ";
			$sql .= "'$dialplan_detail_uuid', ";
			$sql .= "'action', ";
			$sql .= "'set', ";
			$sql .= "'domain_name=\${domain}', ";
			$sql .= "'50' ";
			$sql .= ")";
			$db->exec(check_sql($sql));
			unset($sql);
		}

	//set call_direction
		if (count($_SESSION["domains"]) > 1) {
			$dialplan_detail_uuid = uuid();
			$sql = "insert into v_dialplan_details ";
			$sql .= "(";
			$sql .= "domain_uuid, ";
			$sql .= "dialplan_uuid, ";
			$sql .= "dialplan_detail_uuid, ";
			$sql .= "tag, ";
			$sql .= "field_type, ";
			$sql .= "field_data, ";
			$sql .= "field_order ";
			$sql .= ") ";
			$sql .= "values ";
			$sql .= "(";
			$sql .= "'$domain_uuid', ";
			$sql .= "'$dialplan_uuid', ";
			$sql .= "'$dialplan_detail_uuid', ";
			$sql .= "'action', ";
			$sql .= "'set', ";
			$sql .= "'call_direction=inbound', ";
			$sql .= "'60' ";
			$sql .= ")";
			$db->exec(check_sql($sql));
			unset($sql);
		}

	//set limit
		if (strlen($limit) > 0) {
			$dialplan_detail_uuid = uuid();
			$sql = "insert into v_dialplan_details ";
			$sql .= "(";
			$sql .= "domain_uuid, ";
			$sql .= "dialplan_uuid, ";
			$sql .= "dialplan_detail_uuid, ";
			$sql .= "tag, ";
			$sql .= "field_type, ";
			$sql .= "field_data, ";
			$sql .= "field_order ";
			$sql .= ") ";
			$sql .= "values ";
			$sql .= "(";
			$sql .= "'$domain_uuid', ";
			$sql .= "'$dialplan_uuid', ";
			$sql .= "'$dialplan_detail_uuid', ";
			$sql .= "'action', ";
			$sql .= "'limit', ";
			$sql .= "'db \${domain} inbound ".$limit." !USER_BUSY', ";
			$sql .= "'70' ";
			$sql .= ")";
			$db->exec(check_sql($sql));
			unset($sql);
		}

	//set answer
		$tmp_app = false;
		if ($action_application_1 == "ivr") { $tmp_app = true; }
		if ($action_application_2 == "ivr") { $tmp_app = true; }
		if ($action_application_1 == "conference") { $tmp_app = true; }
		if ($action_application_2 == "conference") { $tmp_app = true; }
		if ($tmp_app) {
			$dialplan_detail_uuid = uuid();
			$sql = "insert into v_dialplan_details ";
			$sql .= "(";
			$sql .= "domain_uuid, ";
			$sql .= "dialplan_uuid, ";
			$sql .= "dialplan_detail_uuid, ";
			$sql .= "tag, ";
			$sql .= "field_type, ";
			$sql .= "field_data, ";
			$sql .= "field_order ";
			$sql .= ") ";
			$sql .= "values ";
			$sql .= "(";
			$sql .= "'$domain_uuid', ";
			$sql .= "'$dialplan_uuid', ";
			$sql .= "'$dialplan_detail_uuid', ";
			$sql .= "'action', ";
			$sql .= "'answer', ";
			$sql .= "'', ";
			$sql .= "'80' ";
			$sql .= ")";
			$db->exec(check_sql($sql));
			unset($sql);
		}
		unset($tmp_app);

	//add action 1
		$dialplan_detail_uuid = uuid();
		$sql = "insert into v_dialplan_details ";
		$sql .= "(";
		$sql .= "domain_uuid, ";
		$sql .= "dialplan_uuid, ";
		$sql .= "dialplan_detail_uuid, ";
		$sql .= "tag, ";
		$sql .= "field_type, ";
		$sql .= "field_data, ";
		$sql .= "field_order ";
		$sql .= ") ";
		$sql .= "values ";
		$sql .= "(";
		$sql .= "'$domain_uuid', ";
		$sql .= "'$dialplan_uuid', ";
		$sql .= "'$dialplan_detail_uuid', ";
		$sql .= "'action', ";
		$sql .= "'$action_application_1', ";
		$sql .= "'$action_data_1', ";
		$sql .= "'90' ";
		$sql .= ")";
		$db->exec(check_sql($sql));
		unset($sql);

	//add action 2
		if (strlen($action_application_2) > 0) {
			$dialplan_detail_uuid = uuid();
			$sql = "insert into v_dialplan_details ";
			$sql .= "(";
			$sql .= "domain_uuid, ";
			$sql .= "dialplan_uuid, ";
			$sql .= "dialplan_detail_uuid, ";
			$sql .= "tag, ";
			$sql .= "field_type, ";
			$sql .= "field_data, ";
			$sql .= "field_order ";
			$sql .= ") ";
			$sql .= "values ";
			$sql .= "(";
			$sql .= "'$domain_uuid', ";
			$sql .= "'$dialplan_uuid', ";
			$sql .= "'$dialplan_detail_uuid', ";
			$sql .= "'action', ";
			$sql .= "'$action_application_2', ";
			$sql .= "'$action_data_2', ";
			$sql .= "'100' ";
			$sql .= ")";
			$db->exec(check_sql($sql));
			unset($sql);
		}

	//commit the atomic transaction
		$count = $db->exec("COMMIT;"); //returns affected rows

	//synchronize the xml config
		sync_package_v_dialplan();

	//redirect the user
		require_once "includes/header.php";
		echo "<meta http-equiv=\"refresh\" content=\"2;url=dialplans.php?dialplan_context=public\">\n";
		echo "<div align='center'>\n";
		echo "Update Complete\n";
		echo "</div>\n";
		require_once "includes/footer.php";
		return;
} //end if (count($_POST)>0 && strlen($_POST["persistformvar"]) == 0)

?>

<script type="text/javascript">
<!--
	function type_onchange(field_type) {
	var field_value = document.getElementById(field_type).value;
	if (field_type == "condition_field_1") {
		if (field_value == "destination_number") {
			document.getElementById("desc_condition_expression_1").innerHTML = "expression: 5551231234";
		}
		else if (field_value == "zzz") {
			document.getElementById("desc_condition_expression_1").innerHTML = "";
		}
		else {
			document.getElementById("desc_condition_expression_1").innerHTML = "";
		}
	}
	if (field_type == "condition_field_2") {
		if (field_value == "destination_number") {
			document.getElementById("desc_condition_expression_2").innerHTML = "expression: 5551231234";
		}
		else if (field_value == "zzz") {
			document.getElementById("desc_condition_expression_2").innerHTML = "";
		}
		else {
			document.getElementById("desc_condition_expression_2").innerHTML = "";
		}
	}
-->
</script>

<?php
//show the content
	echo "<div align='center'>";
	echo "<table width='100%' border='0' cellpadding='0' cellspacing='2'>\n";

	echo "<tr class='border'>\n";
	echo "	<td align=\"left\">\n";
	echo "		<br>";

	echo "<form method='post' name='frm' action=''>\n";
	echo "<div align='center'>\n";

	echo " 	<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
	echo "	<tr>\n";
	echo "		<td align='left'><span class=\"vexpl\"><span class=\"red\"><strong>Inbound Call Routing\n";
	echo "			</strong></span></span>\n";
	echo "		</td>\n";
	echo "		<td align='right'>\n";
	if (permission_exists("public_includes_edit") && $action == "advanced") {
		echo "			<input type='button' class='btn' name='' alt='basic' onclick=\"window.location='dialplan_public_add.php?action=basic'\" value='Basic'>\n";
	}
	else {
		echo "			<input type='button' class='btn' name='' alt='advanced' onclick=\"window.location='dialplan_public_add.php?action=advanced'\" value='Advanced'>\n";
	}
	echo "			<input type='button' class='btn' name='' alt='back' onclick=\"window.location='dialplans.php?dialplan_context=public'\" value='Back'>\n";
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "	<tr>\n";
	echo "		<td align='left' colspan='2'>\n";
	echo "			<span class=\"vexpl\">\n";
	echo "			The public dialplan is used to route incoming calls to destinations based on one or more conditions and context. It can send incoming calls to an auto attendant, huntgroup, extension, external number, or a script.\n";
	echo "		</span>\n";
	echo "		<br />\n";
	echo "			</span>\n";
	echo "		</td>\n";
	echo "	</tr>\n";
	echo "	</table>";

	echo "<br />\n";
	echo "<br />\n";

	echo "<table width='100%'  border='0' cellpadding='6' cellspacing='0'>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap>\n";
	echo "    Name:\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <input class='formfld' style='width: 60%;' type='text' name='dialplan_name' maxlength='255' value=\"$dialplan_name\">\n";
	echo "<br />\n";
	echo "Please enter an inbound route name.<br />\n";
	echo "</td>\n";
	echo "</tr>\n";

	if (permission_exists("public_includes_edit") && $action == "advanced") {
		echo "<tr>\n";
		echo "<td class='vncellreq' valign='top' align='left' nowrap>\n";
		echo "	Condition 1:\n";
		echo "</td>\n";
		echo "<td class='vtable' align='left'>\n";
		echo "	<table style='width: 60%;' border='0'>\n";
		echo "	<tr>\n";
		echo "	<td style='width: 62px;'>Field:</td>\n";
		echo "	<td style='width: 35%;'>\n";

		echo "    <select class='formfld' name='condition_field_1' id='condition_field_1' onchange='type_onchange(\"condition_field_1\");' style='width:100%'>\n";
		echo "    <option value=''></option>\n";
		if (strlen($condition_field_1) > 0) {
			echo "    <option value='$condition_field_1' selected>$condition_field_1</option>\n";
		}
		echo "    <option value='context'>context</option>\n";
		echo "    <option value='username'>username</option>\n";
		echo "    <option value='rdnis'>rdnis</option>\n";
		echo "    <option value='destination_number'>destination_number</option>\n";
		echo "    <option value='public'>public</option>\n";
		echo "    <option value='caller_id_name'>caller_id_name</option>\n";
		echo "    <option value='caller_id_number'>caller_id_number</option>\n";
		echo "    <option value='ani'>ani</option>\n";
		echo "    <option value='ani2'>ani2</option>\n";
		echo "    <option value='uuid'>uuid</option>\n";
		echo "    <option value='source'>source</option>\n";
		echo "    <option value='chan_name'>chan_name</option>\n";
		echo "    <option value='network_addr'>network_addr</option>\n";
		echo "    </select><br />\n";

		echo "	</td>\n";
		echo "	<td style='width: 73px;'>&nbsp; Expression:</td>\n";
		echo "	<td>\n";
		echo "		<input class='formfld' type='text' name='condition_expression_1' maxlength='255' style='width:100%' value=\"$condition_expression_1\">\n";
		echo "	</td>\n";
		echo "	</tr>\n";
		echo "	</table>\n";
		echo "	<div id='desc_condition_expression_1'></div>\n";
		echo "</td>\n";
		echo "</tr>\n";

		echo "<tr>\n";
		echo "<td class='vncell' valign='top' align='left' nowrap>\n";
		echo "	Condition 2:\n";
		echo "</td>\n";
		echo "<td class='vtable' align='left'>\n";

		echo "	<table style='width: 60%;' border='0'>\n";
		echo "	<tr>\n";
		echo "	<td align='left' style='width: 62px;'>\n";
		echo "		Field:\n";
		echo "	</td>\n";
		echo "	<td style='width: 35%;' align='left'>\n";
		echo "    <select class='formfld' name='condition_field_2' id='condition_field_2' onchange='type_onchange(\"condition_field_2\");' style='width:100%'>\n";
		echo "    <option value=''></option>\n";
		if (strlen($condition_field_2) > 0) {
			echo "    <option value='$condition_field_2' selected>$condition_field_2</option>\n";
		}
		echo "    <option value='context'>context</option>\n";
		echo "    <option value='username'>username</option>\n";
		echo "    <option value='rdnis'>rdnis</option>\n";
		echo "    <option value='destination_number'>destination_number</option>\n";
		echo "    <option value='public'>public</option>\n";
		echo "    <option value='caller_id_name'>caller_id_name</option>\n";
		echo "    <option value='caller_id_number'>caller_id_number</option>\n";
		echo "    <option value='ani'>ani</option>\n";
		echo "    <option value='ani2'>ani2</option>\n";
		echo "    <option value='uuid'>uuid</option>\n";
		echo "    <option value='source'>source</option>\n";
		echo "    <option value='chan_name'>chan_name</option>\n";
		echo "    <option value='network_addr'>network_addr</option>\n";
		echo "    </select><br />\n";
		echo "	</td>\n";
		echo "	<td style='width: 73px;' align='left'>\n";
		echo "		&nbsp; Expression:\n";
		echo "	</td>\n";
		echo "	<td>\n";
		echo "		<input class='formfld' type='text' name='condition_expression_2' maxlength='255' style='width:100%' value=\"$condition_expression_2\">\n";
		echo "	</td>\n";
		echo "	</tr>\n";
		echo "	</table>\n";
		echo "	<div id='desc_condition_expression_2'></div>\n";
		echo "</td>\n";
		echo "</tr>\n";
	}
	else {
		echo "<tr>\n";
		echo "<td class='vncellreq' valign='top' align='left' nowrap>\n";
		echo "    Destination Number:\n";
		echo "</td>\n";
		echo "<td class='vtable' align='left'>\n";
		echo "    <input class='formfld' style='width: 60%;' type='text' name='condition_expression_1' maxlength='255' value=\"$condition_expression_1\">\n";
		echo "<br />\n";
		echo "Please enter the destination number. In North America this is usually a 10 or 11 digit number.\n";
		echo "</td>\n";
		echo "</tr>\n";
	}

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap>\n";
	if (permission_exists("public_includes_edit") && $action=="advanced") {
		echo "    Action 1:\n";
	}
	else {
		echo "    Action:\n";
	}
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";

	//switch_select_destination(select_type, select_label, select_name, select_value, select_style, action);
	switch_select_destination("dialplan", "", "action_1", $action_1, "width: 60%;", "");

	echo "</td>\n";
	echo "</tr>\n";

	echo "</td>\n";
	echo "</tr>\n";

	if (permission_exists("public_includes_edit") && $action=="advanced") {
		echo "<tr>\n";
		echo "<td class='vncell' valign='top' align='left' nowrap>\n";
		echo "    Action 2:\n";
		echo "</td>\n";
		echo "<td class='vtable' align='left'>\n";

		//switch_select_destination(select_type, select_label, select_name, select_value, select_style, action);
		switch_select_destination("dialplan", "", "action_2", $action_2, "width: 60%;", "");

		echo "</td>\n";
		echo "</tr>\n";
	}

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap>\n";
	echo "    Limit:\n";
	echo "</td>\n";
	echo "<td colspan='4' class='vtable' align='left'>\n";
	echo "    <input class='formfld' style='width: 60%;' type='text' name='limit' maxlength='255' value=\"$limit\">\n";
	echo "<br />\n";
	echo "\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap>\n";
	echo "    Order:\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "              <select name='public_order' class='formfld' style='width: 60%;'>\n";
	if (strlen(htmlspecialchars($public_order))> 0) {
		echo "              <option selected='yes' value='".htmlspecialchars($public_order)."'>".htmlspecialchars($public_order)."</option>\n";
	}
	$i=0;
	while($i<=999) {
		if (strlen($i) == 1) { echo "              <option value='00$i'>00$i</option>\n"; }
		if (strlen($i) == 2) { echo "              <option value='0$i'>0$i</option>\n"; }
		if (strlen($i) == 3) { echo "              <option value='$i'>$i</option>\n"; }
		$i++;
	}
	echo "              </select>\n";
	echo "<br />\n";
	echo "\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncellreq' valign='top' align='left' nowrap>\n";
	echo "    Enabled:\n";
	echo "</td>\n";
	echo "<td class='vtable' align='left'>\n";
	echo "    <select class='formfld' name='dialplan_enabled' style='width: 60%;'>\n";
	if ($dialplan_enabled == "true") { 
		echo "    <option value='true' SELECTED >true</option>\n";
	}
	else {
		echo "    <option value='true'>true</option>\n";
	}
	if ($dialplan_enabled == "false") { 
		echo "    <option value='false' SELECTED >false</option>\n";
	}
	else {
		echo "    <option value='false'>false</option>\n";
	}
	echo "    </select>\n";
	echo "<br />\n";
	echo "\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "<td class='vncell' valign='top' align='left' nowrap>\n";
	echo "    Description:\n";
	echo "</td>\n";
	echo "<td colspan='4' class='vtable' align='left'>\n";
	echo "    <input class='formfld' style='width: 60%;' type='text' name='dialplan_description' maxlength='255' value=\"$dialplan_description\">\n";
	echo "<br />\n";
	echo "\n";
	echo "</td>\n";
	echo "</tr>\n";

	echo "<tr>\n";
	echo "	<td colspan='5' align='right'>\n";
	if ($action == "update") {
		echo "			<input type='hidden' name='dialplan_uuid' value='$dialplan_uuid'>\n";
	}
	echo "			<input type='submit' name='submit' class='btn' value='Save'>\n";
	echo "	</td>\n";
	echo "</tr>";

	echo "</table>";
	echo "</div>";
	echo "</form>";

	echo "</td>\n";
	echo "</tr>";
	echo "</table>";
	echo "</div>";

	echo "<br><br>";

//include the footer
	require_once "includes/footer.php";
?>