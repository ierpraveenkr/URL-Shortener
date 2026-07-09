<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function store(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'email' => 'required|email|unique:users,email|unique:invitations,email',
            'company_name' => 'required_if:role_id,null',
            'role_id' => 'required_if:company_name,null|in:2,3',
        ]);

        $companyId = $user->company_id;
        $roleId = $request->role_id;

        if ($user->role_id === 1) {
            // SuperAdmin inviting a new Client (Admin)
            $company = \App\Models\Company::create(['name' => $request->company_name]);
            $companyId = $company->id;
            $roleId = 2; // Admin
        } elseif ($user->role_id === 2) {
            // Admin inviting a Member or Admin to their company
            if (!in_array($roleId, [2, 3])) {
                abort(403);
            }
        } else {
            abort(403, 'Members cannot invite users.');
        }

        $token = \Illuminate\Support\Str::random(32);
        
        $invitation = \App\Models\Invitation::create([
            'email' => $request->email,
            'company_id' => $companyId,
            'role_id' => $roleId,
            'token' => $token,
        ]);

        \Illuminate\Support\Facades\Mail::to($request->email)->send(new \App\Mail\InviteEmail($invitation));

        return back()->with('success', 'Invitation emailed successfully to ' . $request->email);
    }

    public function showRegistrationForm($token)
    {
        $invitation = \App\Models\Invitation::where('token', $token)->where('status', 'pending')->firstOrFail();
        return view('auth.invite', compact('invitation'));
    }

    public function register(\Illuminate\Http\Request $request, $token)
    {
        $invitation = \App\Models\Invitation::where('token', $token)->where('status', 'pending')->firstOrFail();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|min:8',
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $invitation->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'company_id' => $invitation->company_id,
            'role_id' => $invitation->role_id,
        ]);

        $invitation->update(['status' => 'accepted']);
        auth()->login($user);
        
        return redirect('/dashboard');
    }
}