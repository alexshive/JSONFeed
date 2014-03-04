<?php
define('IN_COPPERMINE', true);

// This should work as it is, but hardcode if necessary.
define('CPG15', version_compare(COPPERMINE_VERSION, "1.5.0", ">="));
define('PHP5', version_compare(phpversion(), "5", ">="));

$version = '1';

// total to show
// adjust defaults here
$total = array(
	'photos' => 10,
	'random' => 10,
	'albums' => 99
);

$base = rtrim($CONFIG['ecards_more_pic_target'], '/');
$site_path = $CONFIG['site_url'] . $CONFIG['fullpath'];

$json = array('v' => $version, 'total' => 0, 'current' => array(), 'path' => $site_path, 'category' => array(), 'albums' => array(),'images' => array());

// Grab total photos
$count_query = "SELECT COUNT(pid) AS total FROM {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES'";
$count_result = cpg_db_query($count_query);
$total_count_row = cpg_db_fetch_row($count_result);
$total_count = $total_count_row['total'];

$json['total'] = $total_count;

// $_GET REQUESTS
// Coppermine overrides these for security reasons
$debug = 0;
if($superCage->get->keyExists('debug')) $debug = $superCage->get->getEscaped('debug'); else $debug = 0;

$page = 1;
if($superCage->get->keyExists('page')) $page = $superCage->get->getEscaped('page') - 1; else $page = 0;

// category
$category = 0;
if($superCage->get->keyExists('cid')) (int)$category = $superCage->get->getEscaped('cid');

if($superCage->get->keyExists('totalphotos')) $total['photos'] = $superCage->get->getEscaped('totalphotos');
if($superCage->get->keyExists('totalrandom')) $total['random'] = $superCage->get->getEscaped('totalrandom');
if($superCage->get->keyExists('totalalbums')) $total['albums'] = $superCage->get->getEscaped('totalalbums');

$album_id = 0;
if($superCage->get->keyExists('aid')) $album_id = $superCage->get->getEscaped('aid');

$start = $page * $total['photos'];

//if($category == 0 && !$album_id) {
if($album_id === 0) {
// grab categories
	$query = "SELECT cid,name,thumb FROM {$CONFIG['TABLE_CATEGORIES']} WHERE parent = '".$category."' ORDER BY `name`";
	$result = cpg_db_query($query);
	$cat_data = cpg_db_fetch_rowset($result);
	
	if($cat_data) 
		foreach($cat_data as $category_thumb_array) 
			if($category_thumb_array['thumb'] > 0) $thumb_array[] = $category_thumb_array['thumb'];
	
	if($thumb_array > 0):
		$thumb_query = "SELECT pid,filepath,filename FROM {$CONFIG['TABLE_PICTURES']} WHERE";
		$thumb_counter = 0;
		foreach($thumb_array as $thumb_id):
			$thumb_query .= " pid = '".$thumb_id."'";
			$thumb_counter += 1;
			if($thumb_counter < count($thumb_array)) {
				$thumb_query .= ' OR ';
			}
		endforeach;
		$thumb_result = cpg_db_query($thumb_query);
		$thumb_data = cpg_db_fetch_rowset($thumb_result);
		//print_r($thumb_data);
	endif;
	
	if($cat_data) {
		foreach($cat_data as $category_array) {
			if($thumb_data)
				foreach($thumb_data as $thumb_data_array)
					if($thumb_data_array['pid'] == $category_array['thumb'])
						$category_array['thumb'] = $thumb_data_array['filepath'] . "thumb_" . $thumb_data_array['filename'];
					else
						$category_array['thumb'] = false;
			$json['category'][] = $category_array;
		}
	}
}

// select media when in album
if ($album_id > 0 || $category > 0) {
	
	if ($category == 0) {
		//get_meta_album_set(0);

		// In case of metaalbums, the category id gets negative value which is actually the album id. So, first get the correct category id
		// $aid = -($category);
		$query = "SELECT title, category FROM {$CONFIG['TABLE_ALBUMS']} WHERE aid = '$album_id'";
		$result = cpg_db_query($query);

		$row = cpg_db_fetch_rowset($result);
		$category = $row[0]['category'];
		$album_title = $row[0]['title'];

	}

	$query = "SELECT pid,aid,filepath,filename,url_prefix,pwidth,pheight,filesize,ctime,title FROM {$CONFIG['TABLE_PICTURES']} WHERE aid = '".$album_id."' AND approved = 'YES' ORDER BY pid DESC LIMIT " . $start . ", " .$total['photos'];

	// look ahead to find out how many total there are, only SQL can do this
	$count_query = "SELECT COUNT(pid) AS total FROM {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES' AND aid = '".$album_id."' ";
	$count_result = cpg_db_query($count_query);
	$count_result_row = cpg_db_fetch_row($count_result);
	$total_count_album = $count_result_row['total'];
	
	$start_count = $start;
	if($start_count == 0) $start_count = $total['photos'];
	if($start_count < $total_count_album) $json['more'] = true;
			
	$result = cpg_db_query($query);
	$pic_data = cpg_db_fetch_rowset($result);

} else {
	// grab photos
	get_meta_album_set(0);
	
	$query = "SELECT pid,aid,filepath,filename,url_prefix FROM {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES' ORDER BY pid DESC LIMIT " . $start . ", " .$total['photos'];
	
	$start_count = $start;
	if($start_count == 0) $start_count = $total['photos'];
	if($start_count < $total_count) $json['more'] = true;
			
	$result = cpg_db_query($query);
	$pic_data = cpg_db_fetch_rowset($result);
	
	// RANDOM IMAGES
	$random_query = "SELECT pid,filepath,filename,url_prefix FROM {$CONFIG['TABLE_PICTURES']} WHERE approved = 'YES' ORDER BY RAND() LIMIT 0 ," . $total['random'];
	$random_result = cpg_db_query($random_query);
	$random_pic_data = cpg_db_fetch_rowset($random_result);
	if($random_pic_data) {
		$results = array();
		foreach ($random_pic_data as $row) {
			$results['id'] = $row['pid'];
			$results['full'] = $base . "/".get_pic_url($row);
			$results['thumb'] = $base . "/".get_pic_url($row, 'thumb');
			$json['random'][] = $results;
		}
	}

	// LATEST ALBUMS
	$latest_album_query = "SELECT r.aid, a.thumb, a.title, MAX(ctime) AS ctime FROM {$CONFIG['TABLE_PICTURES']} AS r INNER JOIN {$CONFIG['TABLE_ALBUMS']} AS a ON a.aid = r.aid WHERE (1) AND approved = 'YES' GROUP BY r.aid ORDER BY ctime DESC LIMIT 0 ," . $total['albums'];
	// echo $latest_album_query;
	$latest_album_result = cpg_db_query($latest_album_query);
	$latest_album_data = cpg_db_fetch_rowset($latest_album_result);
	if($latest_album_data) {
		$results = array();
		foreach ($latest_album_data as $row) {
			$album_thumb_query = "SELECT pid,filepath,filename,url_prefix FROM {$CONFIG['TABLE_PICTURES']} WHERE ((aid = '{$row['aid']}' ) ) AND approved='YES' ORDER BY ctime DESC LIMIT 0,1";
			$album_thumb_result = cpg_db_query($album_thumb_query);
			$album_thumb_data = cpg_db_fetch_row($album_thumb_result);
			$results['aid'] = $row['aid'];
			$results['title'] = $row['title'];
			$results['thumb'] = $base . "/".get_pic_url($album_thumb_data, 'thumb');
			$json['latest_albums'][] = $results;
		}
	}

	
} //end getting album

// get breadcrumps of categories
if($category > 0) {
	$query = "SELECT p.cid, p.name FROM {$CONFIG['TABLE_CATEGORIES']} AS c, {$CONFIG['TABLE_CATEGORIES']} AS p WHERE c.lft BETWEEN p.lft AND p.rgt AND c.cid = {$category} ORDER BY p.lft";
	$result = cpg_db_query($query);
	$category_data = cpg_db_fetch_rowset($result);
	if($category_data) {
		foreach($category_data as $category_data_row) {
			$json['current']['category'][] = $category_data_row;
		}
	}
}

// grab albums from category
if($category > 0) {	
	$query = "SELECT aid,title FROM {$CONFIG['TABLE_ALBUMS']} WHERE category = '".$category."' ORDER BY `title`";
	$result = cpg_db_query($query);
	$album_data = cpg_db_fetch_rowset($result);
	if($album_data) {
		foreach($album_data as $album) {
			$json['albums'][] = $album;
		}
	}
}

// Set JSON
if($pic_data) {
	foreach ($pic_data as $row) {
		$results['id'] = $row['pid'];
		$results['full'] = $base . "/".get_pic_url($row);
		$results['thumb'] = $base . "/".get_pic_url($row, 'thumb');
		$json['images'][] = $results;
	}
}

if($debug > 0) {
	$query_total_time = 0;
	echo "<pre>";
	foreach($query_stats as $query_time) {
		$query_total_time += $query_time;
	}
	$json['query_time'] = '<b>' . strval($query_total_time) . 'ms</b> for <b>' . count($query_stats) . '</b> queries';
	$json['queries'] = $queries;
	print_r($json);
	echo "</pre>";
} else {
	echo json_encode($json);
}
