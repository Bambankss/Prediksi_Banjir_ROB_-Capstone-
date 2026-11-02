<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use Illuminate\View\View;

class AktivitasController extends Controller
{
    public function index(): View
    {
        $logs = LogAktivitas::latest('waktu')->paginate(25);

        return view('admin.aktivitas.index', [
            'logs' => $logs,
        ]);
    }
}

