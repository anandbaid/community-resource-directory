<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int page_id
 * @property string title
 * @property string description
 * @property string image
 * @property string link
 * @property int order
 */
class StaticPageItems extends Model
{
    use HasFactory;
}
