<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string title
 * @property string slug
 * @property string description
 * @property string content_1
 * @property string content_2
 * @property string content_3
 * @property string content_4
 * @property string status
 */
class StaticPages extends Model
{
    use HasFactory;
}
