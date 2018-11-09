<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application File Handling Library

In all cases, the following parameters have the following meanings:

$basePath		The root directory of the instance which the user
				cannot escape.
$currentPath	The current path into the root directory instance.
$filename		The filename being acted upon.
$dirname		The directory name being acted upon.
$oldFile		The old filename which must exist. (Rename Only)
$newFile		The new filename which must not exist. (Rename Only)
$oldDir			The old directory name which must exist. (Rename Only)
$newDir			The new directory name which must not exist. (Rename Only)
$modPathFile	The path/filename of the module in reference to the
				server document root.  IE: / means top level directory
				of the server document root.

*/


require_once 'confload.php';
require_once 'mime.php';
require_once 'vfystr.php';
require_once 'error.php';
require_once 'html.php';
require_once 'timedate.php';


interface filesInterface
{
	// Some operational constants
	const BLOCKSIZE = 4096;
	const BLOCKBACK = 512;

	// Methods

	// Files
	public function fileUpload($basePath, $currentPath, $sizeMax, $randFilename = false);
	public function fileDownloadUrl($basePath, $currentPath, $filename,
		$baseUrl, $modPathFile);
	public function fileDownload($token);
	public function fileViewUrl($basePath, $currentPath, $filename,
		$baseUrl, $modPathFile);
	public function fileView($token);
	public function fileRename($basePath, $currentPath, $oldFile, $newFile);
	public function fileMove($basePath, $currentPath, $filename, $newPath);
	public function fileCopy($basePath, $currentPath, $filename, $newPath);
	public function fileDelete($basePath, $currentPath, $filename);
	public function fileDetail($basePath, $currentPath, $filename);

	// Directories
	public function directoryMoveUp($basePath, $currentPath);
	public function directoryMoveDown($basePath, $currentPath, $dirname);
	public function directoryCreate($basePath, $currentPath, $newDir);
	public function directoryRename($basePath, $currentPath, $oldDir, $newDir);
	public function directoryMove($basePath, $currentPath, $dirname, $newPath);
	public function directoryRemove($basePath, $currentPath, $dirname);

	// Listing
	public function buildDirectoryList($basePath, $currentPath);
}


class filesClass implements filesInterface
{
	// Builds a real path for a file or directory.
	private function buildRealPath($basePath, $currentPath, $name = '')
	{
		if ($currentPath == '/')
			$realPath = $basePath;
		else
			$realPath = $basePath . '/' . $currentPath;
		if (!empty($name))
			$realPath .= '/' . $name;
		$realPath = str_replace('//', '/', $realPath);
		return $realPath;
	}

	// Checks path/names for invalid characters.
	private function checkNamesPaths($type, $basePath, $currentPath, $name1, $name2 = '')
	{
		global $vfystr;
		global $herr;

		// For all variations, this is needed.
		$result = $vfystr->strchk($currentPath, '', '', verifyString::STR_PATHNAME);
		if ($result == false)
			$herr->puterrmsg('Invalid characters in directory path.');
		
		// Set the parameters for the checking
		switch ($type)
		{
			case 100:		// Basic filename
				$msg1 = 'Invalid characters in filename.';
				$msg2 = '';
				$chk1 = verifyString::STR_FILENAME;
				$chk2 = -1;
				$type = 0;
				$echk1 = 0;
				$echk2 = 0;
				break;
			case 101:		// Rename file
				$msg1 = 'Invalid characters in old filename.';
				$msg2 = 'Invalid characters in new filename.';
				$chk1 = verifyString::STR_FILENAME;
				$chk2 = verifyString::STR_FILENAME;
				$type1 = 0;
				break;	
			case 102:		// Copy/Move file
				$msg1 = 'Invalid characters in filename.';
				$msg2 = 'Invalid characters in target path.';
				$chk1 = verifyString::STR_FILENAME;
				$chk2 = verifyString::STR_PATHNAME;
				$type = 0;
				break;	
			case 200:		// Basic directory name
				$msg1 = 'Invalid characters in directory name.';
				$msg2 = '';
				$chk1 = verifyString::STR_FILENAME;
				$chk2 = -1;
				$type = 1;
				break;
			case 201:		// Rename directory
				$msg1 = 'Invalid characters in old directory name.';
				$msg2 = 'Invalid characters in new directory name.';
				$chk1 = verifyString::STR_FILENAME;
				$chk2 = verifyString::STR_FILENAME;
				$type = 1;
				break;	
			case 202:		// Move directory
				$msg1 = 'Invalid characters in directory name.';
				$msg2 = 'Invalid characters in target path.';
				$chk1 = verifyString::STR_FILENAME;
				$chk2 = verifyString::STR_PATHNAME;
				$type = 1;
				break;	
			case 203:		// Create directory
				$msg1 = 'Invalid characters in directory name.';
				$msg2 = 'Invalid characters in target path.';
				$chk1 = verifyString::STR_FILENAME;
				$chk2 = verifyString::STR_PATHNAME;
				$type = -1;
				break;	
		}

		// Run the file/dir/path name checks.
		$result = $vfystr->strchk($name1, '', '', $chk1);
		if ($result == false)
			$herr->puterrmsg($msg1);
		if (!empty($name2))
		{
			// Check the second name if needed.
			$result = $vfystr->strchk($name2, '', '', $chk2);
			if ($result == false)
				$herr->puterrmsg($msg2);
		}

		// Check for errors from above.
		if ($herr->checkState())
			handleError($herr->errorGetMessage());

		// Check to make sure that the user isn't trying to break out
		// of the virtual file tree.
		$realPath = $this->buildRealPath($basePath, $currentPath, $name1);
		$result = $this->checkDirectory($basePath, $realPath);
		if ($result == false)
			handleError('Filesystem Error: You are not allowed to exit the' .
				' virtual directory tree.');
		if (!empty($name2))
		{
			// If the second name was specified.
			$realPath = $this->buildRealPath($basePath, $currentPath, $name2);
			$result = $this->checkDirectory($basePath, $realPath);
			if ($result == false)
				handleError('Filesystem Error: You are not allowed to exit the' .
					' virtual directory tree.');
		}

		// Check source type.
		switch ($type)
		{
			case 0:
				if (!file_exists($name1))
					handleError('Filesystem Error: Selected file does' .
						' not exist.');
				break;
			case 1:
				if (!is_dir($name1))
					handleError('Filesystem Error: This command can only' .
						' be used with directories.');
				break;
			default:
				break;
		}
	}


	// Generates a string of random characters sutable for use
	// as temporary filenames.
	private function generateTempFilename($length = 0)
	{
		global $CONFIGVAR;

		if ($length == 0)
		{
			$length = $CONFIGVAR['files_random_filename_length']['value'];
			if ($length < 4 || $length > 128)
				return false;
		}
		$secure = (bool)false;
		$bin = openssl_random_pseudo_bytes($length, $secure);
		if ($bin === false) return false;
		if ($secure == false) return false;
		$b64 = base64_encode($bin);
		return $hex;
	}

	// Checks if the given path is outside the web server document
	// root directory.  Returns false if it is, true if not.
	private function checkDirectory($basePath, $path)
	{
		// Run a few checks
		if (strpos($path, '..', 0) != false) return false;
		if (strncmp($basePath, $path, strlen($basePath)) != 0) return false;
		return true;
	}

	// Determines the type of the given path/filename and returns
	// a text string indicating the type.  Returns unknown if the
	// type cannot be determined.
	private function determineFileType($file)
	{
		if (is_dir($file)) return 'Directory';
		if (is_link($file)) return 'Symlink';
		if (is_executable($file)) return 'Program';
		if (is_file($file)) return 'File';
		return 'Unknown';
	}

	// Converts the given mode to rwx format for unix systems.
	private function convertMode($mode)
	{
		$modelist = array('x', 'w', 'r', 'x', 'w', 'r', 'x', 'w', 'r');
		$text = '';
		for ($i = 0; $i < 9; $i++)
		{
			if (($mode & 0x00000001) != 0)
				$text = $modelist[$i] . $text;
			else
				$text = '-' . $text;
			$mode >>= 1;
		}
		return $text;
	}

	// Uploads one or more files onto the server.  If $randFilename is
	// set to true, then the files are renamed using a base64 random name
	// generator.  A mapping array which maps the given files to the temp
	// files is returned in that case.
	public function fileUpload($basePath, $currentPath, $sizeMax, $randFilename = false)
	{
		global $herr;
		global $vfystr;
	
		// Sets the time limit.  Needed on Windows only.
		if (identOS() == 2) set_time_limit(14400);
	
		// Check the request method.  This function only works with the
		// PUT method.
		if ($_SERVER['REQUEST_METHOD'] != 'PUT')
			handleError('Protocol Error: Uploading files requires the ' .
				'PUT method.');
	
		// For random filename submissions, this array maps the original
		// filenames to the system generated filenames.
		$fileLinkArray = array();

		// Build paths...
		$realPath = $this->buildRealPath($basePath, $currentPath);

		// ...and verify they are correct.
		$this->checkNamesPaths(200, $basePath, $currentPath, $realPath);

		// Make sure the target directory exists.
		if (!file_exists($realPath))
			handleError('Filesystem Error: Upload target directory ' .
				'does not exist.<br>Contact your administrator.');

		// Parse out our multipart form marker.
		$content = $_SERVER['CONTENT_TYPE'];
		$cxa = explode(';', $content);
		foreach ($cxa as $kx => $vx)
		{
			$position = strpos($vx, 'boundary=');
			if ($position === false) continue;
			$cxb = explode('=', $vx);
			break;
		}
		$markerStart = $cxb[1];
		$markerEnd = '--' . $cxb[1] . '--';
		unset($cxa, $cxb);
	
		// Save file to temp file
		$tempFile = tmpfile();
		if ($tempFile === false)
			handleError('Filesystem Error: Unable to open temporary file.' .
				'<br>XX23001');
		$inputStream = fopen('php://input', 'r');
		if ($inputStream === false)
			handleError('Network Error: Unable to open file input stream.' .
				'<br>XX23002');
		while (!feof($inputStream))
		{
			$data = fread($inputStream, self::BLOCKSIZE);
			if ($data === false)
				handleError('Network Error: Unable to read from network ' .
					'stream.<br>XX23003');
			$result = fwrite($tempFile, $data);
			if ($result === false)
				handleError('Filesystem Error: Unable to write to temporary ' .
					'file.<br>XX23004');
		}
		fclose($inputStream);
		fflush($tempFile);
	
		// Now perform the analysis on the temp file that we received
		// from the network.  The analysis phase basically looks for the
		// content boundary which denotes the beginning the multipart/form
		// header in the file and marks the location in an array.
		// This is used to split the temporary file into the individual
		// component files.
		$result = fseek($tempFile, 0, SEEK_SET);
		if ($result != 0)
			handleError('Filesystem Error: Unable to perform seek on temporary ' .
				'file.<br>XX23005');
		$formpart = array();
		$filepos = 0;
		while (!feof($tempFile))
		{
			// Read the tempfile data.
			$data = fread($tempFile, self::BLOCKSIZE);
			if ($data === false)
			{
				// If we get nothing, then we either got an eof or an error.
				if (feof($tempFile)) break;
				handleError('Filesystem Error: Unable to read temporary file.' .
					'<br>XX23006');
			}
	
			// Look for the marker.
			$position = strpos($data, $markerEnd);
			if ($position !== false) array_push($formpart, $position + $filepos);
			else
			{
				$position = strpos($data, $markerStart);
				if ($position !== false) array_push($formpart, $position + $filepos);
			}
	
			// If we hit the EOF, then we bail.
			if (feof($tempFile)) break;
	
			// Backup the file pointer just in case a marker was split across
			// reads.
			$result = fseek($tempFile, -self::BLOCKBACK, SEEK_CUR);
			if ($result != 0)
				handleError('Filesystem Error: Unable to perform seek on' .
				' temporary file.<br>XX23007');
			$filepos += self::BLOCKSIZE - self::BLOCKBACK;
		}
	
		// Now that we have everything that we need, we now jump to each
		// header location looking for filenames.  When we find one, we
		// compute the length to the next header and extract the data
		// into the file whose name is contained in the filename parameter.
		foreach($formpart as $kx => $vx)
		{
			// Set the file position to the start of the header.
			$result = fseek($tempFile, $vx, SEEK_SET);
			if ($result != 0)
				handleError('Filesystem Error: Unable to perform seek on' .
				' temporary file.<br>XX23008');
	
			// Read in the 4 lines of the header which consits of the
			// marker, content disposition, content type, and a blank
			// line.
			$header = array(
				0 => fgets($tempFile, self::BLOCKSIZE),
				1 => fgets($tempFile, self::BLOCKSIZE),
				2 => fgets($tempFile, self::BLOCKSIZE),
				3 => fgets($tempFile, self::BLOCKSIZE),
			);
			if (strcmp($header[0], $markerEnd) == 0) break;
			$filepos = ftell($tempFile);
			if ($filepos === false)
				handleError('Filesystem Error: Unable to retreive seek position' .
					' for temporary file.<br>XX23009');
	
			// The only one that we care about is 1.  If filename is not
			// present, then it's not a file and we skip it.
			$position = strpos($header[1], 'filename="');
			if ($position === false) continue;
	
			// Extract the filename.  Once we get to $fxa, because the filename
			// is in double quotes, it *should* be in index 1.
			$contDisp = explode(';', $header[1]);
			foreach($contDisp as $k2 => $v2)
			{
				$position = strpos($v2, 'filename');
				if ($position === false) continue;
				$fxa = explode('"', $v2);
				$filename = $fxa[1];
				$tempFilename = $filename;
				$result = $vfystr->strchk($filename, '', '', verifyString::STR_FILENAME);
				if (!$result)
				{
					$herr->puterrmsg('Processing Error: Multi-Part form header is ' .
						'malformed.<br>XX23010 OFFSET=' . $filepos);
					handleError($herr->errorGetMessage());
				}
				break;
			}

			// Now that we have the filename that was given to us, we need to
			// check to see if random filename is to be used.  If it is, then
			// we generate a random filename, and save both file names into the
			// holding array so we can return this information to the caller.
			if ($randFilename == true)
			{
				$fileCheck = true;
				while ($fileCheck)
				{
					// After we generate a filename, we need to check to make
					// sure that it does not already exist.
					$tfile = $this::generateTempFilename();
					$fileCheck = file_exists($realPath . '/' . $tfile);
				}
				$tarray = array(
					$filename => $tfile,
				);
				array_push($fileLinkArray, $tarray);
				$filename = $tfile;
			}
	
			// Now that we have the filename, we need to get the location of the
			// next marker from $formpart which should be at $kx + 1.  This will
			// allow us to compute the length of the data stream that we need
			// to copy into the new file.
			$fileLength = $formpart[$kx + 1] - $filepos - 2;	// XXX May need adjustment.
			if ($fileLength < 0)
				handlerError('Processing Error: File length is negative!<br>' .
				'XX23011 FILE=' . $filename);
			
			// Check to see if the file length does not exceed maximum size.
			// If it does, then we throw an error.
			if ($sizeMax > 0)
			{
				if ($fileLength > $sizeMax)
					handleError('File ' . $tempFilename . ' exceeds the maximum size of ' .
					$sizeMax . ' bytes.');
			}
	
			// Now we copy the data from the temp file over to the new file.
			$outputStream = fopen($realPath . '/' . $filename, 'w');
			if ($outputStream === false)
				handleError('Filesystem Error: Unable to open output stream for ' .
					'writing<br>XX23012 FILE=' . $filename);
			while ($fileLength > 0)
			{
				if ($fileLength < self::BLOCKSIZE)
				{
					$data = fread($tempFile, $fileLength);
					if ($data === false)
						handleError('Filesystem Error: Unable to read from ' .
							'temporary file.<br>XX23013');
					$fileLength = 0;
				}
				else
				{
					$data = fread($tempFile, self::BLOCKSIZE);
					if ($data === false)
						handleError('Filesystem Error: Unable to read from ' .
							'temporary file.<br>XX23014');
					$fileLength -= self::BLOCKSIZE;
				}
				fwrite($outputStream, $data);
				if ($result === false)
					handleError('Filesystem Error: Unable to write to output ' .
						'stream.<br>XX23004 FILE=' . $filename);
			}
			fflush($outputStream);
			fclose($outputStream);
		}

		// Close the temp file.
		fclose($tempFile);

		// If this was a student submission, then we return the filename
		// mapping array to the caller.
		if ($randFilename == true) return $fileLinkArray;
		return;
	}

	// Generates a URL with a random token that is used by the client
	// to download the specific file.
	public function fileDownloadUrl($basePath, $currentPath, $filename,
		$baseUrl, $modPathFile)
	{
		global $CONFIGVAR;

		// Build paths
		$realPath = $this->buildRealPath($basePath, $currentPath, $filename);

		// Check to make sure that there are no invalid characters and
		// that the current paths are valid.
		$this->checkNamesPaths(100, $basePath, $currentPath, $filename);

		// Generate a security access token and place it into the user's
		// session state.
		$token = $this->generateTempFile(
			$CONFIGVAR['download_token_length']['value']);
		if ($token == false)
			handleError('OpenSSL Error: Failed to generate download token.');
		$_SESSION[$token] = $realPath;

		// Now build the URL
		$url = $baseUrl . '/' . $modPathFile . '?download=' . $token;

		// Return the full URL to the caller.
		return $url;
	}

	// Initiates a file download to the client.
	public function fileDownload($token)
	{
		// Check the download token
		if (!isset($_SESSION[$token]))
			handleError('Security Violation: Invalid download token.');
		$realPath = $_SESSION[$token];
		unset($_SESSION[$token]);

		// Send the file
		if (file_exists($realPath)) {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . basename($realpath));
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($realPath));
			readfile($realPath);
		}
		else http_response_code(404);
	}

	// Generates a URL with a random token that is used by the client
	// to view the specific file.
	public function fileViewUrl($basePath, $currentPath, $filename,
		$baseUrl, $modPathFile)
	{
		global $CONFIGVAR;

		// Build paths
		$realPath = $this->buildRealPath($basePath, $currentPath, $filename);

		// Check to make sure that there are no invalid characters and
		// that the current paths are valid.
		$this->checkNamesPaths(100, $basePath, $currentPath, $filename);

		// Generate a security access token and place it into the user's
		// session state.
		$token = $this->generateTempFile(
			$CONFIGVAR['download_token_length']['value']);
		if ($token == false)
			handleError('OpenSSL Error: Failed to generate download token.');
		$_SESSION[$token] = $realPath;

		// Now build the URL
		$url = $baseUrl . '/' . $modPathFile . '?view=' . $token;

		// Return the full URL to the caller.
		return $url;
	}

	// Instructs the client to view the file.
	public function fileView($token)
	{
		// Check the download token
		if (!isset($_SESSION[$token]))
			handleError('Security Violation: Invalid file viewing token.');
		$realPath = $_SESSION[$token];
		unset($_SESSION[$token]);

		// Send the file
		if (file_exists($realPath)) {
			$ctype = mimeTypes::determineMime($realPath);
			header('Content-Type: ' . $ctype);
			header('Content-Disposition: inline');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($realPath));
			readfile($realPath);
		}
		else http_response_code(404);
	}

	// Renames a file in the given directory.
	public function fileRename($basePath, $currentPath, $oldFile, $newFile)
	{
		// Build paths
		$realPath = $this->buildRealPath($basePath, $currentPath);
		$realFileOld = $this->buildRealPath($basePath, $currentPath, $oldFile);
		$realFileNew = $this->buildRealPath($basePath, $currentPath, $newFile);

		// Check to make sure that there are no invalid characters and
		// that the current paths are valid.
		$this->checkNamesPaths(101, $basePath, $currentPath, $realFileOld, $realFileNew);

		// Check to make sure that $oldFile exists and $newFile doesn't.
		if (!file_exists($realFileOld))
			handleError('The selected file with the name ' . $oldFile
				. ' does not exist.');
		if (file_exists($realFileNew))
			handleError('A file with the name ' . $newFile
				. ' already exists.');

		// Everything is good, so rename the file.
		$result = rename($realFileOld, $realFileNew);
		if ($result == false)
			handleError('Filesystem Error: Rename file failed.');
	}

	// Moves a file to the target path.
	public function fileMove($basePath, $currentPath, $filename, $newPath)
	{
		// Build paths
		$realPath = $this->buildRealPath($basePath, $currentPath);
		$pathFile = $this->buildRealPath($basePath, $currentPath, $filename);
		$target = $this->buildRealPath($basePath, $currentPath, $newPath);

		// Check to make sure that there are no invalid characters and
		// that the current paths are valid.
		$this->checkNamesPaths(102, $basePath, $currentPath, $pathFile, $targetPath);

		// Check to make sure that the orig file exists and the target
		// file doesn't.
		if (!file_exists($pathFile))
			handleError('The selected file with the name ' . $filename
				. ' does not exist.');
		if (file_exists($targetPath . '/' . $filename))
			handleError('A file with the name ' . $filename .
				' already exists.');

		// Everything is good, so rename the file.
		$result = rename($pathFile, $targetPath . '/' . $filename);
		if ($result == false)
			handleError('Filesystem Error: Move file failed.');
	}

	// Copies a file to the target path.
	public function fileCopy($basePath, $currentPath, $filename, $newPath)
	{
		// Build paths
		$realPath = $this->buildRealPath($basePath, $currentPath);
		$pathFile = $this->buildRealPath($basePath, $currentPath, $filename);
		$target = $this->buildRealPath($basePath, $currentPath, $newPath);

		// Check to make sure that there are no invalid characters and
		// that the current paths are valid.
		$this->checkNamesPaths(102, $basePath, $currentPath, $pathFile, $targetPath);

		// Check to make sure that the orig file exists and the target
		// file doesn't.
		if (!file_exists($pathFile))
			handleError('The selected file with the name ' . $filename
				. ' does not exist.');
		if (file_exists($targetPath . '/' . $filename))
			handleError('A file with the name ' . $filename .
				' already exists.');

		// Everything is good, so rename the file.
		$result = copy($pathFile, $targetPath);
		if ($result == false)
			handleError('Filesystem Error: Copy file failed.');
	}

	// Removes the specified file from the server.
	public function fileDelete($basePath, $currentPath, $filename)
	{
		// Build paths
		$realPath = $this->buildRealPath($basePath, $currentPath, $filename);

		// Check to make sure that there are no invalid characters and
		// that the current paths are valid.
		$this->checkNamesPaths(100, $basePath, $currentPath, $filename);

		// Check to make sure that the file exits.
		if (!file_exists($realPath))
			handleError('The selected file with the name ' . $filename
				. ' does not exist.');

		// Everything is good, remove the file.
		$result = unlink($realPath);
		if ($result == false)
			handleError('Filesystem Error: Remove file failed.');
	}	

	// Returns details about a file.
	public function fileDetail($basePath, $currentPath, $filename)
	{
		// Build paths
		$realPath = $this->buildRealPath($basePath, $currentPath, $filename);

		// Check to make sure that there are no invalid characters and
		// that the current paths are valid.
		$this->checkNamesPaths(100, $basePath, $currentPath, $filename);

		// Check to make sure that the file exits.
		if (!file_exists($realPath))
			handleError('The selected file with the name ' . $filename
				. ' does not exist.');

		// Get file statistics.
		$filestat = stat($realPath);
		if ($filestat === false)
			handleError('Filesystem Error: Unable to get file status.');
		$finfo = finfo_open(FILEINFO_MIME);
		if (is_resource($finfo) == true)
		{
			$mtype = finfo_file($finfo, $realPath, FILEINFO_MIME_TYPE);
			$mencode = finfo_file($finfo, $realPath, FILEINFO_MIME_ENCODING);
		}
		else
		{
			$mtype = mimeTypes::determineMime($filename);
			$mencode = 'unknown';
		}
		
		// Now build the information text.
		$fileinfo = 'Name: ' . $filename. chr(13);
		$fileinfo .= 'Path: ' . $currentPath . chr(13);
		$fileinfo .= 'Type: ' . $this->determineFileType($filepath) . chr(13);
		$fileinfo .= 'MIME Type: ' . $mtype . chr(13);
		$fileinfo .= 'MIME Encoding: ' . $mencode . chr(13);
		$fileinfo .= 'Size: ' . $filestat['size'] . chr(13);
		$fileinfo .= 'Blocks: ' . $filestat['blocks'] . chr(13);
		$fileinfo .= 'Mode: ' . $this->convertMode($filestat['mode']) .
			' (' . $filestat['mode'] . ')' . chr(13);
		$fileinfo .= 'UserID: ' . $filestat['uid'] . chr(13);
		$fileinfo .= 'GroupID: ' . $filestat['gid'] . chr(13);
		$fileinfo .= 'Link Count: ' . $filestat['nlink'] . chr(13);
		$fileinfo .= 'Access Time: ' . timedate::unix2canonical($filestat['atime']) . chr(13);
		$fileinfo .= 'Modify Time: ' . timedate::unix2canonical($filestat['mtime']) . chr(13);
		$fileinfo .= 'iNode  Time: ' . timedate::unix2canonical($filestat['ctime']) . chr(13);
		$fileinfo .= 'Device No: ' . $filestat['dev'] . chr(13);
		$fileinfo .= 'Device Type: ' . $filestat['rdev'] . chr(13);
		$fileinfo .= 'iNode No: ' . $filestat['ino'] . chr(13);
		$fileinfo .= 'Block Size: ' . $filestat['blksize'] . chr(13);

		// Return
		return $fileinfo;
	}

	// Directories

	// Moves up one directory level.  Returns the new path.
	public function directoryMoveUp($basePath, $currentPath)
	{
		global $herr;
		global $vfystr;

		// Check to make sure that there are no invalid characters.
		$result = $vfystr->strchk($currentPath, '', '', verifyString::STR_PATHNAME);
		if ($result == false)
			handleError($herr->errorGetMessage());

		// Take the last directory out of the string.
		$pathArray = explode('/', $currentPath);
		unset($pathArray[count($pathArray) - 1]);
		$newPath = implode('/', $pathArray);

		// Check to make sure that we are allowed to go into the given
		// directory.
		$result = $this->checkDirectory($basePath, $newPath);
		if ($result == false)
			handleError('Filesystem Error: You are not allowed to exit ' .
				'the root directory.');
		
		// We are good, return
		return $newPath;
	}

	// Moves down one directory level.  Returns the new path.
	public function directoryMoveDown($basePath, $currentPath, $dirname)
	{
		global $herr;
		global $vfystr;

		// Check to make sure that there are no invalid characters.
		$result = $vfystr->strchk($filename, '', '', verifyString::STR_FILENAME);
		if ($result == false)
			$herr->puterrmsg('Invalid characters in directory name.');
		$result = $vfystr->strchk($currentPath, '', '', verifyString::STR_PATHNAME);
		if ($result == false)
			$herr->puterrmsg('Invalid characters in directory path.');
		if ($herr->checkState())
			handleError($herr->errorGetMessage());

		// Generate new path
		if ($currentPath == '/')
			$newPath = '/' . $dirname;
		else
			$newPath = $currentPath . '/' . $dirname;

		// Check to make sure that the selected item is a directory.
		if (!is_dir($basePath . $newPath))
			handleError('Filesystem Error: You must select a directory ' .
				'in order to use this command.');

		// Check to make sure that we are allowed to go into the given
		// directory.
		$result = $this->checkDirectory($basePath, $newPath);
		if ($result == false)
			handleError('Filesystem Error: You are not allowed to exit ' .
				'the root directory.');
		
		// We are good, return
		return $newPath;
	}

	// Creates a new directory.
	public function directoryCreate($basePath, $currentPath, $newDir)
	{
		// Build paths
		$realPath = $this->buildRealPath($basePath, $currentPath, $newDir);

		// Check to make sure that there are no invalid characters and
		// that the current paths are valid.
		$this->checkNamesPaths(203, $basePath, $currentPath, $newDir);

		// Make sure that the directory does not already exist.
		if (file_exists($realPath))
			handleError('A directory with the name ' . $newDir .
				' already exists.');

		// Everything is good, so create the directory and set the group.
		$result = mkdir($realPath . '/' . $dirName, 0775);
		if ($result == false)
			handleError('Filesystem Error: Make directory failed.');
		if (identOS() == 0)
		{
			$result = chgrp($realPath . '/' . $dirName, 'www');
			if ($result == false)
				handleError('Filesystem Error: Unable to set directory GID.');
		}
	}

	// Renames a directory.
	public function directoryRename($basePath, $currentPath, $oldDir, $newDir)
	{
		// Build paths
		$realPath = $this->buildRealPath($basePath, $currentPath);
		$realDirOld = $this->buildRealPath($basePath, $currentPath, $oldDir);
		$realDirnew = $this->buildRealPath($basePath, $currentPath, $newDir);

		// Check to make sure that there are no invalid characters and
		// that the current paths are valid.
		$this->checkNamesPaths(201, $basePath, $currentPath, $oldDir, $newDir);

		// Check to make sure that $oldDir exists and $newDir doesn't.
		if (!file_exists($realFileOld))
			handleError('The selected directory with the name ' . $oldDir
				. ' does not exist.');
		if (file_exists($realFileNew))
			handleError('A directory with the name ' . $newDir
				. ' already exists.');

		// Everything is good, so rename the file.
		$result = rename($realDirOld, $realDirNew);
		if ($result == false)
			handleError('Filesystem Error: Rename directory failed.');
	}

	// Moves a directory.
	public function directoryMove($basePath, $currentPath, $dirname, $newPath)
	{
		// Build paths
		$realPath = $this->buildRealPath($basePath, $currentPath);
		$realDirOld = $this->buildRealPath($basePath, $currentPath, $dirname);
		$realDirNew = $this->buildRealPath($basePath, $newPath, $dirname);

		// Check to make sure that there are no invalid characters and
		// that the current paths are valid.
		$this->checkNamesPaths(202, $basePath, $currentPath, $oldDir, $newDir);

		// Check to make sure that $oldDir exists and $newDir doesn't.
		if (!file_exists($realDirOld))
			handleError('The selected directory with the name ' . $dirname
				. ' does not exist.');
		if (file_exists($realDirnew))
			handleError('A directory with the name ' . $dirname
				. ' already exists.');

		// Everything is good, so rename the file.
		$result = rename($realDirOld, $realDirNew);
		if ($result == false)
			handleError('Filesystem Error: Rename directory failed.');
	}

	// Removes a directory.
	public function directoryRemove($basePath, $currentPath, $dirname)
	{
		global $herr;
		global $vfystr;

		// Build paths
		$realPath = $this->buildRealPath($basePath, $currentPath, $filename);

		// Check to make sure that there are no invalid characters and
		// that the current paths are valid.
		$this->checkNamesPaths(200, $basePath, $currentPath, $filename);

		// Check to make sure that $oldDir exists and $newDir doesn't.
		if (!file_exists($realPath))
			handleError('The selected directory with the name ' . $filename
				. ' does not exist.');

		// Check to make sure that the directory is empty.
		$rxa = scandir($realPath);
		if (count($rxa) > 2)
			handleError('Filesystem Error: Unable to remove directory ' .
				'because it is not empty.');

		// Everything is good, remove the file.
		$result = rmdir($realPath);
		if ($result == false)
			handleError('Filesystem Error: Remove file failed.');
	}

	// Builds a list of files and directories.
	public function buildDirectoryList($basePath, $currentPath)
	{
		global $CONFIGVAR;

		// Set default path if path variable is empty.
		if (empty($currentPath)) $currentPath = '/';
	
		// Set the current path as hidden data.
		$hidden = array(
			'type' => html::TYPE_HIDE,
			'fname'=> 'hiddenForm',
			'name' => 'hidden',
			'data' => $path,
		);
	
		// Build paths
		$realPath = $this->buildRealPath($basePath, $currentPath);

		// Check to make sure that there are no invalid characters and
		// that the current paths are valid.
		$result = $vfystr->strchk($currentPath, '', '', verifyString::STR_PATHNAME);
		if ($result == false)
			$herr->puterrmsg('Invalid characters in directory path.');
		if ($herr->checkState())
			handleError($herr->errorGetMessage());
	
		// Setup
		$filelist = array();
		
		// Get directory listing
		$file = scandir($realPath);
		if ($file === false)
			handleError('Filesystem Error: Unable to get directory listing: ' .
				$currentPath);
		
		// Process entries
		foreach($file as $kx => $vx)
		{
			if ($vx == '.') continue;
			if ($vx == '..') continue;
			$stat = stat($realPath . '/' . $vx);
			if ($stat == false) continue;
			$temp = array(
				'name' =>		$vx,
				'type' =>		$this->determineFileType($realPath . '/' . $vx),
				'size' =>		$stat['size'],
				'ctime' =>		timedate::unix2canonical($stat['mtime']),
				'mode' =>		$this->convertMode($stat['mode']),
				'desc' =>
					'Name: ' .			$vx . '<br>' .
					'Type: ' .			$this->determineFileType($realPath . '/' . $vx) . chr(13) .
					'Size: ' .			$stat['size'] . chr(13) .
					'Mode: ' .			$this->convertMode($stat['mode']) .
						'  (' . $stat['mode'] . ')' . chr(13) .
					'UID: ' . 			$stat['uid'] . chr(13) .
					'GID: ' . 			$stat['gid'] . chr(13) .
					'Access Time: ' .	timedate::unix2canonical($stat['atime']) . chr(13) .
					'Modify Time: ' .	timedate::unix2canonical($stat['mtime']) . chr(13),
			);
			$filelist[$vx] = $temp;
		}
		
		// Setup selection table
		$list = array(
			'type' => html::TYPE_RADTABLE,
			'name' => 'select_item',
			'clickset' => true,
			'condense' => true,
			'hover' => true,
			'titles' => array(
				// Add column titles here
				'Name',
				'Type',
				'Size',
				'Changed',
				'Mode',
			),
			'tdata' => array(),
			'tooltip' => array(),
		);
	
		if (count($filelist) > 0)
		{
			foreach ($filelist as $kx => $vx)
			{
				$tdata = array(
					// These are the values that show up under the columns above.
					// The *FIRST* value is the value that is sent when a row
					// is selected.  AKA Key Field.
					$vx['name'],
					$vx['name'],
					$vx['type'],
					$vx['size'],
					$vx['ctime'],
					$vx['mode'],
				);
				array_push($list['tdata'], $tdata);
				array_push($list['tooltip'], $vx['desc']);
			}
		}
	
		// Generate rest of page.
		$data = array(
			$hidden,
			array(
				'type' => html::TYPE_HEADING,
				'message1' => 'Path: ',
				'message2' => $currentPath,
			),
			array('type' => html::TYPE_TOPB1),
			array('type' => html::TYPE_WD75OPEN),
			array(
				'type' => html::TYPE_HIDEOPEN,
				'name' => 'fileInputDiv',
				'hidden' => true,
			),
			array(
				'type' => html::TYPE_FILE,
				'bname' => 'fileSubmit',
				'fname' => 'fileInputForm',
				'name' => 'fileInput',
				'action' => 'fileUpload()',
				'fsize' => 8,
				'lsize' => 2,
			),
			array('type' => html::TYPE_TOPB2),
			array('type' => html::TYPE_HIDECLOSE),
			array(
				'type' => html::TYPE_FORMOPEN,
				'name' => 'select_table',
			),
	
			// Enter custom data here.
			$list,
	
			array('type' => html::TYPE_FORMCLOSE),
			array('type' => html::TYPE_WDCLOSE),
			array('type' => html::TYPE_BOTB1),
			array('type' => html::TYPE_VTAB10),
		);
	
		// Return HTML
		return html::pageAutoGenerate($data);
	}
}

// Autoinstantiate the class
$files = new filesClass();


?>