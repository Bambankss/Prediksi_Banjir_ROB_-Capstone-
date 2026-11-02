<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Impor;
use Illuminate\View\View;

class ImporController extends Controller
{
    public function index(): View
    {
        $imporList = Impor::latest('dimulai_pada')->paginate(15);

        return view('admin.impor.index', [
            'imporList' => $imporList,
        ]);
    }

    public function show(int $id): View
    {
        $impor = Impor::with('dataHarian.kelurahan')->findOrFail($id);

        return view('admin.impor.show', [
            'impor' => $impor,
        ]);
    }
}

