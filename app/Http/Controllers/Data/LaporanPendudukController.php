<?php

/*
 * File ini bagian dari:
 *
 * PBB Desa
 *
 * Aplikasi dan source code ini dirilis berdasarkan lisensi GPL V3
 *
 * Hak Cipta 2016 - 2021 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 *
 * Dengan ini diberikan izin, secara gratis, kepada siapa pun yang mendapatkan salinan
 * dari perangkat lunak ini dan file dokumentasi terkait ("Aplikasi Ini"), untuk diperlakukan
 * tanpa batasan, termasuk hak untuk menggunakan, menyalin, mengubah dan/atau mendistribusikan,
 * asal tunduk pada syarat berikut:
 *
 * Pemberitahuan hak cipta di atas dan pemberitahuan izin ini harus disertakan dalam
 * setiap salinan atau bagian penting Aplikasi Ini. Barang siapa yang menghapus atau menghilangkan
 * pemberitahuan ini melanggar ketentuan lisensi Aplikasi Ini.
 *
 * PERANGKAT LUNAK INI DISEDIAKAN "SEBAGAIMANA ADANYA", TANPA JAMINAN APA PUN, BAIK TERSURAT MAUPUN
 * TERSIRAT. PENULIS ATAU PEMEGANG HAK CIPTA SAMA SEKALI TIDAK BERTANGGUNG JAWAB ATAS KLAIM, KERUSAKAN ATAU
 * KEWAJIBAN APAPUN ATAS PENGGUNAAN ATAU LAINNYA TERKAIT APLIKASI INI.
 *
 * @package	    OpenDK
 * @author	    Tim Pengembang OpenDesa
 * @copyright	Hak Cipta 2016 - 2021 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license    	http://www.gnu.org/licenses/gpl.html    GPL V3
 * @link	    https://github.com/OpenSID/opendk
 */

namespace App\Http\Controllers\Data;

use App\Http\Controllers\Controller;
use App\Imports\ImporLaporanPenduduk;
use App\Models\DataDesa;
use App\Models\LaporanPenduduk;
use function back;
use function compact;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use function redirect;
use function route;
use function view;
use Yajra\DataTables\DataTables;
use ZipArchive;

class LaporanPendudukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(LaporanPenduduk $penduduk)
    {
        $page_title       = 'Laporan Penduduk';
        $page_description = 'Data Penduduk';
        $list_desa        = DataDesa::get();
        $list_bulan       = LaporanPenduduk::select('bulan')->groupBy('bulan')->get();

        return view('data.laporan-penduduk.index', compact('page_title', 'page_description', 'list_desa', 'list_bulan'));
    }

    /**
     * Return datatable Data Laporan Penduduk.
     *
     * @param Request $request
     * @return DataTables
     */
    public function getData(Request $request)
    {
        $desa = $request->input('desa');
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');

        $query = DB::table('das_laporan_penduduk')
            ->leftJoin('das_data_desa', 'das_laporan_penduduk.desa_id', '=', 'das_data_desa.desa_id')
            ->select([
                'das_laporan_penduduk.id',
                'das_data_desa.nama as nama_desa',
                'das_laporan_penduduk.judul',
                'das_laporan_penduduk.bulan',
                'das_laporan_penduduk.tahun',
                'das_laporan_penduduk.nama_file',
                'das_laporan_penduduk.imported_at',
            ])
            ->when($desa, function ($query) use ($desa) {
                return $desa === 'ALL'
                    ? $query
                    : $query->where('das_data_desa.desa_id', $desa);
            });

        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                $delete_url = route('data.laporan-penduduk.destroy', $row->id);
                $download_url = asset('storage/laporan_penduduk/' . $row->nama_file);

                return view('forms.action', compact('delete_url', 'download_url'));
            })->make();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy($id)
    {
        try {
            $penduduk = LaporanPenduduk::findOrFail($id);

            // Hapus file penduduk
            Storage::disk('public')->delete('laporan_penduduk/' . $penduduk->nama_file);

            $penduduk->delete();

            return redirect()->route('data.laporan-penduduk.index')->with('success', 'Data sukses dihapus!');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('data.laporan-penduduk.index')->with('error', 'Data gagal dihapus!');
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function import()
    {
        $page_title       = 'Laporan Penduduk';
        $page_description = 'Import Data';

        return view('data.laporan-penduduk.import', compact('page_title', 'page_description'));
    }

    /**
     * Impor data penduduk dari file Excel.
     *
     * @return Response
     */
    public function do_import(Request $request)
    {
        $this->validate($request, [
            'file' => 'file|mimes:zip|max:51200',
        ]);

        try {
            // Upload file zip temporary.
            $file = $request->file('file');
            $file->storeAs('temp', $name = $file->getClientOriginalName());

            // Temporary path file
            $path = storage_path("app/temp/{$name}");
            $extract = storage_path('app/temp/laporan_penduduk/');

            // Ekstrak file
            $zip = new ZipArchive();
            $zip->open($path);
            $zip->extractTo($extract);
            $zip->close();

            // Proses impor excell
            (new ImporLaporanPenduduk())
                ->queue($extract . Str::replaceLast('zip', 'xlsx', $name));
        } catch (Exception $e) {
            return back()->with('error', 'Import data gagal. ' . $e->getMessage());
        }

        return redirect()->route('data.laporan-penduduk.index')->with('success', 'Import data sukses');
    }
}