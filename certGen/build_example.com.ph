#!/usr/bin/env php
<?php
/**
 * Configuration for testproject
 */
define('CERTGEN_NAME','example.com'); // Certificate name
$CERTGEN_SUBJECT = array( // Certificate subject
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

// Include certGen
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'certGen.php');