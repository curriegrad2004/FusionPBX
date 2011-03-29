<?php
	$apps[$x]['name'] = "Content Manager";
	$apps[$x]['guid'] = '892C8D0B-BFA5-1BDF-E090-A974DA7A7C5E';
	$apps[$x]['category'] = 'System';
	$apps[$x]['subcategory'] = '';
	$apps[$x]['version'] = '';
	$apps[$x]['menu'][0]['title']['en'] = 'Content Manger';
	$apps[$x]['menu'][0]['guid'] = '90397352-395C-40F6-2087-887144ABC06D';
	$apps[$x]['menu'][0]['parent_guid'] = '594D99C5-6128-9C88-CA35-4B33392CEC0F';
	$apps[$x]['menu'][0]['category'] = 'internal';
	$apps[$x]['menu'][0]['path'] = '/mod/content/rsslist.php';
	$apps[$x]['menu'][0]['groups'][] = 'user';
	$apps[$x]['menu'][0]['groups'][] = 'admin';
	$apps[$x]['menu'][0]['groups'][] = 'superadmin';
	$apps[$x]['permissions'][] = 'content_view';
	$apps[$x]['permissions'][] = 'content_add';
	$apps[$x]['permissions'][] = 'content_edit';
	$apps[$x]['permissions'][] = 'content_delete';
	$apps[$x]['license'] = 'Mozilla Public License 1.1';
	$apps[$x]['url'] = 'http://www.fusionpbx.com';
	$apps[$x]['description']['en'] = 'Manage Content for any page in the interface.';
?>