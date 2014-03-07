<?php
/**
 * certGen by C!$C0^211
 * 
 * Info: http://www.zytrax.com/tech/survival/ssl.html
 */

// When running this in cli
if (PHP_SAPI == 'cli') {
	if (!defined('CERTGEN_NAME')) define('CERTGEN_NAME','example.com'); // Certificate name
	if (!isset($CERTGEN_SUBJECT)) $CERTGEN_SUBJECT = array( // Certificate subject
		'countryName'=>'DE', // Country name (ISO3166, countrycode)
		'commonName'=>'example.com', // Common name (Product name, Domain, whatever)
		'domainComponent'=>'example.com', // Domain component
		'distinguishedName'=>'Example', // Distinguished name
		'emailAddress'=>'mail@example.com', // E-Mail
		'givenName'=>'Jon', // Given name
		'initials'=>'JD', // Initials
		'localityName'=>'Halle Saale', // Locality name (City)
		'organizationName'=>'Example', // Organization name (Company name)
		'organizationalUnitName'=>'Example Domain Cert', // Organization unit name (Cert type)
		'pseudonym'=>'JDE', // Pseudonym
		'serialNumber'=>'211', // Serial number
		'surname'=>'Doe', // Surname
		'stateOrProvinceName'=>'Sachsen-Anhalt', // State/Province name
		'title'=>'Cert for example.com', // Title
	);
	certGen(CERTGEN_NAME,$CERTGEN_SUBJECT,TRUE);
	exit(0);
}

/**
 * certGen function
 */
function certGen($name,$subject,$verbose=FALSE) {
	if ($verbose) {
		print chr(13).chr(10).'certGen started!';
		print chr(13).chr(10).'Name: '.$name;
		print chr(13).chr(10).'Subject: '.print_r($subject,TRUE);
	}
	$string = '';
	foreach ($subject as $k => $v) {
		if (empty($v)) continue;
		if (is_array($v)) {
			foreach ($v as $w) {
				$string .= '/'.$k.'='.$w;
			}
		} else {
			$string .= '/'.$k.'='.$v;
		}
	}
	$r = shell_exec('openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout '.$name.'.key -out '.$name.'.crt -subj "'.$string.'"');
	if ($verbose) {
		print $r.'certGen finished!'.chr(13).chr(10).chr(13).chr(10);
	}
}