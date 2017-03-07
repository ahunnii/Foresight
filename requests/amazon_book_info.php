<?php 
$suppress_error_redirect = true;
require_once('../../Secure/error_reporting.inc.php');
require_once('../../Secure/db_connect.inc.php');
require_once('../../Secure/aws_constants.inc.php');
require_once('../include/AmazonECS.class.php');

/*
if (isset($_GET['isbn'])) {
	$response = get_book_info_isbn($isbn);
	#echo '<p>ISBN: ' . $_GET['isbn'] . '</p>';
	echo json_encode($response);
	#echo '<p>' . json_last_error() . '</p>';
} else if (isset($_GET['title']) && isset($_GET['author'])) {
	$response = get_book_info($_GET['title'], $_GET['author'], false);
	
	//echo '<p>Title: ' . $_GET['title'] . '</p>';
	//echo '<p>Author: ' . $_GET['author'] . '</p>';
	echo json_encode($response);
}
*/

function get_book_info_isbn($isbn) {
	if (($len = is_isbn_valid($isbn)) !== false) {
		$amazonEcs = new AmazonECS(AWS_API_KEY_ID, AWS_API_SECRET_KEY, 'com', AWS_ASSOCIATE_TAG);
		
		$amazonEcs->returnType(AmazonECS::RETURN_TYPE_ARRAY);

		$response = $amazonEcs->
			responseGroup('Medium')->
			optionalParameters(
				array(
					'IdType' => ( $len == 10 ? 'ISBN' : 'EAN' ), 
					'SearchIndex' => 'Books'
				)
			)->
			lookup($isbn);
		
		return $response;
	}
	
	return false;
}

# Validates an isbn code of 10 or 13 digit format 
function is_isbn_valid($isbn) {
	if (is_string($isbn)) {
		$isbn = preg_replace('/[^0-9xX]+/', '', $isbn);
		
		$len = strlen($isbn);
		
		if ($len == 10) {
			return is_isbn_10_valid($isbn) ? 10 : false;
		} else if ($len == 13) {
			return is_isbn_13_valid($isbn) ? 13 : false;
		}
	} 
	
	return false;
}

function is_isbn_10_valid($ISBN10){
    if(strlen($ISBN10) != 10)
        return false;
 
    $a = 0;
    for($i = 0; $i < 10; $i++){
        if ($ISBN10[$i] == "X"){
            $a += 10*intval(10-$i);
        } else {//running the loop
            $a += intval($ISBN10[$i]) * intval(10-$i);
        }
    }
    return ($a % 11 == 0);
}

function is_isbn_13_valid($n){
	$check = 0;
	for ($i = 0; $i < 13; $i+=2) $check += substr($n, $i, 1);
	for ($i = 1; $i < 12; $i+=2) $check += 3 * substr($n, $i, 1);
	return $check % 10 == 0;
}

# Search for book information using its cached asin 
function get_book_info_asin($asin, $cache) {
	$amazonEcs = new AmazonECS(AWS_API_KEY_ID, AWS_API_SECRET_KEY, 'com', AWS_ASSOCIATE_TAG);
	
	$amazonEcs->returnType(AmazonECS::RETURN_TYPE_ARRAY);

	$response = $amazonEcs->
		responseGroup('Medium')->
		optionalParameters(
			array(
				'SearchIndex' => 'Books'
			)
		)->
		lookup($asin);
	
	if ($response) {
	
		if ($cache) {
			cache_book_info($response);
		}
		
		return $response;
	}
	
	return false;
}

# Perform raw item search
function get_book_info($title, $author, $cache) {
	$amazonEcs = new AmazonECS(AWS_API_KEY_ID, AWS_API_SECRET_KEY, 'com', AWS_ASSOCIATE_TAG);
	
	$amazonEcs->returnType(AmazonECS::RETURN_TYPE_ARRAY);

	$response = $amazonEcs->
		responseGroup('Medium')->
		category('Books')->
		optionalParameters(
			array(
				'Author', $author
			)
		)->
		search($title);

	if ($response) {
		
		if ($cache) {
			cache_book_info($response);
		}
		
		return $response;
	}
	
	return false;
}

# Save book data to db for quick access
function cache_book_info($_id, $data) {
	$item = $data['Items']['Item'];
	$asin = $item['ASIN'];
	$amazon_product_page = escape_data($item['DetailPageURL']);
	$product_image_small = escape_data($item['SmallImage']['URL']);
	$product_image_med = escape_data($item['MediumImage']['URL']);
	$list_price = isset($item['ItemAttributes']['ListPrice']) ? $item['ItemAttributes']['ListPrice']['Amount'] : null;
	$lowest_used_price = $item['OfferSummary']['LowestUsedPrice']['Amount'];
	$lowest_new_price = $item['OfferSummary']['LowestNewPrice']['Amount'];
	
	$result = mysql_query(
		"UPDATE Books 
		SET 
			asin = $asin, 
			amazon_product_page = '$amazon_product_page', 
			product_image_small = '$product_image_small', 
			product_image_med = '$product_image_med', 
			list_price = " . ($list_price == null ? "'NULL'" : "($list_price / 100)") . ", 
			lowest_used_price = ($lowest_used_price / 100), 
			lowest_new_price = ($lowest_new_price / 100), 
			last_amazon_cache = NOW() 
		WHERE 
			_id = $_id 
		LIMIT 1"
	);
}

function get_course_materials($course_id) {
	$result = mysql_query(
		"SELECT b.*, (b.last_amazon_cache IS NOT NULL AND b.last_amazon_cache >= DATE_SUB(NOW(), INTERVAL 1 DAY)) AS up_to_date 
		FROM Book_Refs AS r 
		INNER JOIN Books AS b 
		ON ( 
			r.course_id = $course_id 
			AND b._id = r.book_id 
		) 
		LIMIT 0, 1"
	);
	
	if (mysql_num_rows($result) > 0) {
		$ret = mysql_fetch_array($result, MYSQL_ASSOC);
		
		if ($ret['up_to_date'] != true) {
			$data = get_book_info_isbn($ret['isbn']);
			if ($data) {
				$item = $data['Items']['Item'];
				$ret['asin'] = $item['ASIN'];
				$ret['amazon_product_page'] = $item['DetailPageURL'];
				$ret['product_image_small'] = $item['SmallImage']['URL'];
				$ret['product_image_med'] = $item['MediumImage']['URL'];
				$ret['list_price'] = isset($item['ItemAttributes']['ListPrice']) ? $item['ItemAttributes']['ListPrice']['Amount'] : null;
				$ret['lowest_used_price'] = $item['OfferSummary']['LowestUsedPrice']['Amount'];
				$ret['lowest_new_price'] = $item['OfferSummary']['LowestNewPrice']['Amount'];
				
				cache_book_info($ret['_id'], $data);
				
				#$ret['amazon_ret'] = $data;
			} else {
				$ret = null;
			}
		}
		
	} else {
		$ret = null;
	}
	
	mysql_free_result($result);
	
	return $ret;
}

?>