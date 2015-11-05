<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
<body>
<?php

//error_reporting(0);
set_time_limit(0);

echo "-- script started --";
flush();


function getTime()
    {
    $a = explode (' ',microtime());
    return(double) $a[0] + $a[1];
    }
$Start = getTime();


$kodovani = "utf-8";

	  $spojeni = mysql_pconnect('localhost','mta','*****');
    mysql_select_db("netdevel_mta", $spojeni);
    
    //$spojeni = mysql_pconnect('localhost','root','root');
    //mysql_select_db("mta_mapy", $spojeni);

    mysql_query("SET NAMES '".$kodovani."'");
    mysql_query("SET OPTION CHARACTER SET ".$kodovani."");
    mysql_query("SET CHARACTER SET '".$kodovani."'");

$opts = array(
  'http'=>array(
    'method'=>"GET",
    'header'=>"Accept-language: en\r\n" .
             "Referer: http://community.mtasa.com"
  )
);
$context = stream_context_create($opts);

$from = 0; // 0
$to = 3400; // 3395

for ($i = $from; $i <= $to; $i++) {


if(($i % 100) == 0){
		echo $i." done - ";
    flush();
}


$url = "http://community.mtasa.com/index.php?p=resources&s=details&id=" . $i;
$content = file_get_contents($url);



preg_match_all("#rss</a></span>(.*?)[\r\n]#", $content, $name);


if (isset($name[1][0])) {
    preg_match_all("#<tr><th>Category:</th><td>(.*?)</td></tr>#", $content, $ismap);
    
	     if ($ismap[1][0] == "map") {
	     
	     // author
	         preg_match_all("#<tr><th>Author:</th><td><a href(.*?)>(.*?)</a></td></tr>#", $content, $author);
       // downloads
	         preg_match_all("#<tr><th>Downloads:</th><td>(.*?)</td></tr>#", $content, $downloads);
       // rating
	         preg_match_all("#<tr><th>Rating:</th><td id=\"resrating\">(.*?)[\r\n]#", $content, $rating);
       // desc
	         preg_match_all("#<div id=\"resdescr\">(.*?)<script#msU", $content, $desc);
      // date, version, gamemode, changes
					 $exp = "#<tr class=\"row1\"><td class=\"history_newest\">(.*?)</td><td>(.*?)</td><td>(.*?)</td><td>#msU";
	         preg_match_all($exp, str_replace(array("\r\n", "\r", "\n", "\t"), '', $content), $other);





if (strpos($content, "Gamemodes for this map")) {
	$piece = explode("</td><td>", $other[1][0]);
	$gamemode = $other[3][0];
	$releasenotes = $piece[2];
	$version = $piece[0];
	$date = $piece[1];
}
else {
	$releasenotes = $other[3][0];
	$version = $other[1][0];
	$date = $other[2][0];

	$gamemode = ""; // gamemode
}


if (strpos($version, "</td><td>")) {
$vp = explode("</td><td>", $version);
$version = $vp[0];
}



 //var_dump($version);


     			 preg_match_all("#<a href=\"(.*?)\">Download latest version</a>#", $content, $link);
     			 $content2 = file_get_contents("http://community.mtasa.com/index.php" . str_replace("&amp;", "&", $link[1][0]));
           preg_match_all("#If it did not click <a href=\"(.*?)\" onClick#", $content2, $link2);
           
          if(trim($link2[1][0]) != ""){
	           $down = "http://community.mtasa.com/" . str_replace(" ","%20",$link2[1][0]);
	           $content3 = file_get_contents($down, false, $context);
	           $handle = fopen("./maps/".$i.".zip", "w+");
	           fwrite($handle, $content3);
	           fclose($handle);

							$q = "INSERT INTO maps VALUES ('', ".$i.", '".mysql_real_escape_string(trim($name[1][0]))."', '".mysql_real_escape_string(trim($author[2][0]))."', '".mysql_real_escape_string(trim($downloads[1][0]))."', '".intval($rating[1][0])."', '".mysql_real_escape_string(str_replace(array("\r\n", "\r", "\n", "\t"), ' ', trim(strip_tags($desc[1][0]))))."', '".mysql_real_escape_string($down)."', '".mysql_real_escape_string($date)."', '".mysql_real_escape_string($version)."', '".mysql_real_escape_string($gamemode)."', '".mysql_real_escape_string($releasenotes)."')";
							
							//echo $q."<hr/>";

	           $query = mysql_query($q, $spojeni);


if(mysql_error($spojeni) != ""){
echo mysql_error($spojeni)."<hr />";
}



	        }
           

			 }
	
}

}



echo " - OK - " . ($to - $from) . " items done!";

$End = getTime();
echo " - Time taken = ".number_format(($End - $Start),2)." secs";

?>

</body>
</html>
