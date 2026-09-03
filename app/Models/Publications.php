<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $image
 * @property string $file
 * @property string $url
 * @property string $status
 */
class Publications extends Model
{
    use HasFactory;

    public function organizations()
    {
        return $this->belongsToMany(Organizations::class, 'organization_publication', 'publication_id', 'organization_id');
    }
}
