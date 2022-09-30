<?php
set_time_limit(1000); 

$crawlUrl = "https://www.britannica.com/topic/wiki"; // CRAWL URL
$pageCrawl = 5; // LIMIT NUMBER OF PAGES TO CRAWL

$check_url_crawled = array();
$crawl_data = array();


function checkUrlType($url){
	$check_url = preg_match("@^https?://@", $url);
	return $check_url == 1 ? true : false;
}

function startloadTimer(){
	$start_time = microtime(TRUE);
	return $start_time;
}


function endLoadTimer(){
	$end_time = microtime(TRUE);
	return $end_time;
}


function avgTime($start, $end){
	$time_result = ( $start - $end ) * 1000;
	$time_result = round( $time_result, 5 );
	return $time_result;
}

// Scrape Html Elements
function scraped_info($url, $baseUrl) { 

	$options = array('http'=>array('ignore_errors' => true, 'method'=>"GET", 'headers'=>"User-Agent: howBot/0.1\n"));

	$href = $styles = $js = $internal=$external=$photo = array(); 

	$context = stream_context_create($options);

	$dom = new DOMDocument(); // Initialize DOM

	$start = microtime(true);
	$fgets = @file_get_contents($url, false, $context); 
	@$dom->loadHTML($fgets); // Load HTML Elements
	
	$avg_load_time = microtime(true)-$start;
	$search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
           '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
           '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments including CDATA
	);
	$contents = preg_replace($search, '', $fgets); 
	$word_count = array_sum(array_count_values(str_word_count(strip_tags($contents), 1))); // Count All Words From Page..

	$links = $dom->getElementsByTagName("a"); 
	$schemas = $dom->getElementsByTagName("link"); 
	$scripts = $dom->getElementsByTagName("script");
	$images = $dom->getElementsByTagName("img");

	// Filter Image Links
	foreach($images as $image){
		$photo[] = urlencode($image->getAttribute("src"));
	}

	// Filter src Links From Href
	foreach($links as $link){
		$href[] = urlencode($link->getAttribute("src"));
	}

	// Style Links
	foreach($schemas as $schema){
		$styles[] = $schema->getAttribute("href");
	}

	// Script Tag Links
	foreach($scripts as $script){
		$js[] = $script->getAttribute("src");
		
	}
	
	return [
		'url' => urlencode($url), 
		'images' => array_unique($photo), 
		'load' => $avg_load_time,
		'word_count' => $word_count,
		'status_code' => $http_response_header
	];

}


function crawler($url, $avgUrl) {

	global $check_url_crawled;
	global $crawl_data;

	$options = array('http'=>array('method'=>"GET", 'headers'=>"User-Agent: howBot/0.1\n"));

	$crawl_result = array();

	$count = 0;

	$context = stream_context_create($options);

	$dom = new DOMDocument();

	@$dom->loadHTML(@file_get_contents($url, false, $context));

	$linklist = $dom->getElementsByTagName("a"); // GET PAGE URL LINKLIST

	foreach ($linklist as $link) {

		$l =  $link->getAttribute("href");

		// Verify HTTPS URL
		$check_url = preg_match("@^https?://@", $l);
		if($check_url == 0){
			if (substr($l, 0, 1) == "/" && substr($l, 0, 2) != "//") {
				$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].$l;
			} else if (substr($l, 0, 2) == "//") {
				$l = parse_url($url)["scheme"].":".$l;
			} else if (substr($l, 0, 2) == "./") {
				$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].dirname(parse_url($url)["path"]).substr($l, 1);
			} else if (substr($l, 0, 1) == "#") {
				$l = parse_url($url)["scheme"]."://".parse_url($url)["host"].parse_url($url)["path"].$l;
			} else if (substr($l, 0, 3) == "../") {
				$l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
			} else if (substr($l, 0, 11) == "javascript:") {
				continue;
			} else if (substr($l, 0, 5) != "https" && substr($l, 0, 4) != "http") {
				$l = parse_url($url)["scheme"]."://".parse_url($url)["host"]."/".$l;
			}
			if($l !== $url && $count <=	 $avgUrl){
				if (!in_array($l, $check_url_crawled)) {
						$check_url_crawled[] = $l;
						$crawl_data[] = $l; // Get All Related URL to CRAWL
						
						$crawl_result[] = scraped_info($l, $url);
				}
			}
			
		}
		$count++;
	}

	// CRAWL OTHER PAGES
	array_shift($crawl_data);
	foreach ($crawl_data as $site) {
		crawler($site, $avgUrl);
	}

	return json_encode($crawl_result); // Return Crawled Results

}


// Begin the crawl_data process by crawl_data, the starting link first.
$json = json_decode(crawler($crawlUrl, $pageCrawl));
