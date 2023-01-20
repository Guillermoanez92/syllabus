<?php
function getContent($uid, $type = null){

	global $BUILDINFO;

	if(isset($BUILDINFO->lib->content_management) && function_exists("getByUID")){

		return (is_null($type)) ? getByUID($uid) : getByUID($uid, $type);
	}

	return null;
}

/**
 * Slugify string
 */
function slugify($string, $delimiter = "-") {

	$string = iconv("utf-8", "ASCII//TRANSLIT//IGNORE", $string);
	$string = str_replace("'", "", $string);
	$string = strtolower(trim(preg_replace("/[^A-Za-z0-9-]+/", $delimiter, $string)));
	$string = str_replace("---", "-", $string);
	$string = trim($string, $delimiter);

	return $string;
}

/**
 * Truncate string
 */
function truncateString($string, $length = 300){

	$string = htmlentities($string, null, "utf-8");
	$string = preg_replace("/\s+|&nbsp;/", " ", $string);
	$string = preg_replace("/\s+?(\S+)?$/", "", substr($string, 0, $length));
	$string = html_entity_decode($string);

	return $string;
}

/**
 * Get Embedded Video ID
 *	supports: youtube, vimeo
 */
function returnEmbedID($string){

	$video_id = "";

	if(strpos($string, "youtu") > 0){
		/* ----- Youtube ----- */
		preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+(?=\?)|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $string, $matches);
		$video_id = $matches[0];
	} elseif(strpos($string, "vimeo") > 0){
		/* ----- Vimeo ----- */
		preg_match("/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([‌​0-9]{6,11})[?]?.*/", $string, $matches);
		$video_id = $matches[5];
	}

	return $video_id;
}

/**
 * Image output
 *
 */
function writeImage($url){

	$urlInfo = parse_url($url);

	/* --------------- */
	echo "<br><pre>";
	print_r($urlInfo);
	echo "</pre>";
	/* --------------- */
}

/**
 * Fire Parallel Request
 */
function execRequest($url, $payload) {

	$cmd = "curl -X POST -H 'Content-Type: application/json'";
	$cmd .= " -d '" . $payload . "' " . "'" . $url . "'";
	$cmd .= " > /dev/null 2>&1 &";

	exec($cmd, $output);
}

/**
 * Load Prismic Content By Type
 */
function getByType($type, $sort = null){

	global $CONTENT;


	if (file_exists(FILE_CACHE . "documents.json")) {
		$reference = json_decode(file_get_contents(FILE_CACHE . "documents.json"));
	}

	$document = array();

	if ($sort === null) {
		$sort = "first_publication_date desc";
	}



	/* --- Loop through content --- */
	if (!isset($reference) || !isset($reference->{$type}) || count($reference->{$type}) <= 0) {

		$api = $CONTENT->framework->get_api();
		$response = $api->query(Predicates::at("document.type", $type), array("orderings" => "[my." . $type . "." . $sort . "]"));

		/* --- No Reference? No Problem --- */
		if (!isset($reference)) {
			$prismicReference = new stdClass();
		} else {
			$prismicReference = $reference;
		}

		foreach ($response->results as $content) {

			$uid = $content->uid;
			$info = getContent($uid, $type);

			if (!isset($prismicReference->{$content->type}) || !is_array($prismicReference->{$content->type})) {
				$prismicReference->{$content->type} = array();
			}

			array_push($prismicReference->{$content->type}, base64_encode($content->uid));
			array_push($document, $info);
		}

		/* --- Check through pagination --- */
		$page = (int)$response->page;
		$total = (int)$response->total_pages;

		while ($page < $total) {

			++$page;

			$response = $api->query(Predicates::at("document.type", $type), array("page" => $page, "orderings" => "[my." . $type . "." . $sort . "]"));

			foreach ($response->results as $content) {

				$uid = $content->uid;
				$info = getContent($uid, $type);

				array_push($prismicReference->{$content->type}, base64_encode($content->uid));
				array_push($document, $info);
			}
		}

		/* --- Write Reference --- */
		file_put_contents(FILE_CACHE . "documents.json", json_encode($prismicReference));
		chmod(FILE_CACHE . "documents.json", 0664);
	} else {

		foreach ($reference->{$type} as $content) {

			$uid = base64_decode($content);
			$info = $CONTENT->local->getContent($uid, $type);

			array_push($document, $info);
		}

		if ($sort !== null) {
			$sortArray = explode(" ", $sort);
			$sortKey = $sortArray[0];

			if (isset($sortArray[1]) && $sortArray[1] !== "asc") {
				$sortDir = strtolower($sortArray[1]);
			} else {
				$sortDir = "asc";
			}

			$sort = new sortPrismicObject($sortKey, $sortDir);
			$document = $sort->sort($document);
		}
	}

	return $document;
}

/**
 * Sort Prismic Object by a child Key in specified direction
 *	- Note: direction defaults to "desc"
 */
class sortPrismicObject{

	private $key;
	private $direction;

	public function __construct($key, $direction){
		$this->key = $key;
	}

	public function sortKeys($a, $b){

		switch ($this->key) {

			case "author":
			case "category":
			case "location":

				return strcmp($b->data->{$this->key}->slug, $a->data->{$this->key}->slug);

				break;

			case "published_date":
			case "title":


			return strcmp($b->data->{$this->key}, $a->data->{$this->key});

				break;

			case "relevance":

				return strcmp($b->relevance, $a->relevance);

				break;

			case "first_publication_date":
			default:

			return strcmp($b->{$this->key}, $a->{$this->key});

				break;
		}
	}

	public function sort($object){

		usort($object, array($this, "sortKeys"));
		/* --------------- *
		echo "<br><pre>";
		print_r($object);
		echo "</pre>";
		/* --------------- */

		return $object;
	}
}





/**
 * Check For Tag
 */
function checkForTag($tag_array, $tag){

	foreach ($tag_array as $key => $obj) {
		if ($obj->tag->uid === strtolower($tag)) {
			return true;
		}
	}

	return false;
}

/**
 * Return select option countries
 *	- Remove countries that are outside Shipping zone
 *	- Prioritize the country list: Default US
 */
function returnCountryOptions(string $selected = null): string
{
	$countries = array(
		"US" => "United States",
		"AF" => "Afghanistan",
		"AX" => "Åland Islands",
		"AL" => "Albania",
		"DZ" => "Algeria",
		"AS" => "American Samoa",
		"AD" => "Andorra",
		"AO" => "Angola",
		"AI" => "Anguilla",
		"AQ" => "Antarctica",
		"AG" => "Antigua and Barbuda",
		"AR" => "Argentina",
		"AM" => "Armenia",
		"AW" => "Aruba",
		"AU" => "Australia",
		"AT" => "Austria",
		"AZ" => "Azerbaijan",
		"BS" => "Bahamas",
		"BH" => "Bahrain",
		"BD" => "Bangladesh",
		"BB" => "Barbados",
		"BY" => "Belarus",
		"BE" => "Belgium",
		"BZ" => "Belize",
		"BJ" => "Benin",
		"BM" => "Bermuda",
		"BT" => "Bhutan",
		"BO" => "Bolivia",
		"BA" => "Bosnia and Herzegovina",
		"BW" => "Botswana",
		"BV" => "Bouvet Island",
		"BR" => "Brazil",
		"IO" => "British Indian Ocean Territory",
		"BN" => "Brunei Darussalam",
		"BG" => "Bulgaria",
		"BF" => "Burkina Faso",
		"BI" => "Burundi",
		"KH" => "Cambodia",
		"CM" => "Cameroon",
		"CA" => "Canada",
		"CV" => "Cape Verde",
		"KY" => "Cayman Islands",
		"CF" => "Central African Republic",
		"TD" => "Chad",
		"CL" => "Chile",
		"CN" => "China",
		"CX" => "Christmas Island",
		"CC" => "Cocos (Keeling) Islands",
		"CO" => "Colombia",
		"KM" => "Comoros",
		"CG" => "Congo",
		"CD" => "Congo, The Democratic Republic of The",
		"CK" => "Cook Islands",
		"CR" => "Costa Rica",
		"CI" => "Cote D'ivoire",
		"HR" => "Croatia",
		"CU" => "Cuba",
		"CY" => "Cyprus",
		"CZ" => "Czech Republic",
		"DK" => "Denmark",
		"DJ" => "Djibouti",
		"DM" => "Dominica",
		"DO" => "Dominican Republic",
		"EC" => "Ecuador",
		"EG" => "Egypt",
		"SV" => "El Salvador",
		"GQ" => "Equatorial Guinea",
		"ER" => "Eritrea",
		"EE" => "Estonia",
		"ET" => "Ethiopia",
		"FK" => "Falkland Islands (Malvinas)",
		"FO" => "Faroe Islands",
		"FJ" => "Fiji",
		"FI" => "Finland",
		"FR" => "France",
		"GF" => "French Guiana",
		"PF" => "French Polynesia",
		"TF" => "French Southern Territories",
		"GA" => "Gabon",
		"GM" => "Gambia",
		"GE" => "Georgia",
		"DE" => "Germany",
		"GH" => "Ghana",
		"GI" => "Gibraltar",
		"GR" => "Greece",
		"GL" => "Greenland",
		"GD" => "Grenada",
		"GP" => "Guadeloupe",
		"GU" => "Guam",
		"GT" => "Guatemala",
		"GG" => "Guernsey",
		"GN" => "Guinea",
		"GW" => "Guinea-bissau",
		"GY" => "Guyana",
		"HT" => "Haiti",
		"HM" => "Heard Island and Mcdonald Islands",
		"VA" => "Holy See (Vatican City State)",
		"HN" => "Honduras",
		"HK" => "Hong Kong",
		"HU" => "Hungary",
		"IS" => "Iceland",
		"IN" => "India",
		"ID" => "Indonesia",
		"IR" => "Iran, Islamic Republic of",
		"IQ" => "Iraq",
		"IE" => "Ireland",
		"IM" => "Isle of Man",
		"IL" => "Israel",
		"IT" => "Italy",
		"JM" => "Jamaica",
		"JP" => "Japan",
		"JE" => "Jersey",
		"JO" => "Jordan",
		"KZ" => "Kazakhstan",
		"KE" => "Kenya",
		"KI" => "Kiribati",
		"KP" => "Korea, Democratic People's Republic of",
		"KR" => "Korea, Republic of",
		"KW" => "Kuwait",
		"KG" => "Kyrgyzstan",
		"LA" => "Lao People's Democratic Republic",
		"LV" => "Latvia",
		"LB" => "Lebanon",
		"LS" => "Lesotho",
		"LR" => "Liberia",
		"LY" => "Libyan Arab Jamahiriya",
		"LI" => "Liechtenstein",
		"LT" => "Lithuania",
		"LU" => "Luxembourg",
		"MO" => "Macao",
		"MK" => "Macedonia, The Former Yugoslav Republic of",
		"MG" => "Madagascar",
		"MW" => "Malawi",
		"MY" => "Malaysia",
		"MV" => "Maldives",
		"ML" => "Mali",
		"MT" => "Malta",
		"MH" => "Marshall Islands",
		"MQ" => "Martinique",
		"MR" => "Mauritania",
		"MU" => "Mauritius",
		"YT" => "Mayotte",
		"MX" => "Mexico",
		"FM" => "Micronesia, Federated States of",
		"MD" => "Moldova, Republic of",
		"MC" => "Monaco",
		"MN" => "Mongolia",
		"ME" => "Montenegro",
		"MS" => "Montserrat",
		"MA" => "Morocco",
		"MZ" => "Mozambique",
		"MM" => "Myanmar",
		"NA" => "Namibia",
		"NR" => "Nauru",
		"NP" => "Nepal",
		"NL" => "Netherlands",
		"AN" => "Netherlands Antilles",
		"NC" => "New Caledonia",
		"NZ" => "New Zealand",
		"NI" => "Nicaragua",
		"NE" => "Niger",
		"NG" => "Nigeria",
		"NU" => "Niue",
		"NF" => "Norfolk Island",
		"MP" => "Northern Mariana Islands",
		"NO" => "Norway",
		"OM" => "Oman",
		"PK" => "Pakistan",
		"PW" => "Palau",
		"PS" => "Palestinian Territory, Occupied",
		"PA" => "Panama",
		"PG" => "Papua New Guinea",
		"PY" => "Paraguay",
		"PE" => "Peru",
		"PH" => "Philippines",
		"PN" => "Pitcairn",
		"PL" => "Poland",
		"PT" => "Portugal",
		"PR" => "Puerto Rico",
		"QA" => "Qatar",
		"RE" => "Reunion",
		"RO" => "Romania",
		"RU" => "Russian Federation",
		"RW" => "Rwanda",
		"SH" => "Saint Helena",
		"KN" => "Saint Kitts and Nevis",
		"LC" => "Saint Lucia",
		"PM" => "Saint Pierre and Miquelon",
		"VC" => "Saint Vincent and The Grenadines",
		"WS" => "Samoa",
		"SM" => "San Marino",
		"ST" => "Sao Tome and Principe",
		"SA" => "Saudi Arabia",
		"SN" => "Senegal",
		"RS" => "Serbia",
		"SC" => "Seychelles",
		"SL" => "Sierra Leone",
		"SG" => "Singapore",
		"SK" => "Slovakia",
		"SI" => "Slovenia",
		"SB" => "Solomon Islands",
		"SO" => "Somalia",
		"ZA" => "South Africa",
		"GS" => "South Georgia and The South Sandwich Islands",
		"ES" => "Spain",
		"LK" => "Sri Lanka",
		"SD" => "Sudan",
		"SR" => "Suriname",
		"SJ" => "Svalbard and Jan Mayen",
		"SZ" => "Swaziland",
		"SE" => "Sweden",
		"CH" => "Switzerland",
		"SY" => "Syrian Arab Republic",
		"TW" => "Taiwan, Province of China",
		"TJ" => "Tajikistan",
		"TZ" => "Tanzania, United Republic of",
		"TH" => "Thailand",
		"TL" => "Timor-leste",
		"TG" => "Togo",
		"TK" => "Tokelau",
		"TO" => "Tonga",
		"TT" => "Trinidad and Tobago",
		"TN" => "Tunisia",
		"TR" => "Turkey",
		"TM" => "Turkmenistan",
		"TC" => "Turks and Caicos Islands",
		"TV" => "Tuvalu",
		"UG" => "Uganda",
		"UA" => "Ukraine",
		"AE" => "United Arab Emirates",
		"GB" => "United Kingdom",
		"UM" => "United States Minor Outlying Islands",
		"UY" => "Uruguay",
		"UZ" => "Uzbekistan",
		"VU" => "Vanuatu",
		"VE" => "Venezuela",
		"VN" => "Viet Nam",
		"VG" => "Virgin Islands, British",
		"VI" => "Virgin Islands, U.S.",
		"WF" => "Wallis and Futuna",
		"EH" => "Western Sahara",
		"YE" => "Yemen",
		"ZM" => "Zambia",
		"ZW" => "Zimbabwe"
	);

	$html = "";

	foreach ($countries as $key => $value) {
		if ($selected && $selected == $key || $selected && $selected == $value) {
			$html .= "<option value=\"$key\" title=\"" . htmlspecialchars($value) . "\" selected=\"selected\">" . htmlspecialchars($value) . "</option>";
		} else {
			$html .= "<option value=\"$key\" title=\"" . htmlspecialchars($value) . "\">" . htmlspecialchars($value) . "</option>";
		}
	}

	return $html;
}
