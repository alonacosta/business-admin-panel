<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class UserSearchController extends Controller
{
    public function __invoke(Request $request): Collection
    {
        $search = $request->string('search');

        return User::query()
            ->when($search,
                fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            )
            ->select('id', 'name', 'email')
            ->limit(8)
            ->get();
    }
}
