<?php
namespace App\Models;

use App\CustomAR\Record;
use App\CustomAR\Relation;

class Document extends Record {
    public const TABLE = 'documents';
    public const RELATIONS = [
        // Relation::BELONGS_TO => [
        //     'user' => User::class,
        // ]
    ];
}