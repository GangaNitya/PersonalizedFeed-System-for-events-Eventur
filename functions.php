<?php include("porter.php"); ?>
<?php

function createTokenArray($content){

$tokenarray = array();
 	 $string = $content;
	 $html_stripped_string = strip_tags($string);
	 $quotes_stripped_string = preg_replace("/[^a-zA-Z0-9]+/", " ",html_entity_decode($html_stripped_string,ENT_COMPAT));
	 //echo $quotes_stripped_string;
	 $tokenarray = explode(" ",$quotes_stripped_string);
	
	return $tokenarray;
}
?>
<?php

function createNormalizedArray($token_array){

$stop_words_array = File("stop_words.txt",FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$k = 0;
$normalized_array = array();
for($j=0;$j<sizeof($token_array);$j++){
	$normalized_word = strtolower($token_array[$j]);
		if(in_array($normalized_word,$stop_words_array))
				{//do nothing	
				}
		else
			{
					if(strlen($normalized_word)>1)
						{
							$normalized_array[$k] = $normalized_word;
							$k++;
						}				
			}
		}
return $normalized_array;
}
?>
<?php


function createStemmedArray($normalized_array){
	$stem_words_array = array();
	$s = 0;
for($j=0;$j<sizeof($normalized_array);$j++)
		{
			$stem = PorterStemmer::Stem($normalized_array[$j]);
			$stem_words_array[$s]  = $stem;
			$s++;
		}
return $stem_words_array;
}
?>
<?php
function createDocumentVector($content){
	$token_array = array();
	$normalized_array = array();
	$stem_words_array = array();
	$document_vector = array();
	$key_array = array();
	$values_array = array();
	$token_array = createTokenArray($content);
	$normalized_array = createNormalizedArray($token_array);
	$stem_words_array = createStemmedArray($normalized_array);
//	print_r($stem_words_array);
	$document_array = 	array_count_values($stem_words_array);
	arsort($document_array);
	$key_array = array_keys($document_array);
	$values_array = array_values($document_array);

	for($i=0;$i<sizeof($key_array);$i++){

	$document_vector[$key_array[$i]] = round($values_array[$i]/$values_array[0],4);
}

//	echo "<br>";
//	print_r($document_vector);
	return $document_vector;

}
?>
<?php

function generateUserProfileVector($xml){
$stop_words_array = File("stop_words.txt",FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$normalized_array = array();
$stem_words_array = array();
$i = 0;
$s = 0;
$k = 0;
while($xml->channel->item[$i]!=null){
	$tokenarray = null;
	 $string = $xml->channel->item[$i]->description;
	 $html_stripped_string = strip_tags($string);
	 $quotes_stripped_string = preg_replace("/[^a-zA-Z0-9]+/", " ",html_entity_decode($html_stripped_string,ENT_COMPAT));
	 //echo $quotes_stripped_string;
	 $tokenarray = explode(" ",$quotes_stripped_string);
	for($j=0;$j<sizeof($tokenarray);$j++){
	$normalized_word = strtolower($tokenarray[$j]);
			if(in_array($normalized_word,$stop_words_array))
				{
						unset($tokenarray[$j]);
				}
				else
				{
					if(strlen($normalized_word)>1)
						{
							$normalized_array[$k] = $normalized_word;
							$k++;
						}				
				}

			}

	for($j=0;$j<sizeof($normalized_array);$j++)
		{
			$stem = PorterStemmer::Stem($normalized_array[$j]);
			$stem_words_array[$s]  = $stem;
			$s++;
		}
//	 echo"<br><br><br>";
//	 echo "<h2>".$i."</h2>";
	 $i++;
}

//final printing of frequencies
$freqarray = array_count_values($stem_words_array);
arsort($freqarray);
//print_r($freqarray);
$interesting_array = array_slice($freqarray,0,500,true);
//print_r($interesting_array);
//echo "<br><h1>".sizeof($interesting_array)."</h1>";

$keys_array = array_keys($interesting_array);
$values_array = array_values($interesting_array);
$userprofile_vector = array();

for($i=0;$i<sizeof($keys_array);$i++){

	$userprofile_vector[$keys_array[$i]] = round($values_array[$i]/$values_array[0],4);
}
//print_r($userprofile_vector);

return $userprofile_vector;
}
?>
<?php

function calculate_cosine_similarity($vector1,$vector2){
	$size_vector1 = sizeof($vector1);
$size_vector2 = sizeof($vector2);
$keyarray1 = array_keys($vector1);
$keyarray2 = array_keys($vector2);
$size = ($size_vector1<=$size_vector2)?$size_vector1:$size_vector2;
$numerator = 0;
$v1_sumofsq = 0;
$v2_sumofsq = 0;

for($i=0;$i<$size;$i++){
	//echo $i."~~~";
	$v1 = $vector1[$keyarray1[$i]];
	$v2 = $vector2[$keyarray2[$i]];
	//echo "values=".$v1."-".$v2."<br>";
	$numerator +=  $v1*$v2;
	$v1_sumofsq += pow($v1,2);
	$v2_sumofsq += pow($v2,2);

}

$len1 = sqrt($v1_sumofsq);
$len2 = sqrt($v2_sumofsq);

$denominator = $len1 * $len2;
$cosine_similarity = $numerator/$denominator;
/*echo "<h2>".$numerator."</h2>";
echo "<h2>".$denominator."</h2>";
echo "<h2>Cosine Sim: ".$numerator/$denominator."</h2>";
*/
return $cosine_similarity;
}
?>
<?php
function find_cooccurence_percent($userprofile_vector,$document_vector){
	$count = 0;

	$keys_array_user_vector = array_keys($userprofile_vector);
	$keys_array_doc_vector = array_keys($document_vector);
	for($j=0;$j<sizeof($keys_array_doc_vector);$j++){
	$word = $keys_array_doc_vector[$j];

		if(in_array($word,$keys_array_user_vector))
				{
					$count += 1;
				}
			}

		$cooccurence_percent = $count/sizeof($keys_array_user_vector);
		/*echo "<h1>counts: ".$count." percent:".$cooccurence_percent."</h1>";*/
		return $cooccurence_percent;

}
?>