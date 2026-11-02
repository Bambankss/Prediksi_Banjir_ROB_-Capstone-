<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class TentangController extends Controller
{
    public function index(): View
    {
        $modelVersi = env('MODEL_VERSI_AKTIF', 'stub-v0');

        return view('tentang', [
            'modelVersi' => $modelVersi,
            'isSimulasi' => str_starts_with($modelVersi, 'stub'),
        ]);
    }
}

