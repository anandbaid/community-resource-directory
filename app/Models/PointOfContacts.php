<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $organization_id
 * @property string $first_name
 * @property string $last_name
 * @property string $name
 * @property string $pronouns
 * @property string $email
 * @property string $phone
 * @property string $notes
 */

class PointOfContacts extends Model
{
    use HasFactory;
}
