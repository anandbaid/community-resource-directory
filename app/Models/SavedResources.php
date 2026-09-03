<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $organization_id
 */
class SavedResources extends Model
{
    use HasFactory;

    public function userDetails()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    public function organizationDetails()
    {
        return $this->belongsTo('App\Models\Organizations', 'organization_id');
    }
}
