<?php
/*

SEA-CORE International Ltd.
SEA-CORE Development Group

PHP Web Application File Handling Library


*/

const BASEDIR = '../libs/';
require_once BASEDIR . 'confbase.php';
require_once BASEDIR . 'error.php';
require_once BASEDIR . 'vfystr.php';
// require_once BASEDIR . '.php';
// require_once BASEDIR . '.php';


interface filesInterface
{
	// Some operational constants
	const BLOCKSIZE = 4096;
	const BLOCKBACK = 512;

	// Methods
	public function generateTempFilename();
	public function view($filename);
	public function download($filename);
	public function upload($targetdir, $student = false);
}


class filesClass implements filesInterface
{
	// Generates a string of random characters sutable for use
	// as temporary filenames.
	public function generateTempFilename()
	{
		global $CONFIGVAR;

		if ($CONFIGVAR['files_random_filename_length']['value'] < 4 ||
			$CONFIGVAR['files_random_filename_length']['value'] > 128)
			return false;
		$bin = openssl_random_pseudo_bytes(
			$CONFIGVAR['files_random_filename_length']['value']);
		if ($bin === false) return false;
		$b64 = base64_encode($bin);
		return $hex;
	}

	// Sends the specified file to the client for viewing.
	public function view($filename)
	{
		// Send the file.
		if (file_exists($filename)) {
			$ctype = mimeTypes::determineMime($filename);
			header('Content-Type: ' . $ctype);
			header('Content-Disposition: inline');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($filename));
			readfile($filename);
		}
		else http_response_code(404);
	}

	// Sends the specified file to the client for download.
	public function download($filename)
	{
		// Send the file
		if (file_exists($filename)) {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . basename($filename));
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($filename));
			readfile($filename);
		}
		else http_response_code(404);
	}

	// Uploads one or more files onto the server.  If $student is set to
	// true, then the files are placed into the configured turnin directory
	// with random filenames.  Otherwise, they are placed into the specified
	// directory.
	public function upload($targetdir, $student = false)
	{
		global $herr;
		global $vfystr;
		global $CONFIGVAR;
	
		// Sets the time limit.  Needed on Windows only.
		if (identOS() == 2) set_time_limit(14400);
	
		// Check the request method.  This function only works with the
		// PUT method.
		if ($_SERVER['REQUEST_METHOD'] != 'PUT')
			handleError('Protocol Error: Uploading files requires the ' .
				'PUT method.');
	
		// For student submissions, this array maps the original filenames to
		// the system generated filenames.
		$fileLinkArray = array();

		if ($student == true)
		{
			$realPath = $CONFIGVAR['files_base_path']['value'] . '/' .
				$CONFIGVAR['files_turned_in']['value'];
		}
		else
		{
			$realPath = $CONFIGVAR['files_base_path']['value'] . '/' .
				$targetDir;
		}
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
			// check to see if this is a student account.  If it is, then we
			// generate a random filename, and save both file names into the
			// holding array so we can return this information to the caller.
			// If this is not a student account, then we have an instructor or
			// someone else, so we just use the provided filename.
			if ($student == true)
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
		if ($student == true) return $fileLinkArray;
		return;
	}
}

// Autoinstanciate the class
$files = new filesClass();

?>