<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        return view('users.index');
    }

    //SEARCHING
    public function getUsers(Request $request)
    {
        $query = User::query();

        if ($request->has('name')) {
            $query->where('name', 'LIKE', '%' . $request->name . '%');
        }

        if ($request->has('email')) {
            $query->where('email', 'LIKE', '%' . $request->email . '%');
        }

        if ($request->has('gender') && $request->gender !== null) {
            $query->where('gender', $request->gender);
        }

        $users = $query->get();
        return response()->json($users);

    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'gender' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'file' => 'nullable|file|mimes:pdf,doc,docx,txt|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('images', 'public');
        }

        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')->store('files', 'public');
        }

        $user = User::create($data);

        return response()->json($user);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }


    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'required|string|max:20',
            'gender' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'file' => 'nullable|file|mimes:pdf,doc,docx,txt|max:2048',
        ]);

        if ($request->hasFile('image')) {
            Storage::delete('public/' . $user->image);
            $data['image'] = $request->file('image')->store('images', 'public');
        }

        if ($request->hasFile('file')) {
            Storage::delete('public/' . $user->file);
            $data['file'] = $request->file('file')->store('files', 'public');
        }

        $user->update($data);

        return response()->json($user);
    }

    public function destroy(User $user)
    {
        Storage::delete('public/' . $user->image);
        Storage::delete('public/' . $user->file);
        $user->delete();

        return response()->json('User deleted successfully');
    }

}
