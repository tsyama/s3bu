<?php

use Aws\S3\S3Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require 'vendor/autoload.php';
require 'config.php';
require 'functions.php';

$loggingPath = LOG_DIRECTORY . date('Ym', time()) . '.log';
$logger = new Logger('s3bu');
$logger->pushHandler(new StreamHandler($loggingPath, Logger::INFO));

$logger->info('Backup Started: ' . SOURCE_DIRECTORY);

$fileList = getFileList(SOURCE_DIRECTORY);

foreach ($fileList as $file) {
	try {
		$s3 = new S3Client(array(
			'version' => 'latest',
			'region' => 'ap-northeast-1',
			'credentials' => array(
				'key' => ACCESS_KEY,
				'secret' => SECRET_ACCESS_KEY,
			),
		));

		$s3FileName = str_replace(SOURCE_DIRECTORY, S3_DIRECTORY, $file);
		$s3->putObject(array(
			'Bucket' => BUCKET,
			'Key' => $s3FileName,
			'SourceFile' => $file,
		));
		$logger->info('Backup Succeed: ' . $file);
	} catch (\Exception $e) {
		$logger->error('Backup Failed: ' . $file);
		$logger->error($e);
	}
}

$logger->info('Backup Complete');