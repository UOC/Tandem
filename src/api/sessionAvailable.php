<?php
require_once dirname(__FILE__).'/../classes/lang.php';
require_once dirname(__FILE__).'/../classes/utils.php';
require_once dirname(__FILE__).'/../classes/constants.php';
require_once dirname(__FILE__).'/../classes/gestorBD.php';
require_once dirname(__FILE__).'/../classes/IntegrationTandemBLTI.php';
require_once dirname(__FILE__).'/../lib/vendor/autoload.php';
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

$id = isset($_REQUEST['id'])?intval($_REQUEST['id'],10):0;
$download_url = isset($_REQUEST['download_url'])?$_REQUEST['download_url']:'';

$tandem = false;
$gestorBD = new GestorBD();
$return = new stdClass();
$return->result = 'error';
$return->sessionid = $id;

if ($id>0) {
	$tandem = $gestorBD->obteTandem($id); 
}

if ($tandem) {	

	$gestorBD->updateTandemSessionAvailable($id);
	if (strlen($download_url)>0) {
		$gestorBD->updateDownloadVideoUrlFeedbackTandemByTandemId($id, $download_url);
		if (defined('AWS_URL') &&  defined('TMP_FOLDER') && defined('AWS_S3_BUCKET') && defined('AWS_S3_FOLDER') && defined('AWS_S3_USER') && defined('AWS_S3_SECRET')) {
			$file_nameArray = explode('/', $download_url);
			$file_name = $file_nameArray[count($file_nameArray)-1];
			$file_name = str_replace('/','_',$file_name);
			$file_name = str_replace(':','_',$file_name);
			$file_name = str_replace('=','_',$file_name);
			//Download to local and upload to s3
			try {
			$ch = curl_init($download_url);
			$fp = fopen(TMP_FOLDER.'/'.$file_name, 'wb');
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);
				if (file_exists(TMP_FOLDER.'/'.$file_name)) {
					// Instantiate an S3 client
					$s3 = S3Client::factory(array(
					    'key'    => AWS_S3_USER,
					    'secret' => AWS_S3_SECRET,
					));

					// Upload a publicly accessible file. The file size, file type, and MD5 hash
					// are automatically calculated by the SDK.
					    $s3->putObject(array(
					        'Bucket' => AWS_S3_BUCKET,
					        'Key'    => AWS_S3_FOLDER.'/'.$file_name,
					        'Body'   => fopen(TMP_FOLDER.'/'.$file_name, 'r'),
					        'ACL'    => 'public-read',
					    ));
			    	unlink(TMP_FOLDER.'/'.$file_name);
			    } else {
			    	error_log( "There was an error downloading the file. FROM ".$download_url );
			    }
			} catch (S3Exception $e) {
			    error_log( "There was an error uploading the file. ".$e->getMessage() );
			}
		}
	}
	$return->result = 'ok';	

	
}
else {
	$return->error = 'Unknown tandem id';
}
echo json_encode($return);