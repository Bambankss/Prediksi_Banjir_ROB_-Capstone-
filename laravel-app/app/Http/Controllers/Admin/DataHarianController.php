<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataHarian;
use App\Models\Kelurahan;
use App\Models\LogAktivitas;
use App\Models\Prediksi;
use App\Models\StatusPrediksi;
use App\Services\PrediksiService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DataHarianController extends Controller
{
    public function index(Request $request): View
    {
        $modelVersi = env('MODEL_VERSI_AKTIF', 'stub-v0');
        $search = $request->query('q');

        // Query daftar tanggal dan kelengkapannya
        $query = DataHarian::select('tanggal', DB::raw('COUNT(DISTINCT kelurahan_id) as total_kelurahan'))
            ->groupBy('tanggal');

        if ($search) {
            $query->where('tanggal', 'like', "%{$search}%");
        }

        $dates = $query->orderBy('tanggal', 'desc')->paginate(20)->withQueryString();

        // Ambil status prediksi untuk tanggal-tanggal di halaman ini
        $pageDates = $dates->pluck('tanggal')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))->all();

        $statusList = StatusPrediksi::where('model_versi', $modelVersi)
            ->whereIn('tanggal', $pageDates)
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->tanggal)->format('Y-m-d'));

        return view('admin.data.index', [
            'dates' => $dates,
            'statusList' => $statusList,
            'modelVersi' => $modelVersi,
            'search' => $search,
        ]);
    }

    public function edit(string $tanggal): View
    {
        $kelurahans = Kelurahan::orderBy('nama')->get();
        $harianRecords = DataHarian::where('tanggal', $tanggal)
            ->get()
            ->keyBy('kelurahan_id');

        // Kelurahan yang belum memiliki data
        $missingKelurahan = $kelurahans->filter(function ($kel) use ($harianRecords) {
            return !isset($harianRecords[$kel->id]);
        });

        $modelVersi = env('MODEL_VERSI_AKTIF', 'stub-v0');
        $statusPrediksi = StatusPrediksi::where('tanggal', $tanggal)
            ->where('model_versi', $modelVersi)
            ->first();

        return view('admin.data.edit', [
            'tanggal' => $tanggal,
            'kelurahans' => $kelurahans,
            'harianRecords' => $harianRecords,
            'missingKelurahan' => $missingKelurahan,
            'statusPrediksi' => $statusPrediksi,
            'modelVersi' => $modelVersi,
        ]);
    }

    public function update(string $tanggal, Request $request, PrediksiService $prediksiService): RedirectResponse
    {
        $request->validate([
            'items' => ['required', 'array'],
            'items.*.curah_hujan' => ['required', 'numeric', 'min:0'],
            'items.*.pasang_tertinggi' => ['required', 'numeric'],
            'items.*.gelombang_tertinggi' => ['required', 'numeric', 'min:0'],
        ]);

        $kelurahanIds = Kelurahan::pluck('id')->all();
        $items = $request->input('items', []);

        DB::beginTransaction();
        try {
            foreach ($items as $kelId => $data) {
                if (!in_array((int) $kelId, $kelurahanIds, true)) {
                    continue;
                }

                DataHarian::updateOrCreate(
                    [
                        'kelurahan_id' => $kelId,
                        'tanggal' => $tanggal,
                    ],
                    [
                        'curah_hujan' => (float) $data['curah_hujan'],
                        'pasang_tertinggi' => (float) $data['pasang_tertinggi'],
                        'gelombang_tertinggi' => (float) $data['gelombang_tertinggi'],
                        'sumber' => 'manual',
                        'updated_at' => now(),
                    ]
                );
            }

            LogAktivitas::create([
                'admin_id' => Auth::id(),
                'aksi' => 'update',
                'tabel' => 'data_harian',
                'ref_id' => null,
                'detail' => ['tanggal' => $tanggal, 'jumlah_kelurahan' => count($items)],
                'waktu' => now(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }

        // Otomatis coba hitung ulang prediksi setelah penyimpanan
        $hitungResult = $prediksiService->hitungTanggal($tanggal, force: true);

        if ($hitungResult['success']) {
            return redirect()->route('admin.data.edit', $tanggal)
                ->with('success', 'Data harian berhasil disimpan dan prediksi berhasil diperbarui!');
        } else {
            return redirect()->route('admin.data.edit', $tanggal)
                ->with('warning', 'Data harian berhasil disimpan, namun hitung ulang prediksi: ' . $hitungResult['message']);
        }
    }

    public function destroy(string $tanggal): RedirectResponse
    {
        DB::beginTransaction();
        try {
            DataHarian::where('tanggal', $tanggal)->delete();
            Prediksi::where('tanggal', $tanggal)->delete();
            StatusPrediksi::where('tanggal', $tanggal)->delete();

            LogAktivitas::create([
                'admin_id' => Auth::id(),
                'aksi' => 'delete',
                'tabel' => 'data_harian',
                'ref_id' => null,
                'detail' => ['tanggal' => $tanggal],
                'waktu' => now(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }

        return redirect()->route('admin.data.index')->with('success', "Seluruh data tanggal {$tanggal} berhasil dihapus.");
    }

    public function hitung(string $tanggal, PrediksiService $prediksiService): RedirectResponse
    {
        $res = $prediksiService->hitungTanggal($tanggal, force: true);

        LogAktivitas::create([
            'admin_id' => Auth::id(),
            'aksi' => 'hitung',
            'tabel' => 'prediksi',
            'ref_id' => null,
            'detail' => ['tanggal' => $tanggal, 'result' => $res],
            'waktu' => now(),
        ]);

        if ($res['success']) {
            return back()->with('success', $res['message']);
        }

        return back()->with('error', $res['message']);
    }
}

