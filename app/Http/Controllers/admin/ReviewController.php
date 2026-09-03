<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizationRatings;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reviews = OrganizationRatings::with(['userDetails', 'organizationDetails'])->paginate(20);

        return Inertia::render('Reviews/Index', [
            'reviews' => $reviews->through(fn (OrganizationRatings $review) => [
                'id' => $review->id,
                'user' => $review->userDetails->name ?? '',
                'organization' => $review->organizationDetails->name ?? '',
                'rate' => (int) $review->rate,
                'showUrl' => route('admin.review.show', $review->id),
            ]),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $review = OrganizationRatings::with(['userDetails', 'organizationDetails'])->findOrFail($id);
        $details = json_decode($review->description ?? '', true) ?? [];

        return Inertia::render('Reviews/Show', [
            'indexUrl' => route('admin.review.index'),
            'review' => [
                'user' => ucwords((string) ($review->userDetails->name ?? '')),
                'organization' => $review->organizationDetails->name ?? '',
                'rate' => (int) $review->rate,
            ],
            'answers' => $this->answers($details),
        ]);
    }

    /**
     * Flatten the stored questionnaire into ordered question/answer pairs.
     *
     * The Blade version rendered these as disabled form controls; a read-only
     * screen reads better as plain text, and the shape stays in one place.
     *
     * @param  array<string, mixed>  $details
     * @return array<int, array<string, mixed>>
     */
    private function answers(array $details): array
    {
        $yesNo = fn ($value) => $value ? 'Yes' : 'No';

        $answers = [
            [
                'question' => '1. In which state, district, or territory do you currently reside?',
                'answer' => $details['states'] ?? '',
            ],
            [
                'question' => '2. Are you a system impacted individual?',
                'answer' => $yesNo($details['system_impacted'] ?? false),
            ],
        ];

        if (!empty($details['system_impacted'])) {
            $answers[] = [
                'question' => 'Please select the option below that best describes your connection to the criminal legal system.',
                'answer' => implode(', ', $details['legal_system'] ?? []),
            ];
        }

        $answers[] = [
            'question' => '3. Are you currently serving a term of supervision (e.g. probation, parole, supervised release, etc.)?',
            'answer' => $yesNo($details['term_of_supervision'] ?? false),
        ];
        $answers[] = [
            'question' => '4. Is your rating based on personal experience or third-party disclosure?',
            'answer' => $details['experience'] ?? '',
        ];
        $answers[] = [
            'question' => '5. On what date was your initial interaction with the agency/organization?',
            'answer' => $details['initial_interaction'] ?? '',
        ];
        $answers[] = [
            'question' => '6. Did/does your involvement with this agency/organization include structured, classroom-style activities (e.g. job readiness, parenting, etc.)?',
            'answer' => $yesNo($details['structured_involvement'] ?? false),
        ];

        if (!empty($details['structured_involvement'])) {
            $answers[] = [
                'question' => 'Were you required to attend a minimum number of classroom activities to complete enrollment requirements?',
                'answer' => $yesNo($details['classroom_activities'] ?? false),
            ];
        }

        $answers[] = [
            'question' => '7. Was/is your involvement with this agency/organization mandated by the courts and/or probation/parole?',
            'answer' => $yesNo($details['mandated_by_the_courts'] ?? false),
        ];
        $answers[] = [
            'question' => '8. Did you find the agency/organization details (e.g. Name, Address, Description, Service Categories, etc.) provided to be accurate?',
            'answer' => $yesNo($details['accurate_details'] ?? false),
        ];

        if (empty($details['accurate_details'])) {
            $answers[] = [
                'question' => 'Briefly describe any details provided in their agency/organization interaction.',
                'answer' => $details['details'] ?? '',
            ];
        }

        $answers[] = [
            'question' => '9. Based on your personal experience with this agency/organization, would you recommend them to others?',
            'answer' => $yesNo($details['recommend'] ?? false),
        ];

        return $answers;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
