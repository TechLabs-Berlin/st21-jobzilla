<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
 require "vendor/autoload.php";
 use Aws\S3\S3Client;
 class Aws3{
	 private static $_s3Obj = NULL;
	public function __construct(){
		self :: $_s3Obj = S3Client::factory([
			'version' => 'latest',
			'region' => 'eu-west-1'
		]);
	}	
	
	public function addBucket($bucketName){
		$result = self :: $_s3Obj->createBucket(array(
			'Bucket'=>$bucketName,
			'LocationConstraint'=> 'eu-west-1'));
		return $result;	
	}
	public static function sendFile($bucketName, $filename){
		$result = self :: $_s3Obj->putObject(array(
				'Bucket' => $bucketName,
				'Key' => $filename['name'],
				'SourceFile' => $filename['tmp_name'],
				'ACL' => 'public-read'
		));

		return $result['ObjectURL']."\n";
	}

	public static function deleteObject($bucket, $url ){
		return self :: $_s3Obj->deleteObject([
			'Bucket' => $bucket,
			'Key'    => $url
		]);
	}
	public static function getObjectInfo($bucket, $url){
		try{
			$result = self :: $_s3Obj->getObject([
				'Bucket' => $bucket,
				'Key'    => $url
			]);
			 return $result['ContentType'];
		}catch (exception  $e) {
			return false;
		}
	}	
 }