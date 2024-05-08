<?php

namespace App\Service;

use Aws\S3\S3Client;

class S3ClientService
{
    private S3Client $client;

    public function __construct(
        string $s3Region,
        string $s3AccessKeyId,
        string $s3SecretAccessKey,
        string $s3Endpoint
    ) {
        $this->client = new S3Client([
            'region'  => $s3Region,
            'version' => 'latest',
            'credentials' => [
                'key'    => $s3AccessKeyId,
                'secret' => $s3SecretAccessKey,
            ],
            'endpoint' => $s3Endpoint,
            'use_path_style_endpoint' => true,
        ]);
    }

    public function getClient(): S3Client
    {
        return $this->client;
    }
}
