<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $title
 * @property string $content
 * @property string $data
 */
class EmailTemplates extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'title', 'content', 'data'];
}
