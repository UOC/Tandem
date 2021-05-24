<?php

require_once dirname(__FILE__).'/../classes/lang.php';
require_once dirname(__FILE__).'/../classes/utils.php';
require_once dirname(__FILE__).'/../classes/constants.php';
require_once dirname(__FILE__).'/../classes/gestorBD.php';


$id = isset($_REQUEST['id'])?intval($_REQUEST['id'],10):0;
$download_url = isset($_REQUEST['download_url'])?$_REQUEST['download_url']:'';
if (strlen($download_url)>0) {
if (defined('TMP_FOLDER') && defined('AWS_S3_BUCKET') && defined('AWS_S3_FOLDER') && defined('AWS_S3_USER') && defined('AWS_S3_SECRET')) {

require_once dirname(__FILE__).'/../lib/vendor/autoload.php';
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
			$file_nameArray = explode('/', $download_url);
			$file_name = $file_nameArray[count($file_nameArray)-1];
			$file_name = str_replace('/','_',$download_url);
			$file_name = str_replace(':','_',$file_name);
			$file_name = str_replace('=','_',$file_name);
			//Download to local and upload to s3
			$ch = curl_init($download_url);
			$fp = fopen(TMP_FOLDER.'/'.$file_name, 'wb');
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);
			$ext = pathinfo(TMP_FOLDER.'/'.$file_name, PATHINFO_EXTENSION);
			// Instantiate an S3 client
			$s3 = S3Client::factory(array(
			    'key'    => AWS_S3_USER,
			    'secret' => AWS_S3_SECRET,
			));

			// Upload a publicly accessible file. The file size, file type, and MD5 hash
			// are automatically calculated by the SDK.
			try {
			    $s3->putObject(array(
			        'Bucket' => AWS_S3_BUCKET,
			        'Key'    => AWS_S3_FOLDER.'/'.$id.'.'.$ext,
			        'Body'   => fopen(TMP_FOLDER.'/'.$file_name, 'r'),
			        'ACL'    => 'public-read',
			    ));
			    unlink(TMP_FOLDER.'/'.$file_name);
			} catch (S3Exception $e) {
var_dump($e);
			    error_log( "There was an error uploading the file. ".$e->getMessage() );
			}
		}
}
