<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\CommonFunction;
use App\Http\Controllers\Controller;
use App\Models\SiteSettings;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class UserDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::where('role', '!=', 'admin')->orderBy('id', 'DESC')->get();

        return Inertia::render('Users/Index', [
            'users' => $users->map(fn (User $user) => [
                'id' => $user->id,
                'name' => ucwords((string) $user->name),
                'email' => $user->email,
                'status' => $user->status,
                'avatarUrl' => $user->profile_pic
                    ? asset($user->profile_pic)
                    : asset('assets/img/user-placeholder.png'),
                'showUrl' => route('admin.user.show', $user->id),
                'editUrl' => route('admin.user.edit', $user->id),
                'deleteUrl' => route('admin.user.destroy', $user->id),
                'statusUrl' => route('admin.user.status', $user->id),
            ])->values(),
            'createUrl' => route('admin.user.create'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Users/Form', [
            'type' => 'Create',
            'submitUrl' => route('admin.user.store'),
            'indexUrl' => route('admin.user.index'),
            'placeholderImage' => asset('assets/img/user-placeholder.png'),
            'values' => $this->formValues(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:150',
            'last_name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:20',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
            'phone' => 'required|regex:/^[0-9]{10,20}$/',
            'zipcode' => 'required|regex:/^\d{5}(-\d{4})?$/',
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        try {
            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->name = $request->first_name . ' ' . $request->last_name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->phone = $request->phone;
            $user->zipcode = $request->zipcode;
            $user->role = 'user';
            $user->save();

            if ($request->hasFile('profile_pic')) {
                $file = CommonFunction::fileUploadStorage($request->file('profile_pic'), 'profile', $user->id . '-' . $user->first_name);
                if (!empty($file)) {
                    $user->profile_pic = $file;
                }
            }
            $user->save();

            $this->sendCredentialsMail($user, $request->password);

            return to_route('admin.user.index')->with('success', 'User Details added successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);

        return Inertia::render('Users/Show', [
            'indexUrl' => route('admin.user.index'),
            'avatarUrl' => $user->profile_pic
                ? asset($user->profile_pic)
                : asset('assets/img/user-placeholder.png'),
            'user' => [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => preg_replace('/\D+/', '', (string) $user->phone),
                'zipcode' => $user->zipcode,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);

        return Inertia::render('Users/Form', [
            'type' => 'Edit',
            'submitUrl' => route('admin.user.update', $user->id),
            'indexUrl' => route('admin.user.index'),
            'placeholderImage' => asset('assets/img/user-placeholder.png'),
            'avatarUrl' => $user->profile_pic ? asset($user->profile_pic) : '',
            'values' => $this->formValues($user),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $rules = [
            'first_name' => 'required|string|max:150',
            'last_name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'required|regex:/^[0-9]{10,20}$/',
            'zipcode' => 'required|regex:/^\d{5}(-\d{4})?$/',
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ];

        $resetPassword = !empty($request->reset_password);

        if ($resetPassword) {
            $rules['password'] = [
                'required',
                'string',
                'min:8',
                'max:20',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ];
        }

        $request->validate($rules);

        try {
            $user = User::findOrFail($id);
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->name = $request->first_name . ' ' . $request->last_name;
            $user->email = $request->email;

            // Only touch the password when the admin explicitly asked to reset it.
            // Hashing an absent password here would silently lock the user out.
            if ($resetPassword) {
                $user->password = Hash::make($request->password);
            }

            $user->phone = $request->phone;
            $user->zipcode = $request->zipcode;
            $user->role = 'user';
            $user->save();

            if ($request->hasFile('profile_pic')) {
                $file = CommonFunction::fileUploadStorage($request->file('profile_pic'), 'profile', $user->id . '-' . $user->first_name);
                if (!empty($file)) {
                    CommonFunction::fileDeleteStorage($user->profile_pic);
                    $user->profile_pic = $file;
                }
            }
            $user->save();

            if ($resetPassword) {
                $this->sendCredentialsMail($user, $request->password);
            }

            return to_route('admin.user.index')->with('success', 'User Details updated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return back()->with('error', 'User not found');
            }

            CommonFunction::fileDeleteStorage($user->profile_pic);
            $user->delete();

            return back()->with('success', 'User details deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, string $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        try {
            $user = User::findOrFail($id);
            $user->status = strtolower($request->status);
            $user->save();

            return back()->with('success', 'Status updated');
        } catch (\Exception $err) {
            return back()->with('error', $err->getMessage());
        }
    }

    private function sendCredentialsMail(User $user, string $plainPassword): void
    {
        $email_template = 2;
        $headerLogo = SiteSettings::where('settings_name', 'header_logo')->first()->settings_value ?? 'assets/img/logo.png';
        $logo = url($headerLogo);
        $url = url('/');
        $copyRight = SiteSettings::where('settings_name', 'copy_right')->first()->settings_value ?? '';
        $check_array = ['#Name#', '#UserName#', '#Password#', '#SiteURL#', '#SiteLogo#', '#FooterCopyright#'];
        $replace_array = [$user->name, $user->email, $plainPassword, $url, $logo, $copyRight];

        CommonFunction::sendMail($email_template, $user->email, $check_array, $replace_array);
    }

    /**
     * @return array<string, mixed>
     */
    private function formValues(?User $user = null): array
    {
        return [
            'first_name' => $user->first_name ?? '',
            'last_name' => $user->last_name ?? '',
            'email' => $user->email ?? '',
            'phone' => preg_replace('/\D+/', '', (string) ($user->phone ?? '')),
            'zipcode' => $user->zipcode ?? '',
            'password' => '',
            'password_confirmation' => '',
            'reset_password' => false,
        ];
    }
}
