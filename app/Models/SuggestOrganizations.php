<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $suggestion_type
 * @property int $user_id
 * @property int $organization_id
 * @property string $name
 * @property string $type
 * @property string $logo
 * @property string $phone
 * @property string $email
 * @property string $website
 * @property string $category
 * @property string $target_population
 * @property string $status
 * @property string $service_area_type
 * @property string $service_area
 * @property string $point_of_contact
 * @property string $organization_details
 * @property string $publications
 */

class SuggestOrganizations extends Model
{
    use HasFactory;
}
