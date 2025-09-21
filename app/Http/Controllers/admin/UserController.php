<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::latest();

        if (!empty($request->get('keyword'))) {
            $users = $users->where('name', 'like', '%' . $request->get('keyword') . '%');
            $users = $users->orWhere('email', 'like', '%' . $request->get('keyword') . '%');
        }
        $users = $users->paginate();
        $data['users'] = $users;
        return view('admin.users.list', $data);
    }

    //Create User
    public function create(Request $request)
    {
        return view('admin.users.create');
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'phone' => 'required'
        ]);
        if ($validator->passes()) {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->phone = $request->phone;
            $user->status = $request->status;
            $user->save();

            session()->flash('Success', 'User added successfully');

            return response()->json([
                'status' => true,
                'message' => 'User added successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    //Edit User
    public function edit(Request $request, $userId)
    {
        $users = User::find($userId);
        if ($users == null) {
            session()->flash('Fail', 'User not found');
            return response()->json([
                'status' => false,
                'message' => "User not found",

            ]);
        }
        $data['users'] = $users;
        return view('admin.users.edit', $data);
    }

    public function update(Request $request, $userId)
    {
        $user = User::find($userId);
        if ($user == null) {
            session()->flash('Fail', 'User not found');
            return response()->json([
                'status' => true,
                'message' => "User not found",

            ]);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $userId . ' id,',
            'phone' => 'required'
        ]);
        if ($validator->passes()) {
            $user->name = $request->name;
            $user->email = $request->email;
            if ($request->password != '') {
                $user->password = Hash::make($request->password);
            }
            $user->phone = $request->phone;
            $user->status = $request->status;
            $user->save();

            session()->flash('Success', 'User Updated successfully');

            return response()->json([
                'status' => true,
                'message' => 'User Updated successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    //Delete User
    public function destroy($userId)
    {
        $user = User::find($userId);

        if (empty($user)) {
            session()->flash('Fail', 'User not found');
            return response()->json([
                'status' => true,
                'message' => "User not found",
            ]);
        }

        $user->delete();

        session()->flash('Success', 'User deleted successfully');

        return response()->json([
            'status' => true,
            'message' => "User deleted successfully",
        ]);
    }
}
