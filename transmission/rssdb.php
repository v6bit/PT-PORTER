<?php
include_once('rss.class.php');
include_once('html2ubb.php');
include_once('dbconfig.php');

$tail="&passkey=292f839cc1c4dcd3da580eb23963e105";
//$url="http://chdbits.org/torrentrss.php?rows=10&sta3=1&ismalldescr=1";
//$url_detail='http://chdbits.org/torrentrss.php?rows=10&linktype=dl&passkey=xxxxxxx';

//$pattern='/http:\/\/222.199.184.28\/details.php\?id=([0-9]+)/i';
$pattern='/\/details.php\?id=([0-9]+)/i';
$pimdb='/http:\/\/www.imdb.com\/title\/tt([0-9]+)/i';
$pattern_name='/\[(.*?)\]$/';

 dbconn();
 
 $url=get_rss_source();


 if(mysql_affected_rows())

 {      
	 while($url_item=mysql_fetch_array($url))
	 {
		  echo "parsing rss source:".$url_item['note']." :".$url_item['url']."\n";
		  $rss=new ReadRSS($url_item['url']);
		  $values=$rss->RSS(50);
		  //print_r($values);
		  $row = array();
	  
		  foreach ($values as $value_item)
		  {
			 
			 if(preg_match($pattern,$value_item['link'],$matches))
			 {
				  $tid=$matches[1];
			 }
			 else{
				  $tid=0;
				  }
			 $row['id']="";
			 $row['doubanid']=$value_item['doubanid'];
			 $row['tid']=$tid;
			 $row['filename']=$value_item['title'];
			 $row['name']=$value_item['title'];
			 $small_descr='';
			 if(preg_match($pattern_name,$value_item['title'],$matches))
			 {
				  $small_descr=$matches[1];
			 }
			 else{
				  $small_descr=$value_item['title'];
				  }
			 $row['small_descr']=$small_descr;
			 $row['name']=str_replace("[".$small_descr."]",'',$row['name']);           
			 $row['url']=$value_item['link'];
			 $row['dl_url']=$value_item['enclosure_url'].$tail;
			 $row['length']=$value_item['enclosure_length'];
			 $row['descr']=html2ubb($value_item['description']);
			 $row['type']='401';
			  
			  if(preg_match($pimdb,$value_item['description'],$matches))
			 {
				  $imdb=$matches[0];
			 }
			 else{
				  $imdb=" ";
				  }
			 
			 $row['imdb']=$imdb;
			 $row['downloaded']=0;
			 $row['uploaded']=0;
			 $row['completed']=0;
			 $row['hash']='';
			 
			 
			 //print_r($row);
			
			 $ret=get_torrent_bytid($row['tid']);
			 if(mysql_affected_rows())
			 {
				 printf("torrent ".$row['name']."alreay exist \n");
			  }else
			 	{
				  insert_torrent($row);
				  echo "add torrents name:".$row['name']."small name: ".$row['small_descr']."\n";
				  }
			  
			  mysql_free_result($ret);
	  
		}
 	}
 }
 
 mysql_free_result($url);
?>

