<?php

namespace App\Http\Controllers\Frontend\Concerns;

use App\Models\SavedResources;

/**
 * The account sidebar that resources/views/frontend/includes/quick-links.blade.php
 * renders, as props for the Vue equivalent. Shared by every converted account page.
 */
trait SharesAccountNavigation
{
    /**
     * @return array<string, mixed>
     */
    protected function quickLinks(): array
    {
        $user = auth()->user();
        $savedResourcesCount = $user
            ? SavedResources::where('user_id', $user->id)->count()
            : 0;

        return [
            'dashboardUrl' => route('user.dashboard'),
            'profileUrl' => route('user.profile'),
            'savedResourcesUrl' => url('saved-resources-view'),
            // Null means "nothing to download yet" — the link shows an info toast.
            'downloadSavedResourcesUrl' => $savedResourcesCount > 0
                ? route('download-saved-resources')
                : null,
            'savedSearchesUrl' => url('saved-search-view'),
            'suggestNewUrl' => url('suggest-new-resources'),
            'suggestExistingUrl' => url('suggest-existing-resources'),
        ];
    }
}
