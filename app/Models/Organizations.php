<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrganizationDetails;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $logo
 * @property string $phone
 * @property string $email
 * @property string $website
 * @property int $category
 * @property string $target_population
 * @property string $status
 * @property string $service_area_type
 * @property string $service_area
 */

class Organizations extends Model
{
    use HasFactory;

    public function details()
    {
        return $this->hasOne(OrganizationDetails::class, 'organization_id');
    }

    public function publications()
    {
        return $this->belongsToMany(Publications::class, 'organization_publication', 'organization_id', 'publication_id');
    }

    public function organizationDetails()
    {
        return OrganizationDetails::where('organization_id', $this->id)->first();
    }
}
