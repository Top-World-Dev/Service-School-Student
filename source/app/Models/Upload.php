<?php
namespace App\Models;

use App\CustomAR\Record;
use App\CustomAR\Relation;
use App\Libraries\CloudinaryLibrary;

class Upload extends Record {
    public const TABLE = 'uploads';
    public const RELATIONS = [
        Relation::BELONGS_TO => [
            'exam' => Exam::class,
        ]
    ];

    /**
     * Get fields as array
     *
     * @return array
     */
    public function getFields(): array
    {
        return [
            'id' => $this->id,
            'cloud_url' => CloudinaryLibrary::getTempUrl($this->cloud_url),
            'type' => $this->type,
            'exam_id' => $this->exam_id,
            'scan_status' => $this->scan_status,
        ];
    }
}