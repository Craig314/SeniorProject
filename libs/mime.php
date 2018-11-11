<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application MIME Types


*/


//require_once '.php';


interface mimeTypesInterface
{
	public static function determineMime($filename);
}


class mimeTypes implements mimeTypesInterface
{

	// The array of mime types.  If found here, then the
	// fallthrough method is not executed.
	private static $mime = array(
		// Images
		'jpg'	=> 'image/jpeg',
		'jpg'	=> 'image/jpeg',
		'jpeg'	=> 'image/jpeg',
		'gif'	=> 'image/gif',
		'tif'	=> 'image/tiff',
		'tiff'	=> 'image/tiff',
		'png'	=> 'image/png',
		'bmp'	=> 'image/bmp',
		'svg'	=> 'image/svg+xml',
		// Sound
		'au'	=> 'audio/basic',
		'snd'	=> 'audio/basic',
		'mid'	=> 'audio/mid',
		'mp3'	=> 'audio/mpeg',
		'aif'	=> 'audio/x-aiff',
		'aiff'	=> 'audio/x-aiff',
		'm3u'	=> 'audio/x-mpegurl',
		'ra'	=> 'audio/x-pn-realaudio',
		'ram'	=> 'audio/x-pn-realaudio',
		'wav'	=> 'audio/x-wav',
		// Video
		'mp2'	=> 'video/mpeg',
		'mpa'	=> 'video/mpeg',
		'mpe'	=> 'video/mpeg',
		'mpeg'	=> 'video/mpeg',
		'mpg'	=> 'video/mpeg',
		'mp4'	=> 'video/mp4',
		'mp4v'	=> 'video/mp4',
		'mpg4'	=> 'video/mp4',
		'mov'	=> 'video/quicktime',
		'qt'	=> 'video/quicktime',
		'asf'	=> 'video/x-ms-asf',
		'asx'	=> 'video/x-ms-asf',
		'avi'	=> 'video/x-msvideo',
		'h261'	=> 'video/h261',
		'h263'	=> 'video/h263',
		'h264'	=> 'video/h264',
		'ogv'	=> 'video/ogg',
		// Text
		'css'	=> 'text/css',
		'js'	=> 'text/javascript',
		'htm'	=> 'text/html',
		'html'	=> 'text/html',
		'stm'	=> 'text/html',
		'323'	=> 'text/h323',
		'uls'	=> 'text/iuls',
		'bas'	=> 'text/plain',
		'c'		=> 'text/plain',
		'cc'	=> 'text/plain',
		'cxx'	=> 'text/plain',
		'cpp'	=> 'text/plain',
		'h'		=> 'text/plain',
		'hh'	=> 'text/plain',
		'p'		=> 'text/plain',
		'pas'	=> 'text/plain',
		'cs'	=> 'text/plain',
		'php'	=> 'text/plain',
		'txt'	=> 'text/plain',
		'vbs'	=> 'text/plain',
		's'		=> 'text/plain',
		'asm'	=> 'text/plain',
		'rtx'	=> 'text/richtext',
		'sct'	=> 'text/scriptlet',
		'tsv'	=> 'text/tab-separated-values',
		'htt'	=> 'text/webviewhtml',
		'htc'	=> 'text/x-component',
		'etx'	=> 'text/x-setext',
		'vcf'	=> 'text/vcf',
		'xml'	=> 'text/xml',
		// Application
		'json'	=> 'application/json',
		'doc'	=> 'application/msword',
		'dot'	=> 'application/msword',
		'docx'	=> 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'dotx'	=> 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
		'onetoc'	=> 'application/onenote',
		'onetoc2'	=> 'application/onenote',
		'onetmp'	=> 'application/onenote',
		'onepkg'	=> 'application/onenote',
		'pptx'	=> 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'sldx'	=> 'application/vnd.openxmlformats-officedocument.presentationml.slide',
		'ppsx'	=> 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
		'potx'	=> 'application/vnd.openxmlformats-officedocument.presentationml.template',
		'xlsx'	=> 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'xltx'	=> 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
		'pdf'	=> 'application/pdf',
	);

	// returns the MIME type of the given filename.  The filename must be
	// the fully qualified path/filename, must be valid on the current
	// platform, and it must exist.  This does not check for errors as
	// that is the caller's responsibility.
	public static function determineMime($filename)
	{
		$pathdata = pathinfo($filename);
		if (!isset($pathdata['extension']))
			return 'application/octet-stream';
		if (isset(self::$mime[$pathdata['extension']]))
			return self::$mime[$pathdata['extension']];
		return mime_content_type($filename);
	}

}


?>