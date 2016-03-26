<?php

/**
 * Slim Starter v1 (OLD EXAMPLE API)
 */

require 'vendor/autoload.php';
require 'config.php';
require 'Database.php';

// run Slim app
$app = new \Slim\Slim();

/**
 * Routes
 */
$app->get('/', 'hello');
$app->post('/users', 'registerUser');
$app->get('/users', 'getUsers');
$app->get('/users/:id', 'getUser');
$app->group('/user', function() use ($app) {
  $app->get('/:id', 'getUser');
  // $app->put('/:id', 'updateUser');
  // $app->delete('/:id', 'deleteUser');
});
$app->post('/page-sponsors', 'addPageSponsor');
$app->get('/bases', 'getBases');
$app->get('/bases/:id/listings', 'getListingsByBaseId');
$app->get('/bases/:id/messages', 'getBaseMessages');
$app->get('/bases/:id/sponsors', 'getBaseSponsors');
$app->get('/branches/:id/bases', 'getBasesByBranchId');
$app->get('/branches/:id/listings', 'getListingsByBranchId');
$app->get('/branches/:id', 'getBranchesById');
$app->get('/branches', 'getBranches');
$app->get('/listings/nearme/:lat/:long/:distance', 'getListingsNearMe');
$app->get('/listings', 'getListings');
$app->get('/listings/search/:type/:query', 'searchListings');
$app->get('/lennieList', 'getLennie');
$app->post('/login', 'login');

$app->get('/listingsbycategory/:id', 'getListingsByCategoryId');
$app->get('/listingsbysubcategory/:id', 'getListingsBySubcategoryId');
$app->get('/categories', 'getCategories');
$app->get('/category-tree', 'getCategoryTree');
$app->get('/categoriesandsubcategories', 'getCategoryTree'); // replaced with category-tree on 2/26/2015, GuideOn 0.0.6
$app->get('/subcategories/:id', 'getSubcategoryByCategoryId');
$app->get('/registerapplepushdevice/:devicekey/:baseid/:userid', 'registerApplePushDeviceBaseUser');
$app->get('/registerapplepushdevice/:devicekey/:baseid', 'registerApplePushDevicebase');
$app->get('/registerapplepushdevice/:devicekey', 'registerApplePushDevice');
$app->get('/registerandroidpushdevice/:devicekey/:baseid/:userid', 'registerAndroidPushDeviceBaseUser');
$app->get('/registerandroidpushdevice/:devicekey/:baseid', 'registerAndroidPushDevicebase');
$app->get('/registerandroidpushdevice/:devicekey', 'registerAndroidPushDevice');
$app->get('/messagesByPushID/:pushid/:baseid', 'messagesByBasePushID');
$app->get('/messagesByPushID/:pushid', 'messagesByPushID');
$app->get('/messages/:id/read', 'readMessageByID');
$app->get('/messages/:id/delete', 'deleteMessageByID');
$app->get('/nogos', 'getNoGo');


/* --------------------------------------------------------------------------
Run Slim
-------------------------------------------------------------------------- */
$app->run();


/* --------------------------------------------------------------------------
Functions
-------------------------------------------------------------------------- */

/**
 * Hello GET
 */
function hello() {
  $template = <<<EOT
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>Slim Starter v1</title>
  <style>
    body {
      background: #EDEDED;
      color: #A8A8A8;
      text-align: center;
    }
  </style>
</head>
<body>
  <h1>Slim Starter v1</h1>
</body>
</html>
EOT;
  echo $template;
}

/**
 * Register POST
 */
function registerUser() {
  $request = \Slim\Slim::getInstance()->request();
  $register = json_decode($request->getBody());
  // TODO: validate user data before insert

  // default to user_role_id = 3 (sponsor)
  // TODO: generate password and include in sql
	$sql = "INSERT INTO users (id, name, email, password, user_role_id, agreed_terms_privacy, enabled) VALUES (NULL, :name, :email, :password, 3, UTC_TIMESTAMP(), 1)";
  try {
    $db = getDB();
    $stmt = $db->prepare($sql);

    $stmt->bindParam("name", $register->name);
    $stmt->bindParam("email", $register->email);
    $stmt->bindParam("password", $register->password);

    $stmt->execute();
    $user->id = $db->lastInsertId();
    $db = null;
    echo json_encode($user);
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

/**
 * Login POST
 */
function login() {
  $request = \Slim\Slim::getInstance()->request();
  $credentials = json_decode($request->getBody());
//   $sql = "SELECT * FROM users where email = :email AND password = :password AND enabled = 1";
  $sql = "SELECT users.*, user_roles.discount_percent, user_roles.user_role FROM users, user_roles WHERE users.user_role_id = user_roles.id AND users.email = :email AND users.password = :password AND users.enabled = 1 ORDER BY users.name ASC";
  try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("email", $credentials->email);
    $stmt->bindParam("password", $credentials->password);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_OBJ);

    if ( $user ) {
    	$db = null;
    	echo '{"data": ' . json_encode($user) . '}';
    }
    else {
    	$db = null;
    	echo '{"error":{"text":'. 'Login failed' .'}}';
    }

  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}



/**
 * Base Sponsors GET
 */
function getBaseMessages($id) {
  $sql = "SELECT messages.*, users.name_first AS from_name_first, users.name_last AS from_name_last, users.avatar_thumbnail AS from_avatar_thumbnail FROM messages, users WHERE base_id = :id AND messages.user_id = users.id AND scheduled < UTC_TIMESTAMP()";
  try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $id);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_OBJ);

//     $sponsors_obj = new stdClass();
//     foreach ($messages as $message) {
//     	$page_name = strtolower($sponsor->page_name);
//     	$page_name = str_replace(' ', '_', $page_name);
//     	$sponsors_obj->$page_name = $sponsor;
//     }
//     $sponsors = $sponsors_obj;

    $db = null;
    echo '{"data": ' . json_encode($messages) . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

/**
 * Base Sponsors GET
 */
function getBaseSponsors($id) {
  $sql = "SELECT page_sponsors.*, pages.name AS page_name FROM page_sponsors, pages WHERE page_sponsors.status = 1 AND page_sponsors.base_id = :id AND page_sponsors.start_date <= UTC_DATE() AND page_sponsors.end_date >= UTC_DATE() AND page_sponsors.page_id = pages.id ORDER BY page_sponsors.page_id ASC, page_sponsors.start_date ASC";
  try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $id);
    $stmt->execute();
    $sponsors = $stmt->fetchAll(PDO::FETCH_OBJ);

    $sponsors_obj = new stdClass();
    foreach ($sponsors as $sponsor) {
    	$page_name = strtolower($sponsor->page_name);
    	$page_name = str_replace(' ', '_', $page_name);
      // $sponsors_obj->$page_name = $sponsor;
      if ( empty($sponsors_obj->pages->$page_name) ) {
        $sponsors_obj->pages->$page_name = array();
      }
      array_push($sponsors_obj->pages->$page_name, $sponsor);

    }
    $sponsors = $sponsors_obj;

    $db = null;
    echo '{"data": ' . json_encode($sponsors) . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

// function getBaseSponsors($id) {
//   $sql = "SELECT base_id, page_name, start_date, qty_months, link_url, image FROM temp_page_sponsors WHERE base_id = :id AND start_date < UTC_DATE()";
//   try {
//     $db = getDB();
//     $stmt = $db->prepare($sql);
//     $stmt->bindParam("id", $id);
//     $stmt->execute();
//     $sponsors = $stmt->fetchAll(PDO::FETCH_OBJ);

//     $sponsors_obj = new stdClass();
//     foreach ($sponsors as $sponsor) {
//       $page_name = strtolower($sponsor->page_name);
//       $page_name = str_replace(' ', '_', $page_name);
//       $sponsors_obj->$page_name = $sponsor;
//     }
//     $sponsors = $sponsors_obj;

//     $db = null;
//     echo '{"data": ' . json_encode($sponsors) . '}';
//   } catch(PDOException $e) {
//     //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
//     echo '{"error":{"text":'. $e->getMessage() .'}}';
//   }
// }

function searchListings($type, $query) {
  if ($type == 'keyword') {
  	$sql = "SELECT * FROM listings_new_temp WHERE name LIKE :query OR address_1 LIKE :query ORDER BY sponsored DESC, name LIMIT 20";
  }
  else if ($type == 'building') {
  	$sql = "SELECT * FROM listings_new_temp WHERE building_number != 0 AND building_number LIKE :query ORDER BY sponsored DESC, building_number LIMIT 20";
  }
  try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("query", $query);
    if ($type == 'keyword') {
			$stmt->execute( array("query"=>'%'.$query.'%') );
		}
		else if ($type == 'building') {
			$stmt->execute( array("query"=>$query.'%') );
		}
    $stmt->execute();
    $search_results = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo '{"data": ' . json_encode($search_results) . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

/**
 * Search (Keyword & Building #) POST
 */
// function searchListings_prev() {
//   $request = \Slim\Slim::getInstance()->request();
//   $search = json_decode($request->getBody());
//   if ($search->type === 'keyword') {
//   	$sql = "SELECT * FROM listings WHERE name LIKE :query OR address_2 LIKE :query";
//   }
//   else if ($search->type === 'building') {
//   	$sql = "SELECT * FROM listings WHERE building_number = 'TRUE' AND address_1 LIKE :query";
//   }
//   try {
//     $db = getDB();
//     $stmt = $db->prepare($sql);
//     $stmt->bindParam("query", $search->query);
// 		$stmt->execute( array("query"=>'%'.$search->query.'%') );
//     $stmt->execute();
//     $search_results = $stmt->fetchAll(PDO::FETCH_OBJ);
//     $db = null;
//     echo '{"data": ' . json_encode($search_results) . '}';
//   } catch(PDOException $e) {
//     //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
//     echo '{"error":{"text":'. $e->getMessage() .'}}';
//   }
// }








/*
 * Bases By Branch GET
 */
function getBasesByBranchId($id) {
  $sql = "SELECT * FROM bases WHERE branch_id = :id";
  try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $id);
    $stmt->execute();
    $bases = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo '{"data": ' . json_encode($bases) . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

/*
 * Listings By Branch GET
 */
function getListingsByBranchId($id) {
	// TODO: MAKING LISTINGS BY BRANCH LIVE
  $sql = "SELECT * FROM bases WHERE branch_id = :id AND 1 == 2";
  try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $id);
    $stmt->execute();
    $bases = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo '{"data": ' . json_encode($bases) . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

/*
 * Branches by ID
 */
function getBranchesById($id) {
  $sql = "SELECT * FROM branches WHERE id = :id";
  try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $id);
    $stmt->execute();
    $bases = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo '{"data": ' . json_encode($bases) . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

/*
 * Branches
 */
function getBranches() {
  $sql = "SELECT * FROM branches";
  try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $bases = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo '{"data": ' . json_encode($bases) . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

/*
 * Listings near me
 */
function getListingsNearMe($lat, $long, $distance) {
    $R = 6371;  // earth's mean radius, km

    // first-cut bounding box (in degrees)
    $maxLat = $lat + rad2deg($distance/$R);
    $minLat = $lat - rad2deg($distance/$R);
    // compensate for degrees longitude getting smaller with increasing latitude
    $maxLon = $long + rad2deg($distance/$R/cos(deg2rad($lat)));
    $minLon = $long - rad2deg($distance/$R/cos(deg2rad($lat)));

  	$sql = "SELECT * FROM listings_new_temp WHERE latitude < :maxlat AND latitude > :minlat AND longitude < :maxlong AND longitude > :minlong";
  try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("maxlat", $maxLat);
    $stmt->bindParam("minlat", $minLat);
    $stmt->bindParam("maxlong", $maxLon);
    $stmt->bindParam("minlong", $minLon);
    $stmt->execute();
    $listings = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo '{"data": ' . json_encode($listings) . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

/*
 * Listings
 */
 function getListings() {
  $sql = "SELECT * FROM listings_new_temp ORDER BY sponsored DESC, name";
  try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $bases = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo '{"data": ' . json_encode($bases) . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
 }

 function getLennie() {
  $sql = "SELECT * FROM listings_new_temp";
  try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $bases = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo '{"data": ' . json_encode($bases) . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
 }


/*
 * Listings by Category ID
 */
 function getListingsByCategoryId($id) {

  $sql = "SELECT * FROM listings_new_temp WHERE main_category = :id ORDER BY sponsored DESC, name";
  try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $id);
    $stmt->execute();
    $bases = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo '{"data": ' . json_encode($bases) . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
 }

 /*
 * Listings by Subcategory ID
 */
 function getListingsBySubcategoryId($id) {

  $sql = "SELECT * FROM listings_new_temp WHERE sub_category = :id ORDER BY sponsored DESC, name";
  try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $id);
    $stmt->execute();
    $bases = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo '{"data": ' . json_encode($bases) . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
 }

 /*
  * Categories
  */
function getCategories() {

  $sql = "SELECT * FROM categories_temp";
  try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo '{"data": ' . json_encode($categories) . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}
function getSubcategoryByCategoryId($id) {

  $sql = "SELECT * FROM subcategories_temp WHERE category_id = :category_id";
  try {
    $db = getDB();
    $stmt = $db->prepare($sql);
		$stmt->bindParam("category_id", $id);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo '{"data": ' . json_encode($categories) . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

 /*
  * Categories
  */
function getCategoryTree() {

//   $sql = "SELECT * FROM categories_temp";
  $sql = "SELECT C.category_id AS id, C.category_name as name, count(L.id) AS listing_count FROM categories_temp C LEFT JOIN listings L ON (C.category_id=L.main_category) GROUP BY C.category_id, C.category_name";
//   $subsQuery = "SELECT * FROM subcategories_temp WHERE category_id = :category_id";
	$subsListingCountQuery = "";
	$subsQuery = "SELECT S.subcategory_id AS id, S.subcategory_name as name, S.category_id, count(L.id) AS listing_count FROM subcategories_temp S LEFT JOIN listings L ON (S.subcategory_id=L.sub_category) WHERE S.category_id = :category_id GROUP BY S.subcategory_id, S.subcategory_name";
  try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute();

		$categories = array();
		foreach ($stmt as $categoryInfo) {
			$category = array();
			$category["id"] = $categoryInfo["id"];
			$category["name"] = $categoryInfo["name"];
			$category["listing_count"] = $categoryInfo["listing_count"];
			$category["subcategory_listing_count"] = 0;

			$subs = $db->prepare($subsQuery);
			$subs->bindParam("category_id", $categoryInfo["id"]);
			$subs->execute();

			$subCategories = array();
			foreach ($subs as $subsInfo) {
				$subCat = array();
				$subCat["id"] = $subsInfo["id"];
				$subCat["category_id"] = $subsInfo["category_id"];
				$subCat["name"] = $subsInfo["name"];
				$subCat["listing_count"] = $subsInfo["listing_count"];

				$category["subcategory_listing_count"] += intval( $subCat["listing_count"], 10 );

				$subCategories[] = $subCat;
			}
			$category["subcategories"] = $subCategories;
			$category["subcategory_listing_count"] = (string)$category["subcategory_listing_count"];

			$categories[] = $category;
		}

    $db = null;
    echo '{"data": ' . json_encode($categories)  . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}







/**
 * Bases GET
 */
function getBases() {
  $sql = "SELECT bases.*, branches.name AS branch_name FROM bases, branches WHERE bases.branch_id = branches.id ORDER BY bases.name ASC";
  try {
    $db = getDB();
    $stmt = $db->query($sql);
    $bases = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo '{"data": ' . json_encode($bases) . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

/*
 * Listings By Base ID GET
 */
function getListingsByBaseId($id) {
	// TODO: MAKING LISTINGS BY BRANCH LIVE
  $sql = "SELECT * FROM listings_new_temp WHERE base_id = :id ORDER BY sponsored DESC, name";
  try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $id);
    $stmt->execute();
    $listings = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo '{"data": ' . json_encode($listings) . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

/**
 * Users GET
 */
function getUsers() {
  $sql = "SELECT * FROM users";
  try {
    $db = getDB();
    $stmt = $db->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo '{"data": ' . json_encode($users) . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

/**
 * User GET
 */
function getUser ($id) {
//   $sql = "SELECT * FROM users WHERE id = :id";
  $sql = "SELECT users.*, user_roles.discount_percent, user_roles.user_role FROM users, user_roles WHERE users.id = :id AND users.user_role_id = user_roles.id ORDER BY users.name ASC";
  try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_OBJ);
    $db = null;
    echo '{"data": ' . json_encode($user) . '}';
    // echo json_encode($user);
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

/**
 * Page Sponsors POST
 */
function addPageSponsor() {
  $request = \Slim\Slim::getInstance()->request();
  $sponsor = json_decode($request->getBody());

	// upload banner image to server
  define('UPLOAD_DIR', '/home/guideonmilitary/public_html/uploads/images/banners/');
  $img = $sponsor->imageSrc;
  $img = str_replace('data:image/png;base64,', '', $img);
  $img = str_replace(' ', '+', $img);
  $data = base64_decode($img);
  // $file = UPLOAD_DIR . uniqid() . '.png';
  // $filename = rawurlencode($sponsor->accountName).'-'.date(DATE_ATOM).'.png';
  $filename = uniqid() . '.png';
  // $filename = md5_file($data) . '.png';
  $file = UPLOAD_DIR . $filename;
  $success = file_put_contents($file, $data);

  if ($success) {
    $image = $filename;
  }
  else {
    $image = 'no-success.png';
  }

	// GET user
	$sql = "SELECT * FROM users WHERE users.id = :id";
  try {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $sponsor->userId);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_OBJ);
    $db = null;

    if ($user) {

    	// Send Email to Katie
			$msg = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
			$msg .= '<html xmlns="http://www.w3.org/1999/xhtml" lange="en">';
			$msg .= '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
			$msg .= '<style>@media only screen and (min-device-width: 541px) {.content {width: 540px !important;}}</style>';
			$msg .= '<title>GuideOn - New Page Sponsor</title><meta name="viewport" content="width=device-width, initial-scale=1.0"/></head><body>';
				$msg .= '<table class="content" align="left" cellpadding="5" cellspacing="5" border="0" style="width: 100%; max-width: 540px;">';

					$msg .= '<tr>';
						$msg .= '<td style="font-weight: bold;">Name:</td>';
						$msg .= '<td>';
							$msg .= $user->name;
						$msg .= '</td>';
					$msg .= '</tr>';

					$msg .= '<tr>';
						$msg .= '<td style="font-weight: bold;">Email:</td>';
						$msg .= '<td>';
							$msg .= $user->email;
						$msg .= '</td>';
					$msg .= '</tr>';

					$msg .= '<tr>';
						$msg .= '<td style="font-weight: bold;">Page:</td>';
						$msg .= '<td>';
							$msg .= $sponsor->page->name;
						$msg .= '</td>';
					$msg .= '</tr>';

					$msg .= '<tr>';
						$msg .= '<td style="font-weight: bold;"># of Months:</td>';
						$msg .= '<td>';
							$msg .= $sponsor->qtyMonths;
						$msg .= '</td>';
					$msg .= '</tr>';

					$msg .= '<tr>';
						$msg .= '<td style="font-weight: bold;">Start Date:</td>';
						$msg .= '<td>';
							$msg .= $sponsor->startDate;
						$msg .= '</td>';
					$msg .= '</tr>';

					$msg .= '<tr>';
						$msg .= '<td style="font-weight: bold;">End Date:</td>';
						$msg .= '<td>';
							$msg .= $sponsor->endDate;
						$msg .= '</td>';
					$msg .= '</tr>';

					$msg .= '<tr>';
						$msg .= '<td style="font-weight: bold;">Link URL:</td>';
						$msg .= '<td>';
							$msg .= $sponsor->linkUrl;
						$msg .= '</td>';
					$msg .= '</tr>';

					$msg .= '<tr>';
						$msg .= '<td style="font-weight: bold;">Banner Image:</td>';
						$msg .= '<td>';
							$msg .= '<img src="http://guideonmilitary.sterling.net/uploads/images/banners/' . $filename . '" width="310" height="78">';
						$msg .= '</td>';
					$msg .= '</tr>';

				$msg .= '</table>';
			$msg .= '</body></html>';

			// Always set content-type when sending HTML email
			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

			// More headers
			$headers .= 'From: <noreply@guideonmilitary.com>' . "\r\n";
			$headers .= 'Cc: guideon@joshbuchea.com, silverback@code-monkeys.com' . "\r\n";

			// send email
// 			mail('katie@onedotsolutions.com', 'GuideOn Transaction - New Page Sponsor', $msg, $headers);
			mail('katie@onedotsolutions.com', 'GuideOn Transaction - New Page Sponsor', $msg, $headers);







    	$sql = "INSERT INTO page_sponsors (id, base_id, user_id, page_id, qty_months, start_date, end_date, link_url, image) VALUES (NULL, :base_id, :user_id, :page_id, :qty_months, :start_date, :end_date, :link_url, :image)";
			try {
				$db = getDB();
				$stmt = $db->prepare($sql);

				$stmt->bindParam("base_id", $sponsor->base->id);
				$stmt->bindParam("user_id", $sponsor->userId);
				$stmt->bindParam("page_id", $sponsor->page->id);
				$stmt->bindParam("qty_months", $sponsor->qtyMonths);
				$stmt->bindParam("start_date", $sponsor->startDate);
				$stmt->bindParam("end_date", $sponsor->endDate);
				$stmt->bindParam("link_url", $sponsor->linkUrl);
				$stmt->bindParam("image", $image);

				$stmt->execute();
				$sponsor->id = $db->lastInsertId();
				$db = null;
				echo json_encode($sponsor);
			} catch(PDOException $e) {
				//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
				echo '{"error":{"text":'. $e->getMessage() .'}}';
			}
    }
    else {
			echo '{"error":{"text":'. 'Unable to find user' .'}}';
		}

  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}


/*
	Register Apple Push Device
*/

function registerApplePushDeviceBaseUser($deviceKey, $baseid, $userid) {

  try {
	if ($deviceKey != "") {
		// Determine if device already exists
		$sql = "SELECT * FROM applepushdevices WHERE push_device_id = :deviceKey";
    	$db = getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("deviceKey", $deviceKey);
		$stmt->execute();
		if ($stmt->rowCount() == 0) {
			$sql = "INSERT INTO applepushdevices (push_device_id, user_id, base_id) VALUES (:deviceKey, :userId, :baseId)";
			$insert = $db->prepare($sql);
			$insert->bindParam("deviceKey", $deviceKey);
			$insert->bindParam("userId", $userid);
			$insert->bindParam("baseId", $baseid);
			$insert->execute();
		} else {
			$sql = "UPDATE applepushdevices SET user_id = :userId, base_id = :baseId WHERE push_device_id = :deviceKey";
			$insert = $db->prepare($sql);
			$insert->bindParam("deviceKey", $deviceKey);
			$insert->bindParam("userId", $userid);
			$insert->bindParam("baseId", $baseid);
			$insert->execute();
		}
	} else {
    	echo '{"error":{"text":"Missing device key"}}';
	}
    // echo json_encode($user);
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
    echo '{"data": "success"}';
}

function registerApplePushDeviceBase($deviceKey, $baseid) {

  try {
	if ($deviceKey != "") {
		// Determine if device already exists
		$sql = "SELECT * FROM applepushdevices WHERE push_device_id = :deviceKey";
    	$db = getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("deviceKey", $deviceKey);
		$stmt->execute();
		if ($stmt->rowCount() == 0) {
			$sql = "INSERT INTO applepushdevices (push_device_id, base_id) VALUES (:deviceKey, :baseId)";
			$insert = $db->prepare($sql);
			$insert->bindParam("deviceKey", $deviceKey);
			$insert->bindParam("baseId", $baseid);
			$insert->execute();
		} else {
			$sql = "UPDATE applepushdevices SET base_id = :baseId WHERE push_device_id = :deviceKey";
			$insert = $db->prepare($sql);
			$insert->bindParam("deviceKey", $deviceKey);
			$insert->bindParam("baseId", $baseid);
			$insert->execute();
		}
	} else {
    	echo '{"error":{"text":"Missing device key"}}';
	}
    // echo json_encode($user);
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
    echo '{"data": "success"}';
}

function registerApplePushDevice($deviceKey) {

  try {
	if ($deviceKey != "") {
		// Determine if device already exists
		$sql = "SELECT * FROM applepushdevices WHERE push_device_id = :deviceKey";
    	$db = getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("deviceKey", $deviceKey);
		$stmt->execute();
		if ($stmt->rowCount() == 0) {
			$sql = "INSERT INTO applepushdevices (push_device_id) VALUES (:deviceKey)";
			$insert = $db->prepare($sql);
			$insert->bindParam("deviceKey", $deviceKey);
			$insert->execute();
		}
	} else {
    	echo '{"error":{"text":"Missing device key"}}';
	}
    // echo json_encode($user);
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
    echo '{"data": "success"}';
}

/*
	Register Android Push Device
*/

function registerAndroidPushDeviceBaseUser($deviceKey, $baseid, $userid) {

  try {
	if ($deviceKey != "") {
		// Determine if device already exists
		$sql = "SELECT * FROM androidpushdevices WHERE push_device_id = :deviceKey";
    	$db = getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("deviceKey", $deviceKey);
		$stmt->execute();
		if ($stmt->rowCount() == 0) {
			$sql = "INSERT INTO androidpushdevices (push_device_id, user_id, base_id) VALUES (:deviceKey, :userId, :baseId)";
			$insert = $db->prepare($sql);
			$insert->bindParam("deviceKey", $deviceKey);
			$insert->bindParam("userId", $userid);
			$insert->bindParam("baseId", $baseid);
			$insert->execute();
		} else {
			$sql = "UPDATE androidpushdevices SET user_id = :userId, base_id = :baseId WHERE push_device_id = :deviceKey";
			$insert = $db->prepare($sql);
			$insert->bindParam("deviceKey", $deviceKey);
			$insert->bindParam("userId", $userid);
			$insert->bindParam("baseId", $baseid);
			$insert->execute();
		}
	} else {
    	echo '{"error":{"text":"Missing device key"}}';
	}
    // echo json_encode($user);
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
    echo '{"data": "success"}';
}

function registerAndroidPushDeviceBase($deviceKey, $baseid) {

  try {
	if ($deviceKey != "") {
		// Determine if device already exists
		$sql = "SELECT * FROM androidpushdevices WHERE push_device_id = :deviceKey";
    	$db = getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("deviceKey", $deviceKey);
		$stmt->execute();
		if ($stmt->rowCount() == 0) {
			$sql = "INSERT INTO androidpushdevices (push_device_id, base_id) VALUES (:deviceKey, :baseId)";
			$insert = $db->prepare($sql);
			$insert->bindParam("deviceKey", $deviceKey);
			$insert->bindParam("baseId", $baseid);
			$insert->execute();
		} else {
			$sql = "UPDATE androidpushdevices SET base_id = :baseId WHERE push_device_id = :deviceKey";
			$insert = $db->prepare($sql);
			$insert->bindParam("deviceKey", $deviceKey);
			$insert->bindParam("baseId", $baseid);
			$insert->execute();
		}
	} else {
    	echo '{"error":{"text":"Missing device key"}}';
	}
    // echo json_encode($user);
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
    echo '{"data": "success"}';
}

function registerAndroidPushDevice($deviceKey) {

  try {
	if ($deviceKey != "") {
		// Determine if device already exists
		$sql = "SELECT * FROM androidpushdevices WHERE push_device_id = :deviceKey";
    	$db = getDB();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("deviceKey", $deviceKey);
		$stmt->execute();
		if ($stmt->rowCount() == 0) {
			$sql = "INSERT INTO androidpushdevices (push_device_id) VALUES (:deviceKey)";
			$insert = $db->prepare($sql);
			$insert->bindParam("deviceKey", $deviceKey);
			$insert->execute();
		}
	} else {
    	echo '{"error":{"text":"Missing device key"}}';
	}
    // echo json_encode($user);
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
    echo '{"data": "success"}';
}

/**
 * Messages by Device and Base GET
 */
function messagesByBasePushID($deviceId, $baseId) {
//   $sql = "SELECT * FROM users ORDER BY name_last ASC";
  $sql = "SELECT * FROM messages WHERE push_device_id = :deviceId AND base_id = :baseId AND ignoreMessage = 0 ORDER BY scheduled DESC";
  try {
	$db = getDB();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("deviceId", $deviceId);
	$stmt->bindParam("baseId", $baseId);
	$stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo '{"data": ' . json_encode($users) . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

/**
 * Messages by Device and Base GET
 */
function messagesByPushID($deviceId) {
//   $sql = "SELECT * FROM users ORDER BY name_last ASC";
  $sql = "SELECT * FROM messages WHERE push_device_id = :deviceId AND ignoreMessage = 0 ORDER BY scheduled DESC";
  try {
	$db = getDB();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("deviceId", $deviceId);
	$stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo '{"data": ' . json_encode($users) . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":' . $sql . $e->getMessage() .'}}';
  }
}

function deleteMessageByID($id) {
//   $sql = "SELECT * FROM users ORDER BY name_last ASC";
  $sql = "UPDATE messages SET ignoreMessage = 1 WHERE id = :id";
  try {
	$db = getDB();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("id", $id);
	$stmt->execute();
    $db = null;
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }

}

function readMessageByID($id) {
//   $sql = "SELECT * FROM users ORDER BY name_last ASC";
  $sql = "UPDATE messages SET messages.read = 1 WHERE id = :id";
  try {
	$db = getDB();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("id", $id);
	$stmt->execute();
    $db = null;
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }

}
//

function getNoGo() {
//   $sql = "SELECT * FROM users ORDER BY name_last ASC";
  $sql = "SELECT * FROM nogo";
  try {
	$db = getDB();
	$stmt = $db->prepare($sql);
	$stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_OBJ);
    $db = null;
    echo '{"data": ' . json_encode($users) . '}';
  } catch(PDOException $e) {
    //error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
    echo '{"error":{"text":'. $e->getMessage() .'}}';
  }
}

/*
 * DEBUG FUNTION
 */
function debug( $data ) {
    if ( is_array( $data ) )
        $output = "<script>console.log( 'Debug Objects: " . implode( ',', $data) . "' );</script>";
    else
        $output = "<script>console.log( 'Debug Objects: " . $data . "' );</script>";

    echo $output;
}
