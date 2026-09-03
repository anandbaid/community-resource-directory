<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @property int $id
 * @property string $name
 * @property string $status
 * @property int $category_order
 */
class Categories extends Model
{
    use HasFactory;
}
