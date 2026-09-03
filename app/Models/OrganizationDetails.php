<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $organization_id
 * @property string $additional_resource
 * @property string $title
 * @property string $description
 * @property string $file_url
 * @property string $website
 * @property string $search
 * @property string $referral
 * @property string $other
 * @property string $physical_address_1
 * @property string $physical_address_2
 * @property string $physical_city
 * @property string $physical_state
 * @property string $physical_postal_code
 * @property string $mailing_address_1
 * @property string $mailing_address_2
 * @property string $mailing_city
 * @property string $mailing_state
 * @property string $mailing_postal_code
 * @property string $service_description
 * @property string $social_links
 */

class OrganizationDetails extends Model
{
    use HasFactory;
}
