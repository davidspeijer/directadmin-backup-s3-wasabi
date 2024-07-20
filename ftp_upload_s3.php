<?php
/**
 * @author Harry Tang <harry@powerkernel.com>
 * @link https://powerkernel.com
 * @copyright Copyright (c) 2018 Power Kernel
 */

/* @var $ftp_local_file string $argv[1] */
/* @var $ftp_remote_file string $argv[2] */

use Aws\Exception\AwsException; 
use Aws\Exception\MultipartUploadException;
use Aws\S3\MultipartUploader;
use Aws\S3\S3Client;

require __DIR__ . '/vendor/autoload.php';
$conf = require __DIR__ . '/config.php';

$bucket = $conf['bucket'];
$ftp_local_file=$argv[1];
$ftp_remote_file=$argv[2];

// Wasabi Cloud S3 connection options
$opts = [
    'credentials' => [
        'key' => $conf['credentials']['key'],
        'secret' => $conf['credentials']['secret'],
    ],
    'endpoint' => $conf['endpoint'],
    'region' => $conf['region'],
    'version' => $conf['version'],
    'use_path_style_endpoint' => $conf['use_path_style_endpoint'],
];
if (!empty($conf['endpoint'])) {
    $opts['endpoint'] = $conf['endpoint'];
}

$client = new S3Client($opts);

// check bucket exist
$exist = false;
$buckets = $client->listBuckets();
if ($buckets) {
    foreach ($buckets['Buckets'] as $i => $b) {
        if ($b['Name'] == $bucket) {
            $exist = true;
        }
    }
}

// If bucket is not existing exit
if (!$exist) {
    exit();
}

// Upload
$uploader = new MultipartUploader($client, $ftp_local_file, [
    'bucket' => $bucket,
    'key' => date('Y-m-d') . '/' . $ftp_remote_file,
]);

try {
    $result = $uploader->upload();
    echo "Upload complete: {$result['ObjectURL']}\n";
} catch (MultipartUploadException $e) {
    echo $e->getMessage() . "\n";
}
