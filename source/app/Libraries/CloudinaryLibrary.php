<?php

namespace App\Libraries;

use App\CustomAR\Record;
use Config\Services;
use Cloudinary;

class CloudinaryLibrary {

    /**
     * @var int
     */
    private static $expireTime = 1800; // 30minutes

    /**
     * The constructor
     */
    public function __construct()
    {
        Cloudinary::config(
            [
                'cloud' => [
                    'cloud_name' => getenv('CLOUDINARY_NAME'),
                    'api_key'    => getenv('CLOUDINARY_API_KEY'),
                    'api_secret' => getenv('CLOUDINARY_API_SECRET'),
                    'secure'     => true
                ],
            ]
        );
    }

    /**
     * Upload media to cloudinary
     *
     * @param string $path
     * @param array $option
     */
    public function upload(string $path, array $option)
    {
        return Cloudinary\Uploader::upload($path, $option);
    }

    /**
     * Get time-restricted link.
     *
     * @param string $path
     * @param array $option
     */
    public static function getTempUrl(string $publicId)
    {
        $time = new \DateTime(date("Y-m-d H:i:s"));
        $time->add(new \DateInterval('PT' . self::$expireTime . 'S'));
        $expiry_time = $time;
        $expiry_timestamp = $expiry_time->getTimestamp();

        $tempUrl = Cloudinary::private_download_url($publicId, "pdf", array("type" => "authenticated", "expires_at" => $expiry_timestamp));

        return $tempUrl;
    }

    /**
     * Get blurred image url
     *
     * @param string $publicId
     * @param int $pages
     *
     * @return string
     */
    public function getBluredFile(string $publicId, int $pages): string
    {
        $tag = cl_image_tag(
            $publicId,
            array(
                "sign_url"=>true,
                "format" => 'jpg',
                "type"=>'authenticated',
                "effect"=>"blur_region:1500",
                "y"=>"0.75",
                'page' => rand(1, $pages -1) // select a random page
            )
        );

        $doc = new \DOMDocument();
        $doc->loadHTML($tag);
        $xpath = new \DOMXPath($doc);
        return $xpath->evaluate("string(//img/@src)");
    }
}