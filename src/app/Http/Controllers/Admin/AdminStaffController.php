<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AdminStaffController extends Controller
{
    public function index()
    {
        $staffs = User::where('role', 'staff')->orderBy('id', 'asc')->get();

        return view('admin.staff_list', compact('staffs'));
    }
}

