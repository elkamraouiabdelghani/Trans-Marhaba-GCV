<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\AccountCreated;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $responsibles = User::where('role', 'responsible')->get();

        return view('responsible.responsibles', ['responsibles'=> $responsibles]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('responsible.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'in:admin'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'role' => $request->role,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
        ]);

        event(new Registered($user));

        if($user->role == 'responsible'){
            Mail::to($user->email)->send(new AccountCreated($user, $request->password));
        }

        return redirect()->route('responsible')->with('success', $request->role.' created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        $responsible = User::findOrFail($id);
        $responsible->statu = $request->statu;
        $responsible->save();

        return redirect()->back()->with('success', $responsible->role.' status updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = User::find($id)->role;
        
        User::find($id)->delete();

        return redirect()->route('responsible')->with('success', $role.' deleted successfully.');
    }
}
