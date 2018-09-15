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
function buildRequestPassword($username, $password, $rxa)
{
	$url = $rxa['authurl'];
	$clientid = $rxa['clientid'];

	$request = $url;
	$request .= '?grant_type=' . 'password';
	$request .= '&username=' . $username;
	$request .= '&password=' . $password;
	$request .= '&client_id=' . $clientid;

	return $request;
}

// Builds a application URI for OAuth authentication.
function buildRequestClient($rxa)
{
	$url = $rxa['authurl'];
	$clientid = $rxa['clientid'];
	$clientsecret = $rxa['clientsecret'];

	$request = $url;
	$request .= '?grant_type=' . 'client_credentials';
	$request .= '&client_id=' . $clientid;
	$request .= '&client_secret=' . $clientsecret;

	return $request;
}

// Builds a refresh request for an expired token.
// Does not work with the implicit request type.
function buildRequestRefresh($refresh, $rxa)
{
	$url = $rxa['authurl'];
	$clientid = $rxa['clientid'];
	$clientsecret = $rxa['clientsecret'];

	$request = $url;
	$request .= '?grant_type=' . 'refresh_token';
	$request .= '&client_id=' . $clientid;
	$request .= '&client_secret=' . $clientsecret;
	$request .= '&refresh_token=' . $refresh;

	return $request;
}


	$rxa = $dbconf->queryOAuth($provider);



?>