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
	Portions created by the Initial Developer are Copyright (C) 2008-2010
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/
return; //disable
include "root.php";
require_once "includes/config.php";
require_once "includes/checkauth.php";
require_once "config.php";
if (permission_exists('content_view')) {
	//access granted
}
else {
	echo "access denied";
	exit;
}

//include module specific information
if (strlen($modconfigpath)==0) {
	include "config.php";
}
else {
	//$modconfigpath = "/news"; //examples
	//$modconfigpath = "/mod/news"; //examples
	include $modconfigpath.'/config.php';
}

$rsscssurl = 'http://'.$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"];
$rsscssurl = str_replace ("rss.php", "rss.css", $rsscssurl);
$contenttype = $_GET["c"];
//echo "contenttype $contenttype";
if (strlen($_GET["rss_category"]) > 0) {
	$rss_category = $_GET["rss_category"];
}
if (strlen($contenttype) == 0) {
	$contenttype = "rss"; //define default contenttype
}
if ($contenttype == "html") {
	session_start();
}
//echo $rsscssurl;
//exit;

if ($contenttype == "rss") {
	header('Content-Type: text/xml');
	echo '<?xml version="1.0"  ?'.'>';
	echo '<?xml-stylesheet type="text/css" href="'.$rsscssurl.'" ?'.'>';
	//echo '<?xml-stylesheet type="text/css" href="http://'.$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"].'" ?'.'>';
	//echo "\n";
	echo "<rss version=\"2.0\">\n";
	echo "<channel>\n";

	echo "<title>$moduletitle RSS Feed</title>\n";
	//echo "<link>http://www.xul.fr/</link>\n";
	echo "<description>Task List for RSS...</description>\n";
	echo "<language>en-US</language>\n";
	//echo "<copyright>copyright AH Digital FX Studios, 2006</copyright>\n";
	//echo "<image>\n";
	//echo "    <url>http://www.xul.fr/xul-icon.gif</url>\n";
	//echo "    <link>http://www.xul.fr/index.html</link>\n";
	//echo "</image>";
}

$sql = "";
$sql .= "select * from v_rss ";
$sql .= "where rss_category = '$rss_category' ";
$sql .= "and length(rss_del_date) = 0  ";
$sql .= "or rss_category = '$rss_category' ";
$sql .= "and rss_del_date is null ";
$sql .= "order by rss_id asc ";
//echo $sql;
$prepstatement = $db->prepare(check_sql($sql));
$prepstatement->execute();

$lastcat = "";
$count = 0;
$result = $prepstatement->fetchAll();
foreach ($result as &$row) {

	$rss_id = $row["rss_id"];
	$rss_title = $row["rss_title"];
	$rss_desc = $row["rss_desc"];
	$rss_link = $row["rss_link"];

	//$rss_desc = $row[rss_sub_desc];
	//$rss_desc = str_replace ("\r\n", "<br>", $rss_desc);
	//$rss_desc = str_replace ("\n", "<br>", $rss_desc);

	if ($contenttype == "rss") {
		$rss_title = htmlentities($rss_title);
		$rss_desc  = htmlentities($rss_desc);

		echo "<item>\n";
		echo "<title>".$rss_title."</title>\n";
		echo "<description>".$rss_desc."</description>\n";
		echo "<link>".$rss_link."</link>\n";
		//echo "<pubDate>12 Mar 2007 19:38:06 GMT</pubDate>\n";
		//echo "<guid isPermaLink='true'>http://www.google.com/log/123</guid>\n";
		//echo "<comments>http://www.google.com/log/121#comments</comments>\n";
		//echo "<category>Web Design</category>";
		echo "</item>\n";
		echo "\n";

	}
	else {
		if (strlen($rss_link) > 0) {
			echo "<b><a href='$rss_link'>".$rss_title."</a></b><br>\n";
		}
		else {
			echo "<b>".$rss_title."</b><br>\n";
		}
		echo "".$rss_desc."\n";
		echo "<br><br>";

		if ($rsssubshow == 1) {
		//--- Begin Sub List -------------------------------------------------------

			echo "<br><br><br>";
			echo "<b>$rss_sub_title</b><br>";

			$sql = "";
			$sql .= "select * from v_rss_sub ";
			$sql .= "where rss_id = '$rss_id'  ";
			$sql .= "and length(rss_sub_del_date) = 0  ";
			$sql .= "or rss_id = '$rss_id' ";
			$sql .= "and rss_sub_del_date is null ";

			if (strlen($orderby)> 0) { $sql .= "order by $orderby $order "; }

			$prepstatement2 = $db->prepare($sql);
			$prepstatement2->execute();
			$result2 = $prepstatement->fetchAll();
			$resultcount2 = count($result2);

			$c2 = 0;
			$rowstyle["0"] = "background-color: #F5F5DC;";
			$rowstyle["1"] = "background-color: #FFFFFF;";

			echo "<div align='left'>\n";
			//echo "      <b>Notes</b>";
			echo "<table width='75%' border='1' cellpadding='1' cellspacing='1'>\n";
			//echo "<tr><td colspan='100%'><img src='/images/spacer.gif' width='100%' height='1' style='background-color: #BBBBBB;'></td></tr>";
			if ($resultcount == 0) { //no results
				echo "<tr><td>&nbsp;</td></tr>";
			}
			else { //received results
				echo "<tr><td colspan='100%'><img src='/images/spacer.gif' width='100%' height='1' style='background-color: #BBBBBB;'></td></tr>\n";

				foreach($result2 as $row2) {
				//print_r( $row );
					echo "<tr style='".$rowstyle[$c]."'>\n";
						//echo "<td valign='top'>".$rss_id."</td>";
						//echo "<td valign='top'>&nbsp;<b>".$row2[rss_sub_title]."</b>&nbsp;</td>";
						//echo "<td valign='top'>&nbsp;".$row2[rss_sub_link]."&nbsp;</td>";
						echo "<td valign='top' width='200'>";
						echo "  <b>".$row2[rss_sub_title]."</b>";
						echo "</td>";

						echo "<td valign='top'>".$row2[rss_sub_add_date]."</td>";

						//echo "<td valign='top'>".$row2[rss_sub_optional_1]."</td>";
						//echo "<td valign='top'>".$row2[rss_sub_optional_2]."</td>";
						//echo "<td valign='top'>".$row2[rss_sub_optional_3]."</td>";
						//echo "<td valign='top'>".$row2[rss_sub_optional_4]."</td>";
						//echo "<td valign='top'>".$row2[rss_sub_optional_5]."</td>";
						//echo "<td valign='top'>".$row2[rss_sub_add_user]."</td>";
						echo "<td valign='top' align='right'>";
						echo "  &nbsp;";
						//echo "  <input type='button' class='btn' name='' onclick=\"window.location='rsssubupdate.php?rss_id=".$rss_id."&rss_sub_id=".$row2[rss_sub_id]."'\" value='Update'>";
						echo "  &nbsp; \n";
						//echo "  <a href='rsssubupdate.php?rss_id=".$rss_id."&rss_sub_id=".$row2[rss_sub_id]."'>Update</a>&nbsp;";
						echo "</td>";

						$rss_sub_desc = $row2[rss_sub_desc];
						//$rss_sub_desc = str_replace ("\r\n", "<br>", $rss_sub_desc);
						//$rss_sub_desc = str_replace ("\n", "<br>", $rss_sub_desc);


						echo "</tr>";
						echo "<tr style='".$rowstyle[$c]."'>\n";
						echo "<td valign='top' width='300' colspan='3'>";
						echo "".$rss_sub_desc."&nbsp;";
						echo "</td>";

						echo "</tr>";

					echo "</tr>";

					echo "<tr><td colspan='100%'><img src='/images/spacer.gif' width='100%' height='1' style='background-color: #BBBBBB;'></td></tr>\n";
					if ($c2==0) { $c2=1; } else { $c2=0; }
				} //end foreach
				unset($sql, $result, $rowcount);

				echo "</table>\n";
				echo "</div>\n";


				echo "  <br><br>";
				echo "  </td>\n";
				echo "</tr>\n";

			} //end if results

			echo "</table>\n";
		//--- End Sub List -------------------------------------------------------
		}
	}


	//echo "<item>\n";
	//echo "<title>    ".$row["favname"]."</title>\n";
	//echo "<description>".$row["favdesc"]."</description>\n";
	//echo "<link>".$row["favurl"]."</link>\n";
	//echo "</item>\n";

	//$lastcat = $row["favcat"];
	$count++;

}

if ($contenttype == "rss") {
	echo "</channel>\n";
	echo "\n";
	echo "</rss>\n";
}


?>
