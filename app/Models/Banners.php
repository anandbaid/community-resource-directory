<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $page_title
 * @property string $page_slug
 * @property string $image
 * @property string $banner_heading
 * @property string $banner_text
 * @property int $order
 * @property string $status
 */
class Banners extends Model
{
    use HasFactory;
}
