<?php

/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application OpenID Provider Module for Generic Provider

This module allows customization to various providers and
their access methods.  The callback code and the request
generating functions should be customized to the particulars
of the given provider.

Note that the filename format must be openid.provider.php
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


if (!isset($openIdInitiate))
{
	// User gets web redirected to this file, so we check to see if the
	// initiate flag has been set.  If it has, then this file was
	// included by the main OpenID library.  If it wasn't, then the user
	// was sent here on a redirect.
	$session->restart();
	require_once BASE_DIR . 'openid.php';
	switch ($_SERVER['REQUEST_METHOD'])
	{
		case 'GET':
		// This is specified by the protocol, but may need customization.
			$openIdData = array(
				'ns' => (isset($_GET['openid.ns'])) ? $_GET['openid.ns'] : '',
				'mode' => (isset($_GET['openid.mode'])) ? $_GET['openid.mode'] : '',
				'endpoint' => (isset($_GET['openid.op_endpoint'])) ? $_GET['openid.op_endpoint'] : '',
				'claimed_id' => (isset($_GET['openid.claimed_id'])) ? $_GET['openid.claimed_id'] : '',
				'identiy' => (isset($_GET['openid.identity'])) ? $_GET['openid.identity'] : '',
				'return_to' => (isset($_GET['openid.return_to'])) ? $_GET['openid.return_to'] : '',
				'nonce' => (isset($_GET['openid.response_nonce'])) ? $_GET['openid.response_nonce'] : '',
				'inv_handle' => (isset($_GET['openid.invalidate_handle'])) ? $_GET['openid.invalidate_handle'] : '',
				'asc_handle' => (isset($_GET['openid.associate_handle'])) ? $_GET['oenid.associate_handle'] : '',
				'signed' => (isset($_GET['openid.signed'])) ? $_GET['openid.signed'] : '',
				'signature' => (isset($_GET['openid.sig'])) ? $_GET['openid.sig'] : '',
				'error' => (isset($_GET['openid.error'])) ? $_GET['openid.error'] : '',
			);
			break;
		case 'POST':
			$openIdData = array(
				'ns' => (isset($_POST['openid.ns'])) ? $_POST['openid.ns'] : '',
				'mode' => (isset($_POST['openid.mode'])) ? $_POST['openid.mode'] : '',
				'endpoint' => (isset($_POST['openid.op_endpoint'])) ? $_POST['openid.op_endpoint'] : '',
				'claimed_id' => (isset($_POST['openid.claimed_id'])) ? $_POST['openid.claimed_id'] : '',
				'identiy' => (isset($_POST['openid.identity'])) ? $_POST['openid.identity'] : '',
				'return_to' => (isset($_POST['openid.return_to'])) ? $_POST['openid.return_to'] : '',
				'nonce' => (isset($_POST['openid.response_nonce'])) ? $_POST['openid.response_nonce'] : '',
				'inv_handle' => (isset($_POST['openid.invalidate_handle'])) ? $_POST['openid.invalidate_handle'] : '',
				'asc_handle' => (isset($_POST['openid.associate_handle'])) ? $_POST['oenid.associate_handle'] : '',
				'signed' => (isset($_POST['openid.signed'])) ? $_POST['openid.signed'] : '',
				'signature' => (isset($_POST['openid.sig'])) ? $_POST['openid.sig'] : '',
				'error' => (isset($_POST['openid.error'])) ? $_POST['openid.error'] : '',
			);
			break;
		default:
			html::sendCode(501);
			exit(0);
			break;
	}

	// Calls back into the main library with data from the provider.
	$openid->callback($openIdData);
}

// Builds an OpenID request
// This will have to be customized to the provider and whatever
// situation the system is operating in.
function buildRequest($userdata, $provider, $handle)
{
	global $CONFIGVAR;
	global $dbuser;
	global $dbconf;
	global $ajax;
	global $herr;

	$request = html::getBaseURL() . '?';

	// The OpenID spec says that openid.ns must be this value for v2.0.
	// Other values are as follows:
	// http://openid.net/signon/1.1
	// http://openid.net/signon/1.0
	$request .= 'openid.ns=' . 'http://specs.openid.net/auth/2.0';

	// Pick one of the two following modes.  Delete the other one.
	$request .= '&openid.mode=checkid_setup';	// Allows user to interact with provider
	$request .= '&openid.mode=checkid_immediate';	// Doesn't allow the user to interact with provider

	// The user's OpenID Identity.  This is pulled from the database.
	// Normally, this is user supplied.
	$request .= '&openid.claimed_id=' . $userdata['ident'];

	// The URL to redirect the user back to.
	$request .= '&openid.return_to=' . $provider['redirecturl'];
	$request .= '&openid.assoc_handle=' . $handle;
	$request .= '&openid.realm=' . $CONFIGVAR['server_hostname']['value'];

	return $request;
}





?>