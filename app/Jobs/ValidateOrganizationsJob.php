<?php

namespace App\Jobs;

use App\Http\Controllers\CommonFunction;
use App\Models\Organizations;
use App\Models\SiteSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class ValidateOrganizationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $forceAll;

    public function __construct(bool $forceAll = false)
    {
        $this->forceAll = $forceAll;
    }

    public function handle(): void
    {
        $threshold = Carbon::now()->subDays(90);
        $canEmailCheck = !empty(config('services.kickbox.key'));
        $deactivatedCount = 0;
        $organizationList = [];

        Organizations::where('status', 'active')
            ->when(!$this->forceAll, function ($q) use ($threshold) {
                $q->where(function ($inner) use ($threshold) {
                    $inner->whereNull('last_validated_at')
                        ->orWhere('last_validated_at', '<=', $threshold);
                });
            })
            ->orderBy('id')
            ->chunkById(200, function ($chunk) use (&$deactivatedCount, &$organizationList, $canEmailCheck) {
                foreach ($chunk as $org) {
                    $emailOk = $canEmailCheck ? CommonFunction::smtpValidateEmail($org->email) : true;
                    $websiteOk = CommonFunction::validateWebsite($org->website);

                    $org->last_validated_at = Carbon::now();
                    if (!$emailOk || !$websiteOk) {
                        $org->status = 'inactive';
                        $deactivatedCount++;
                        $organizationList[] = [
                            'id' => $org->id,
                            'name' => $org->name,
                            'email' => $org->email,
                            'website' => $org->website,
                            'email_ok' => $emailOk,
                            'website_ok' => $websiteOk,
                        ];
                    }
                    $org->save();
                }
            });

        $adminEmail = SiteSettings::where('settings_name', 'admin_email')->first()->settings_value ?? null;
        if ($adminEmail) {
            $body = $this->buildEmailBody($deactivatedCount, $organizationList);
            try {
                Mail::raw($body, function ($message) use ($adminEmail) {
                    $message->to($adminEmail)->subject('Manual validation completed');
                });
            } catch (\Throwable $e) {
            }
        }
    }

    private function buildEmailBody(int $total, array $organizationList): string
    {
        $lines = [];
        $lines[] = 'Manual URL and email validation completed.';
        $lines[] = 'Total deactivated: ' . $total;
        if ($total > 0) {
            $lines[] = '';
            $lines[] = 'List of affected organizations:';
            foreach ($organizationList as $it) {
                $lines[] = $it['name'] . ' (ID: ' . $it['id'] . ')';
                $lines[] = ' - Email: ' . $it['email'] . ' [' . ($it['email_ok'] ? 'OK' : 'FAILED') . ']';
                $lines[] = ' - Website: ' . $it['website'] . ' [' . ($it['website_ok'] ? 'OK' : 'FAILED') . ']';
                $lines[] = '';
            }
        }
        return implode("\n", $lines);
    }
}
