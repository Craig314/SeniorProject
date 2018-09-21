<?php

/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application OAuth Provider Module for Generic Provider

This module allows customization to various providers and
their access methods.  The callback code and the request
generating functions should be customized to the particulars
of the given provider.

Note that the filename format must be oauth.provider.php
where provider is the name of the provider such as google,
facebook, etc....  If this convention is not followed, then
the module will not be picked up in the web interface.

This is the generic module that is written directly to the
standard.

*/




const BASE_DIR = '../libs/';
require_once BASE_DIR . 'confload.php';
require_once BASE_DIR . 'dbaseconf.php';
require_once BASE_DIR . 'dbaseuser.php';
require_once BASE_DIR . 'error.php';
require_once BASE_DIR . 'vfystr.php';
require_once BASE_DIR . 'security.php';
require_once BASE_DIR . 'session.php';
require_once BASE_DIR . 'utility.php';


if (!isset($oAuthInitiate))
{
	// User gets web redirected to this file, so we check to see if the
	// initiate flag has been set.  If it has, then this file was
	// included by the main OAuth library.  If it wasn't, then the user
	// was sent here on a redirect.
	$session->restart();
	require_once BASE_DIR . 'oauth.php';
	if ($_SERVER['REQUEST_METHOD'] == 'GET')
	{
		// This is specified by the protocol, but may need customization.
		$oAuthData = array(
			'code' => (isset($_GET['code'])) ? $_GET['code'] : '',
			'state' => (isset($_GET['state'])) ? $_GET['state'] : '',
			'error' => (isset($_GET['error'])) ? $_GET['error'] : '',
			'errdesc' => (isset($_GET['error_description'])) ? $_GET['error_descriptin'] : '',
			'erruri' => (isset($_GET['error_uri'])) ? $_GET['error_uri'] : '',
		);

		// Calls back into the main library with data from the provider.
		$oauth->callback($oAuthData);
	}
	else
	{
	}
}

// Builds an authcode URI to send the user to for OAuth Authentication.
function oAuth_buildRequestAuthcode($rxa, $state = '', $challenge = NULL)
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
	if (!empty($state)) $request .= '&state=' . $state;
	if (!empty($challenge))
	{
		$request .= '&code_challenge=' . $challenge['challenge'];
		$request .= '&code_challenge_method=' . $challenge['method'];
	}

	return $request;
}

// Builds an implicit URI to send the user to for OAuth Authentication.
function oAuth_buildRequestImplicit($rxa, $state = '')
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
	if (!empty($state)) $request .= '&state=' . $state;

	return $request;
}

// Builds an implicit URI to send the user to for OAuth Authentication.
function oAuth_buildRequestPassword($rxa, $username, $password, $state = '')
{
	$url = $rxa['authurl'];
	$clientid = $rxa['clientid'];

	$request = $url;
	$request .= '?grant_type=' . 'password';
	$request .= '&username=' . $username;
	$request .= '&password=' . $password;
	$request .= '&client_id=' . $clientid;
	if (!empty($state)) $request .= '&state=' . $state;

	return $request;
}

// Builds a application URI for OAuth authentication.
function oAuth_buildRequestClient($rxa)
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
function oAuth_buildRequestRefresh($rxa, $refresh, $state = '')
{
	$url = $rxa['authurl'];
	$clientid = $rxa['clientid'];
	$clientsecret = $rxa['clientsecret'];

	$request = $url;
	$request .= '?grant_type=' . 'refresh_token';
	$request .= '&client_id=' . $clientid;
	$request .= '&client_secret=' . $clientsecret;
	$request .= '&refresh_token=' . $refresh;
	if (!empty($state)) $request .= '&state=' . $state;

	return $request;
}

// Builds a request for an access token.
// Only works with the authcode request type.
function oAuth_buildRequestAccessToken($rxa, $authcode, $challenge = NULL)
{
	$redirect = $rxa['redirecturl'];
	$clientid = $rxa['clientid'];
	$clientsecret = $rxa['clientsecret'];

	$request = 'grant_type=' . 'authorization_code';
	$request .= '&code=' . $authcode;
	$request .= '&redirect_uri=' . $redirect;
	$request .= '&client_id=' . $clientid;
	$request .= '&client_secret=' . $clientsecret;
	if (!empty($challenge))
		$request .= '&challenge_verifier=' . $challenge;
	
	return $request;
}

// Converts the return data from the token request into
// a common format.
function oAuth_convertResponseToken($rxa)
{
	$data = array();
	if (isset($rxa['access_token'])) $data['token'] = $rxa['access_token'];
	if (isset($rxa['expires_in'])) $data['expire'] = $rxa['expires_in'];
	if (isset($rxa['error'])) $data['error'] = $rxa['error'];
	if (isset($rxa['error_uri'])) $data['erruri'] = $rxa['error_uri'];
	if (isset($rxa['error_description'])) $data['errdesc'] = $rxa['error_description'];
	return $data;
}


?>