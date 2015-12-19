<!DOCTYPE html>
<html>
<link rel="stylesheet" type="text/css" href="main.css">

<?php

$url1 = $_POST['url1'];
$url2 = $_POST['url2'];

include("functions.php");

if($url1!=null)
$url1 = "http://pittcult.sis.pitt.edu/home.rss";
if($url2!=null)
$url2 = "http://entertainmentcentralpittsburgh.com/feed/"; //sample url tried out



$xml1 = simplexml_load_file($url1);
$xml2 = simplexml_load_file($url2);
$i = 0;
$j = 0;
$document_collection = array();
$userprofile_vector = generateUserProfileVector($xml1);
$ranks_array = array();
$contents_array = array();
$ranked_documents = array();
$titles_array = array();
$ranked_titles = array();
$d = 0;
//print_r($userprofile_vector);
while($xml2->channel->item[$i]!=null){

	$string = $xml2->channel->item[$i]->description;
	$title = $xml2->channel->item[$i]->title;
	
	$document_vector = createDocumentVector($xml2->channel->item[$i]->description);
	$title_vector = createDocumentVector($xml2->channel->item[$i]->title);
	if($document_vector!=null && $title_vector!=null)
	{$cosine_similarity = calculate_cosine_similarity($userprofile_vector,$document_vector);
	$cooccurence_percent = find_cooccurence_percent($userprofile_vector,$document_vector);
	$title_cooccurence_percent = find_cooccurence_percent($userprofile_vector,$title_vector);

	$rank = $cosine_similarity + $cooccurence_percent + $title_cooccurence_percent;

	$ranks_array[$j] = $rank;
	$contents_array[$j] = $string;
	$titles_array[$j] = $title;
	$j++;
	}
	
	 $i++;
}
arsort($ranks_array);
foreach ($ranks_array as $key => $value) {
	//echo "<br>".$key."==".$value;
	$ranked_documents[$d] = $contents_array[$key];
	$ranked_titles[$d] = $titles_array[$key];
	$d++;
}

for($k=0;$k<sizeof($ranked_documents);$k++){
	echo "<div class=\"doc\"><h2>Ranked no:".($k+1)."<h2><br>";
	echo "<h2>".$ranked_titles[$k]."</h2><br>";
	echo $ranked_documents[$k];
	echo "</div><br>";

}
?>
</html>