<?php

namespace App\Controllers;
use App\Libraries\PDFLibrary;
use App\Libraries\CloudinaryLibrary;

class Home extends BaseController
{
    /**
     * Index page
     *
     */
    public function index()
    {
        return view('welcome_message');
    }

    /**
     * Test function. remove it on production
     *
     */
    public function test()
    {
        $filePath = (new PDFLibrary())->createPDF('<h1>Test pdf file</h1>', 'test.pdf');
        $uploadOption = array(
            'folder' => 'temp_quest',
            "resource_type" => "image",
            "format" => "pdf",
        );
        $response = (new CloudinaryLibrary())->upload($filePath, $uploadOption);
        echo $response['public_id'];
    }
}
