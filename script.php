<?php

echo "\nStart:\n\n";

/* Attempt MySQL server connection. Replace marker with your desired values */
$mysqli = new mysqli("localhost", "#USERNAME#", "#PASSWORD#", "#DB_NAME#");

// Check connection
if($mysqli->connect_errno){
	die("ERROR: Could not connect. " . $mysqli->connect_error);
}

// Perform query
// $query = "SELECT ID, post_title, post_name, post_content FROM wp_posts WHERE post_status = 'publish' and post_content like '%href=\"https://www.example.com/wp-content/uploads/%'";
$query = "SELECT ID, post_title, post_name, post_content FROM wp_posts WHERE post_status = 'publish' and post_content like '%href=\"/wp-content/uploads/%'";
if ($result = $mysqli->query($query)) {

	// check every post
	while ($row = $result->fetch_assoc()) {
		$content = $row['post_content'];

		// if link directly appears over image tag
		// $pattern = "/<a ?.*href=\"https:\/\/www\.example\.com\/wp-content\/uploads\/.*><img .*<\/a>/";
		$pattern = "/<a ?.*href=\"\/wp-content\/uploads\/.*><img .*<\/a>/";
		preg_match_all($pattern, $content, $matches);

		$ID = $row['ID'];
		if (count($matches[0]) > 0) {
			echo "\nID['".$ID."']: Count > ".count($matches[0])."\n";

			foreach ($matches[0] as $match) {
				$htmlStr = $match;
				$newHtmlStr = substr($htmlStr, strpos($htmlStr,'>')+1, -4);
				$content = str_replace($htmlStr, $newHtmlStr, $content);
			}

			$content = str_replace("'", "\'", $content);
			$updateQuery = "UPDATE wp_posts SET post_content= '$content' WHERE id=".$ID;
			if ($mysqli->query($updateQuery) === TRUE) {
				// file_put_contents($ID.".log", $content);
				echo $ID.": Updated.";
			} else {
				echo $ID.": Error updating record: " . $mysqli->error;
			}
		} else {
			echo "\nID['".$ID."']: No Changes!\n";
		}
	}
	
	// Free result set
	$result->free_result();
}

// Close connection
$mysqli->close();


echo "\n\nExit!\n\n";
