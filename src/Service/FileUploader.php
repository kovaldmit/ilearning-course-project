<?php

namespace App\Service;

use Aws\S3\S3Client;

class FileUploader
{
    private S3Client $s3Client;
    private string $s3BucketName;

    private string $s3PublicDomain;

    public function __construct(S3ClientService $s3ClientService, string $s3BucketName, string $s3PublicDomain)
    {
        $this->s3Client = $s3ClientService->getClient();
        $this->s3BucketName = $s3BucketName;
        $this->s3PublicDomain = $s3PublicDomain;
    }

    public function uploadFile($file, $fileName): string
    {
        $result = $this->s3Client->putObject([
            'Bucket' => $this->s3BucketName,
            'Key'    => $fileName,
            'SourceFile' => $file->getRealPath(),
            'ACL'    => 'public-read',
        ]);

        return $result->get('ObjectURL');
    }

    public function getFileUrl($fileName): string
    {
        return $this->s3PublicDomain . '/' . $fileName;
    }
}
