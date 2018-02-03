
<?php

// extra security
//if(!isset($_GET['autosecret']) || $_GET['autosecret']!='1secret'){
//	exit("Permission denied");
//}


$debug=false;
if( isset($_REQUEST['debug']) && ($_REQUEST['debug']=="true" || $_REQUEST['debug']=="1")){
    $debug=true;
}

#error_reporting(E_STRICT);
date_default_timezone_set('Europe/Madrid');


$FIREBASE='https://when-will-bitcoin-bubble-burst.firebaseio.com/';


$curl = curl_init();
curl_setopt( $curl, CURLOPT_URL, $FIREBASE . 'predictions.json' );
curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
$response = curl_exec( $curl );
curl_close( $curl );
$predictions=json_decode($response,true);

$timestamp_date=date("Y-m-d");
$timestamp=date("Y-m-d H:i");
$timestampH=date("H");


$url_and_query='https://finance.google.com/finance?q=CURRENCY:BTC';
$curl = curl_init();
curl_setopt( $curl, CURLOPT_URL, $url_and_query );
curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
$response = curl_exec( $curl ); //utf8_decode( not necessary
curl_close( $curl );
    $response=preg_replace("/(\n|&nbsp;)/", " ", $response);
    $response=preg_replace("/1\s+BTC\s+=\s+([^U]+)USD[^\(]*\(([^%]*)%/", "\n1BTC=$1\n1curr_change=$2\n", $response);
    $response=preg_replace("/^[^1].*$/m", "", $response); // needs m to work
    $response=preg_replace("/<[^>]*>/", "", $response);
    //echo "aaa.<pre>".htmlspecialchars($response)."</pre>";
    preg_match("/^1BTC=([0-9,.]*)\s*/m", $response, $btcusdval);
    $btcusdval=str_replace(",","",$btcusdval[1]);
    preg_match("/^1curr_change=([0-9,.-]*)\s*/m", $response, $change);
    $change=str_replace(",","",$change[1]);
$btcusd=floatval($btcusdval);
$btcusd_change=floatval($change); ///100;
echo "<br />btcusd=$btcusd<br />";
echo "<br />btcusd change=$btcusd_change<br />";
echo date('Y-m-d H:i:s')." ending stock_curl_btcusd.php<br />";


$return_arr=array();
$total_votes=0;

foreach ($predictions as $pred_date => $prediction) {
    //echo $pred_date;
    $total_votes+=intval($prediction['votes']);
}

function cmp($a, $b){
    return strcmp(str_pad($a["votes"], 8, '0', STR_PAD_LEFT), str_pad($b["votes"], 8, '0', STR_PAD_LEFT));
}
usort($predictions, "cmp");
//echo print_r($predictions[count($predictions)-1]);

$return_arr['total_votes']=$total_votes;
$return_arr['top1']=$predictions[count($predictions)-1];
$return_arr['top2']=$predictions[count($predictions)-2];
$return_arr['top3']=$predictions[count($predictions)-3];
$return_arr['top4']=$predictions[count($predictions)-4];
$return_arr['top5']=$predictions[count($predictions)-5];
// Make them percentages
$return_arr['top1']['votesp']=floor(intval($return_arr['top1']['votes'])*100/intval($return_arr['total_votes']));
$return_arr['top2']['votesp']=floor(intval($return_arr['top2']['votes'])*100/intval($return_arr['total_votes']));
$return_arr['top3']['votesp']=floor(intval($return_arr['top3']['votes'])*100/intval($return_arr['total_votes']));
$return_arr['top4']['votesp']=floor(intval($return_arr['top4']['votes'])*100/intval($return_arr['total_votes']));
$return_arr['top5']['votesp']=floor(intval($return_arr['top5']['votes'])*100/intval($return_arr['total_votes']));
echo  'percentage 1:'.$return_arr['top1']['votes'];
$return_arr['btc_price']=floor(floatval($btcusd));
$return_arr['btc_change']=round(floatval($btcusd_change), 1);;
$return_arr['last_update']=$timestamp;
$bbb_json_str=json_encode( $return_arr );

$bbb_html = file_get_contents('./index.html');
$bbb_html = preg_replace('/bbb=[^;]*;/', 'bbb='.$bbb_json_str.';', $bbb_html);


// update html directly
echo date('Y-m-d H:i:s')." updating index.html directly\n";
$bbbfile = fopen("index.html", "w") or die("Unable to open file index.html!");
fwrite($bbbfile, $bbb_html);
fclose($bbbfile);

// update bbb.json
/*echo date('Y-m-d H:i:s')." updating bbb.json\n";
$bbbfile = fopen("bbb.json", "w") or die("Unable to open file bbb.json!");
fwrite($bbbfile, $bbb_json_str);
fclose($bbbfile);


$test='
blabla
bbb=dadada da;
bbb=dadada da;\n
bbb=dadada da;
bababa

aaa
';
$test = preg_replace('/bbb=.*;(.*)/', 'bbb='.$bbb_json_str, $test);
echo $test;
*/

?>