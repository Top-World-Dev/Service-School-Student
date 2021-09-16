<?php 
namespace App\Libraries;

use Spatie\Browsershot\Browsershot;
 
class PDFLibrary
{
    /**
     * @var string
     */
    protected $tempUploadPath = WRITEPATH . 'uploads';

    /**
     * Create new pdf file
     *
     * @param string $endpoint
     * @param string $fileName
     */
    public function createPDF(string $endpoint, string $fileName) {
        $filePath = $this->tempUploadPath . DIRECTORY_SEPARATOR  . $fileName;
        Browsershot::url($endpoint)->save($filePath);
        return $filePath;
    }
}
