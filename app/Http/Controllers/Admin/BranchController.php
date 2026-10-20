<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    public function show(Branch $branch): Response
    {
        return Inertia::render('Admin/Branches/Show', [
            'branch' => [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
            ],
        ]);
    }
}
