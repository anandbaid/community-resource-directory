<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\EmailTemplates;
use App\Models\OrganizationDetails;
use App\Models\OrganizationFields;
use App\Models\OrganizationRatings;
use App\Models\Organizations;
use App\Models\PointOfContacts;
use App\Models\Publications;
use App\Models\SiteSettings;
use App\Models\States;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Encoders\WebpEncoder;

class CommonFunction
{
    public static function formatPhone(?string $value): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        if ($digits === '') {
            return '';
        }

        $area = substr($digits, 0, 3);
        $prefix = substr($digits, 3, 3);
        $line = substr($digits, 6, 4);
        $rest = substr($digits, 10);

        if ($area && !$prefix) {
            return '(' . $area;
        }

        $formatted = '(' . $area . ')';
        if ($prefix) {
            $formatted .= ' ' . $prefix;
        }
        if ($line) {
            $formatted .= '-' . $line;
        }
        if ($rest) {
            $formatted .= ' ' . $rest;
        }

        return trim($formatted);
    }

    /**
     * @param  \Illuminate\Http\File|\Illuminate\Http\UploadedFile|string  $file
     */
    public static function fileUploadStorage($file, ?string $path = 'uploads', ?string $name = '', bool $randomizeName = true)
    {
        $name = empty($name) ? uniqid() : $name;
        $fileName = $randomizeName ? $name . '_' . time() . uniqid() : $name;
        $fileName = Str::slug($fileName);
        $fileName .= '.' . $file->getClientOriginalExtension();
        $path = trim($path, '/');
        try {
            $uploadedPath = $file->storeAs($path, $fileName, 'public');
            $destination = public_path('storage/' . $path); // public/storage
            $file->move($destination, $fileName);
        } catch (\Exception $e) {
            Artisan::call('storage:link');
            $uploadedPath = $file->storeAs($path, $fileName, 'public');
        }
        return '/storage/' . trim($uploadedPath, '/');
    }

    public static function fileUploadResize($file, ?string $path = 'uploads', ?string $name = '', bool $randamizeName = true)
    {
        $name = empty($name) ? uniqid() : $name;
        $fileName = $randamizeName ? $name . '_' . time() . uniqid() : $name;
        $fileName = Str::slug($fileName);

        $fileName .= '.webp';
        $path = trim($path, '/');

        try {
            // Store original as WebP too (optional)
            $image = Image::read($file->getRealPath())->orient();
            $encodedOriginal = $image->encode(new WebpEncoder(quality: 85));
            $uploadedPath = $path . '/' . $fileName;
            Storage::disk('public')->put($uploadedPath, (string) $encodedOriginal);

            $sizes = [
                'small'  => [768, 432],
                'medium' => [1440, 810],
            ];

            foreach ($sizes as $sizeName => [$width, $height]) {
                $resizePath = $path . '/' . $sizeName;

                if (!Storage::disk('public')->exists($resizePath)) {
                    Storage::disk('public')->makeDirectory($resizePath);
                }

                $resizedImage = Image::read($file->getRealPath())
                    ->orient()
                    ->scaleDown($width, $height);

                $encoded = $resizedImage->encode(new WebpEncoder(quality: 95));

                Storage::disk('public')->put(
                    $resizePath . '/' . $fileName,
                    (string) $encoded
                );
            }
        } catch (\Exception $e) {
            Artisan::call('storage:link');
            throw $e;
        }

        return '/storage/' . trim($uploadedPath, '/');
    }

    public static function moveFileWithRename(string $sourcePath, string $destinationPath, ?string $newName = null, bool $randomizeName = true)
    {
        try {
            // Remove any incorrect 'storage/' prefix if present
            $sourcePath = preg_replace('/^storage\//', '', ltrim($sourcePath, '/'));
            $destinationPath = rtrim($destinationPath, '/');

            // Check if the file exists in the public disk
            if (!Storage::disk('public')->exists($sourcePath)) {
                return false;
            }

            // Get file extension and generate a new name
            $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
            $baseName = $newName ?: pathinfo($sourcePath, PATHINFO_FILENAME);

            $fileName = $randomizeName
                ? $baseName . '_' . time() . uniqid()
                : $baseName;

            // Create a slug-safe file name
            $fileName = Str::slug($fileName) . '.' . $extension;

            // Build the full destination path
            $fullDestinationPath = $destinationPath . '/' . $fileName;

            // Move the file
            Storage::disk('public')->move($sourcePath, $fullDestinationPath);

            // Return the new public storage path
            return '/storage/' . $fullDestinationPath;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function saveFilesFromUrl(string $url, ?string $path = 'uploads', ?string $name = '', bool $randomizeName = true)
    {
        $name = empty($name) ? uniqid() : $name;
        $fileName = $randomizeName ? $name . '_' . time() . uniqid() : $name;
        $path = trim($path, '/');

        try {
            // Get image content from the URL
            $imageContent = file_get_contents($url);
            if ($imageContent === false) {
                return false;
                // throw new \Exception("Unable to fetch image from URL: $url");
            }

            // Get the file extension from the URL
            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (empty($extension)) {
                return false;
                // throw new \Exception("Invalid file extension for URL: $url");
            }

            // Create a slugged filename
            $fileName = Str::slug($fileName) . '.' . $extension;

            // Full storage path
            $storagePath = $path . '/' . $fileName;

            // Save the image to storage
            $saved = Storage::disk('public')->put($storagePath, $imageContent);
            if (!$saved) {
                return false;
                // throw new \Exception("Failed to save the image to storage: $storagePath");
            }
            return '/storage/' . $storagePath;
        } catch (\Exception $e) {
            // Handle exceptions
            Log::error("Error saving image from URL: " . $e->getMessage());
            return false;
        }
    }

    public static function fileDeleteStorage(?string $path = '')
    {
        if (!empty($path)) {
            $path = public_path($path);
            if (file_exists($path)) {
                return unlink($path);
            }
        }
        return false;
    }

    public static function deleteUploadedImages(string $imagePath)
    {
        $relativePath = str_replace('/storage/', '', $imagePath);
        $directoryPath = dirname($relativePath);
        $fileName = basename($relativePath);

        $sizes = ['small', 'medium'];
        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
        foreach ($sizes as $sizeName) {
            $resizedPath = $directoryPath . '/' . $sizeName . '/' . $fileName;
            if (Storage::disk('public')->exists($resizedPath)) {
                Storage::disk('public')->delete($resizedPath);
            }
        }
        return true;
    }
    public static function curl_api_call(
        string $url,
        string $method = 'GET',
        $body = '',
        array $header = [],
        $associative = true
    ) {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $response = curl_exec($curl);
        $rescode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $header_info = curl_getinfo($curl, CURLINFO_HEADER_OUT);
        $err = curl_error($curl);
        curl_close($curl);
        $succesRescode = [100, 200, 201, 202, 205, 301];
        if ($err || !in_array($rescode, $succesRescode)) {
            $res = '{"status": false, "message" : "api call failed", "rescode": ' . $rescode . ', "data": ' . $response . ' }';
        } else {
            $res = '{"status": true, "message": "api call success", "rescode": ' . $rescode . ', "data": ' . $response . ' }';
        }
        return json_decode($res, $associative);
    }


    public static function sendMail(int $template_id, string $email, array $template_variables, array $email_variables, ?string $cc_email = '')
    {
        $email_template = EmailTemplates::find($template_id);
        if ($email_template->status == 'active') {

            $email_subject = str_replace($template_variables, $email_variables, $email_template->title);
            $email_message = str_replace($template_variables, $email_variables, $email_template->content);

            $email_details = [
                'subject' => $email_subject,
                'body' => $email_message,
            ];

            $mail = Mail::to($email);
            if ($cc_email) {
                $mail->cc($cc_email);
            }
            try {
                $mail->send(new \App\Mail\EmailTemplate($email_details));
                return true;
            } catch (\Exception $e) {
                Log::error('Mail failed: ' . $e->getMessage());
                return false;
            }
        }
    }


    public static function smtpValidateEmail_bk($email)
    {
        list($user, $domain) = explode('@', $email);
        // Get MX records for the domain
        if (getmxrr($domain, $mxHosts)) {
            $mxHost = $mxHosts[0];
            // Connect to the SMTP server on port 25
            $connect = fsockopen($mxHost, 25, $errno, $errstr, 10);
            if ($connect) {
                // Send HELO command
                fputs($connect, "HELO $domain\r\n");
                fgets($connect, 1024);

                // Send MAIL FROM command
                fputs($connect, "MAIL FROM: <test@$domain>\r\n");
                fgets($connect, 1024);

                // Send RCPT TO command
                fputs($connect, "RCPT TO: <$email>\r\n");
                $response = fgets($connect, 1024);

                // Close the connection
                fputs($connect, "QUIT\r\n");
                fclose($connect);

                // Check the response code
                if (strpos($response, '250') !== false) {
                    return true; // Email exists
                    // return "The email address '$email' exists."; // Email exists
                } else {
                    return false; // Email does not exist
                    // return "The email address '$email' does not exist."; // Email does not exist
                }
            } else {
                // Log the error
                return false; // Connection failed
                // return "Connection to the mail server failed: $errstr"; // Connection failed
            }
        }
        return false; // MX records not found
        // return "No MX records found for the domain '$domain'."; // MX records not found
    }

    public static function smtpValidateEmail($email)
    {
        $response = Http::get('https://api.kickbox.com/v2/verify', [
            'email' => $email,
            'apikey' => config('services.kickbox.key'),
        ]);

        $result = $response->json();
        $status = false;
        if (
            isset($result['success'], $result['message'])
            && $result['success'] === false
            && trim((string) $result['message']) === 'Insufficient balance'
        ) {
            $status = true;
            $adminEmail = SiteSettings::where('settings_name', 'admin_email')->first()->settings_value ?? null;
            if (!empty($adminEmail)) {
                $settingKey = 'kickbox_balance_alert_sent_at';
                $today = Carbon::now()->toDateString();
                $lastSent = SiteSettings::where('settings_name', $settingKey)->value('settings_value');
                if ($lastSent === $today) {
                    return $status;
                }
                try {
                    $email_template = 8;
                    $headerLogo = SiteSettings::where('settings_name', 'header_logo')->first()->settings_value ?? 'assets/img/logo.png';
                    $logo = url($headerLogo);
                    $url = url('/');
                    $copyRight = SiteSettings::where('settings_name', 'copy_right')->first()->settings_value ?? '';
                    $check_array = ['#SiteURL#', '#SiteLogo#', '#FooterCopyright#'];
                    $replace_array = [$url, $logo, $copyRight];
                    CommonFunction::sendMail($email_template, $adminEmail, $check_array, $replace_array);
                    SiteSettings::updateOrInsert(
                        ['settings_name' => $settingKey],
                        ['settings_value' => $today, 'updated_at' => date('Y-m-d H:i:s'), 'created_at' => date('Y-m-d H:i:s')]
                    );
                } catch (\Throwable $e) {
                    Log::warning('Failed to notify admin about Kickbox balance issue', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            return $status;
        }
        if (isset($result['result'])) {
            if ($result['result'] === 'deliverable') {
                $status = true;
            } else if ($result['result'] === 'risky') {
                if (!$result['disposable'] && ($result['reason'] === "low_deliverability" || $result['reason'] === "low_quality")) {
                    $status = true;
                }
            }
        }
        return $status;
    }

    public static function validateWebsite($url)
    {
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        $parts = parse_url($url);
        if (empty($parts['host'])) {
            return false;
        }

        if (!filter_var($parts['host'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return false;
        }

        return checkdnsrr($parts['host'], 'A') || checkdnsrr($parts['host'], 'AAAA');
    }

    public static function validateAddress($data = null)
    {
        $url = 'https://addressvalidation.googleapis.com/v1:validateAddress?key=' . config('custom.map_api_key');
        $header = [
            'Content-Type: application/json'
        ];
        $response = self::curl_api_call($url, 'POST', json_encode($data), $header, false);
        if ($response->rescode === 200) {
            $data = $response->data->result;
            if ($data->verdict->validationGranularity == "PREMISE" &&  $data->verdict->geocodeGranularity == "PREMISE") {
                $res = '{"status": true, "message" : "Address validated successfully", "data": ' .  json_encode($data->geocode->location) . '}';
            } else {
                $res = '{"status": false, "message" : "Something went wrong"}';
            }
        } else {
            $res = '{"status": false, "message" : "Something went wrong"}';
        }
        return $res;
    }

    public static function geocodeAddress(array $addressParts): ?array
    {
        $address = implode(', ', array_filter(array_map('trim', $addressParts)));
        if (empty($address)) {
            return null;
        }

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $address,
                'key' => config('custom.map_api_key'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $location = $data['results'][0]['geometry']['location'] ?? null;
                if (!empty($location['lat']) && !empty($location['lng'])) {
                    return [
                        'lat' => $location['lat'],
                        'lng' => $location['lng'],
                    ];
                }
            }

            Log::warning('Geocoding failed', [
                'address' => $address,
                'response' => $response->json(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Geocoding exception', [
                'address' => $address,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    public static function importOrganization($csvData, $trimmedCsvData, $start)
    {
        try {
            $fields = OrganizationFields::where('status', 'active')->get();
            $csvHeader = [];
            $requiredFields = [];
            $publicationHeader = [];
            $validationArray = [];
            $missingRows = [];
            $imported = 0;
            foreach ($fields as $field) {
                if ($field->required == 1) {
                    $requiredFields[] = $field->name;
                    if ($field->regex) {
                        $validationArray[$field->name] = [
                            'regex' => $field->regex,
                            'message' => $field->message
                        ];
                    }
                }
                $positionInArray = array_search($field->name, $trimmedCsvData);
                if ($positionInArray !== false) {
                    $csvHeader[$field->name] = $positionInArray;
                }
            }
            $pattern = '/^Publication \d+ Title$/';
            // Loop through the array to find matches
            foreach ($trimmedCsvData as $key => $value) {
                if (preg_match($pattern, $value)) {
                    $publicationHeader[] = $key;
                }
            }
            $pointOfContactTemplate = EmailTemplates::find(7);
            $siteUrl = url('/');
            $headerLogo = SiteSettings::where('settings_name', 'header_logo')->first()->settings_value ?? 'assets/img/logo.png';

            $siteLogo = url($headerLogo);
            $copyRight = SiteSettings::where('settings_name', 'copy_right')->first()->settings_value ?? '';

            $env = app()->environment();
            foreach ($csvData ?? [] as $key => $value) {
                $noMissingValue = true;
                foreach ($requiredFields ?? [] as $requiredField) {
                    if (!isset($value[$csvHeader[$requiredField]])) {
                        $noMissingValue = false;
                        $missingRows[$key + 1 + $start][] = $requiredField . ' - field is required';
                    } else {
                        if ($env != 'local') {
                            if ($requiredField == "Website" &&  !CommonFunction::validateWebsite($value[$csvHeader[$requiredField]])) {
                                $noMissingValue = false;
                                $missingRows[$key + 1 + $start][] = 'Organization website ping validation failed';
                            }
                        }
                    }
                }

                foreach ($validationArray ?? [] as $valKey => $validation) {
                    if (!preg_match($validation['regex'], $value[$csvHeader[$valKey]])) {
                        $noMissingValue = false;
                        $missingRows[$key + 1 + $start][] = $valKey . ' - ' . $validation['message'];
                    }
                }

                if ($noMissingValue) {
                    try {
                        [$newOrganization, $pointofcontacts] = DB::transaction(function () use (
                            $value,
                            $csvHeader,
                            $publicationHeader
                        ) {
                            $newOrganization = new Organizations();
                            $newOrganization->name = str_replace('`', '\'', $value[$csvHeader['Organization Name']]);
                            $newOrganization->type = str_replace(' ', '-', strtolower($value[$csvHeader['Organization Type']]));
                            $newOrganization->phone = $value[$csvHeader['Phone']];
                            $newOrganization->email = $value[$csvHeader['Email']];
                            $newOrganization->website = $value[$csvHeader['Website']];
                            $catId = [];
                            if ($value[$csvHeader['Service Categories']]) {
                                $categories = explode(',', $value[$csvHeader['Service Categories']]);
                                foreach ($categories as $category) {
                                    if ($categoryDetails = Categories::where('name', trim($category))->where('status', 'active')->first()) {
                                        $catId[] = (string)$categoryDetails->id;
                                    }
                                }
                            }
                            $newOrganization->category = json_encode($catId);
                            $newOrganization->target_population = $value[$csvHeader['Target Population']];
                            $newOrganization->service_area_type = $value[$csvHeader['Service Area Type']];
                            $newOrganization->service_area = $value[$csvHeader['Service Area']];
                            $newOrganization->status = (is_null($value[$csvHeader['Email']]) || $value[$csvHeader['Email']] == '')  ? 'inactive' : 'active';
                            $newOrganization->save();
                            if (!empty($value[$csvHeader['Organization Logo']])) {
                                $file = CommonFunction::saveFilesFromUrl($value[$csvHeader['Organization Logo']], 'organization', $newOrganization->id . '-organization');
                                if (!empty($file)) {
                                    $newOrganization->logo = $file;
                                }
                            }
                            $newOrganization->save();
                            if (!$newOrganization->id) {
                                throw new \Exception('Organization could not be created');
                            }
                            $pointofcontacts = new PointOfContacts();
                            $pointofcontacts->organization_id = $newOrganization->id;
                            $pronounsRaw = $value[$csvHeader['Point-Of-Contact Pronouns']] ?? '';
                            $pronounsTrimmed = rtrim(trim((string) $pronounsRaw), '/');
                            $allowedPronouns = ['He/Him', 'She/Her', 'They/Them'];
                            $pronouns = in_array($pronounsTrimmed, $allowedPronouns, true) ? $pronounsTrimmed : 'They/Them';
                            $pointofcontacts->pronouns = $pronouns;
                            $pointofcontacts->first_name = $value[$csvHeader['Point-Of-Contact First Name']];
                            $pointofcontacts->last_name = $value[$csvHeader['Point-Of-Contact Last Name']];
                            $pointofcontacts->name = $pointofcontacts->first_name . ' ' . $pointofcontacts->last_name;
                            $pointofcontacts->email = $value[$csvHeader['Point-Of-Contact Email']];
                            $pointofcontacts->phone = $value[$csvHeader['Point-Of-Contact Phone']];
                            $pointofcontacts->notes = $value[$csvHeader['Point-Of-Contact Notes']];
                            $pointofcontacts->save();


                            $organization_details = new OrganizationDetails();
                            $organization_details->organization_id = $newOrganization->id;
                            $organization_details->additional_resource = $value[$csvHeader['Additional Resource']];
                            $organization_details->title = $value[$csvHeader['Title']];
                            $organization_details->description = $value[$csvHeader['Description']];
                            $organization_details->file_url = $value[$csvHeader['File Url']];
                            $organization_details->source = $value[$csvHeader['Source']];
                            $organization_details->physical_address_1 = $value[$csvHeader['Physical Address 1']];
                            $organization_details->physical_address_2 = $value[$csvHeader['Physical Address 2']];
                            $organization_details->physical_city = $value[$csvHeader['Physical City']];
                            $organization_details->physical_state = $value[$csvHeader['Physical State']];
                            $organization_details->physical_postal_code = $value[$csvHeader['Physical Postal Code']];

                            $latLng = self::geocodeAddress([
                                $organization_details->physical_address_1,
                                $organization_details->physical_city,
                                $organization_details->physical_state,
                                $organization_details->physical_postal_code,
                            ]);

                            if (empty($latLng) || !isset($latLng['lat'], $latLng['lng'])) {
                                throw new \Exception('Latitude/Longitude could not be resolved for the provided address.');
                            }

                            $organization_details->latitude = $latLng['lat'];
                            $organization_details->longitude = $latLng['lng'];

                            $organization_details->mailing_address_1 = $value[$csvHeader['Mailing Address 1']];
                            $organization_details->mailing_address_2 = $value[$csvHeader['Mailing Address 2']];
                            $organization_details->mailing_city = $value[$csvHeader['Mailing City']];
                            $organization_details->mailing_state = $value[$csvHeader['Mailing State']];
                            $organization_details->mailing_postal_code = $value[$csvHeader['Mailing Postal Code']];
                            $organization_details->service_description = $value[$csvHeader['Service Description']];
                            $social_links = [
                                'facebook' => $value[$csvHeader['Facebook']],
                                'linkedin' => $value[$csvHeader['Linkedin']],
                                'instagram' => $value[$csvHeader['Instagram']],
                            ];
                            $organization_details->social_links = json_encode($social_links);
                            $organization_details->save();

                            foreach ($publicationHeader as $headerKey) {
                                if (!empty($value[$headerKey])) {
                                    $state = States::where('name', $newOrganization->service_area)->first();
                                    $title = trim((string) $value[$headerKey]);
                                    $description = $value[$headerKey + 1] ?? null;
                                    $fileUrl = $value[$headerKey + 2] ?? null;
                                    $imageUrl = $value[$headerKey + 3] ?? null;

                                    $publication = Publications::where('title', $title)->first();
                                    if (!$publication) {
                                        $publication = new Publications();
                                        $publication->title = $title;
                                        $publication->description = $description;
                                        $publication->state = $state ? $state->id : null;
                                        $publication->status = 'active';
                                        $publication->save();
                                        if (!empty($fileUrl)) {
                                            $file = CommonFunction::saveFilesFromUrl($fileUrl, 'publictions', $publication->id . '-file');
                                            if (!empty($file)) {
                                                $publication->file = $file;
                                            }
                                        }
                                        if (!empty($imageUrl)) {
                                            $image = CommonFunction::saveFilesFromUrl($imageUrl, 'publictions', $publication->id . '-image');
                                            if (!empty($image)) {
                                                $publication->image = $image;
                                            }
                                        }
                                        $publication->save();
                                    }
                                    $publication->organizations()->syncWithoutDetaching([$newOrganization->id]);
                                }
                            }
                            return [$newOrganization, $pointofcontacts];
                        });
                        $imported++;
                        if ($pointOfContactTemplate && $pointofcontacts && $pointofcontacts->email) {
                            $emailTitle = 'Organization Added: ' . $newOrganization->name;
                            $check_array = ['#EmailTitle#', '#Name#', '#OrganizationName#', '#ActionMessage#', '#SiteURL#', '#SiteLogo#', '#FooterCopyright#'];
                            $replace_array = [
                                $emailTitle,
                                $pointofcontacts->name,
                                $newOrganization->name,
                                'Your organization has been added to the Community Resource Directory directory.',
                                $siteUrl,
                                $siteLogo,
                                $copyRight,
                            ];
                            CommonFunction::sendMail($pointOfContactTemplate->id, $pointofcontacts->email, $check_array, $replace_array);
                        }
                    } catch (\Throwable $e) {
                        $missingRows[$key + 1 + $start][] = 'Row import failed: ' . $e->getMessage();
                        Log::error('Organization import row failed', [
                            'row' => $key + $start,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
            $response = [
                'status' => true,
                'total' => count($csvData),
                'imported' => $imported,
                'error' => $missingRows,
            ];
            return $response;
        } catch (\Exception $e) {
            Log::info("Batch Error: " . $e->getMessage());
            $status = false;
            if ($imported)
                $status = true;
            $response = [
                'status' => $status,
                'total' => count($csvData),
                'imported' => $imported,
                'error' => $missingRows,
            ];
            return $response;
        }
    }

    static public function getRatingStars($id)
    {
        $allRatings = OrganizationRatings::where('organization_id', $id)->get('rate')->pluck('rate')->toArray();
        $starHtml = '';
        if (count($allRatings ?? []) > 0) {
            $total = 0;
            foreach ($allRatings as $rating) {
                $total += $rating;
            }
            $result = $total / count($allRatings);

            $fullStars = floor($result); // Full stars
            $decimalPart = $result - $fullStars; // Get the decimal part
            $halfStar = 0;
            if ($decimalPart > 0) {
                if ($decimalPart > 0.5) {
                    $fullStars + 1;
                } else {
                    $halfStar = 1;
                }
            }
            $noStar = 5 - ($fullStars + $halfStar);

            for ($i = 0; $i < $fullStars; $i++) {
                $starHtml .= '<i class="fa-solid fa-star"></i>';
            }
            if ($halfStar) {
                $starHtml .= '<i class="fa-solid fa-star-half-stroke"></i>';
            }
            for ($i = 0; $i < $noStar; $i++) {
                $starHtml .= '<i class="fa-regular fa-star"></i>';
            }
        } else {
            $noStar = 5;
            for ($i = 0; $i < $noStar; $i++) {
                $starHtml .= '<i class="fa-regular fa-star"></i>';
            }
        }
        $data = [
            'starHtml' => $starHtml,
            'count' => count($allRatings ?? []),
        ];
        return $data;
    }
}
