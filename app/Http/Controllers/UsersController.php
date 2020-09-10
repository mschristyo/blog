<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;
use Laravel\Cashier\Cashier;

class UsersController extends Controller {

    public function __construct() {
        $this->middleware('auth');
    }

    public function index() {
        $users = User::latest()->paginate(10);

        return view('users.index', compact('users'))->with('i', (request()->input('page', 1) - 1) * 5);
    }

    public function create() {
        $user = new User();
        $intent = $user->createSetupIntent();

        return view('users.create', compact('intent'));
    }

    public function store(Request $request) {
        $request->validate([
            'username' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);
        $data = $request->all();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        $dat = $user->createAsStripeCustomer()->newSubscription('default', 'price_premium')->create($paymentMethod);

        return redirect()->route('users.index')
                        ->with('success', 'User created successfully.');
    }

    public function show(User $user) {
        return view('users.show', compact('user'));
    }

    public function edit(User $user) {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user) {
        $request->validate([
            'username' => 'required',
            'email' => 'required',
        ]);


        $user->update($request->all());
        return redirect()->route('users.index')
                        ->with('success', 'User updated successfully');
    }

    public function destroy(User $user) {
        $user->delete();
        return redirect()->route('users.index')
                        ->with('success', 'User deleted successfully');
    }

}
