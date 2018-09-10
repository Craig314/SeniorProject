<?php

const BASE_DIR = '../libs/';
require_once BASE_DIR . 'confload.php';
require_once BASE_DIR . 'dbaseconf.php';
require_once BASE_DIR . 'dbaseuser.php';
require_once BASE_DIR . 'error.php';
require_once BASE_DIR . 'vfystr.php';
require_once BASE_DIR . 'security.php';
require_once BASE_DIR . 'session.php';
require_once BASE_DIR . 'utility.php';

$providerId = 0;

// Builds an authcode URI to send the user to for OAuth Authentication.
function buildRequestAuthcode($rxa)
{
	$url = $rxa['authurl'];
	$clientid = $rxa['clientid'];
	$redirect = $rxa['redirecturl'];
	$scope = $rxa['scope'];

	$request = $url;
	$request .= '?response_type=' . 'code';
	$request .= '&client_id=' . $clientid;
	$request .= '&redirect_uri=' . $redirect;
	$request .= '&scope=' . $scope;

	return $request;
}

// Builds an implicit URI to send the user to for OAuth Authentication.
function buildRequestImplicit($rxa)
{
	$url = $rxa['authurl'];
	$clientid = $rxa['clientid'];
	$redirect = $rxa['redirecturl'];
	$scope = $rxa['scope'];

	$request = $url;
	$request .= '?response_type=' . 'token';
	$request .= '&client_id=' . $clientid;
	$request .= '&redirect_uri=' . $redirect;
	$request .= '&scope=' . $scope;

	return $request;
}

// Builds an implicit URI to send the user to for OAuth Authentication.
function buildRequestPassword($rxa)
{
	$url = $rxa['authurl'];
	$clientid = $rxa['clientid'];
	$redirect = $rxa['redirecturl'];
	$scope = $rxa['scope'];

	$request = $url;
	$request .= '?response_type=' . 'token';
	$request .= '&client_id=' . $clientid;
	$request .= '&redirect_uri=' . $redirect;
	$request .= '&scope=' . $scope;

	return $request;
}


	$rxa = $dbconf->queryOAuth($provider);



?>