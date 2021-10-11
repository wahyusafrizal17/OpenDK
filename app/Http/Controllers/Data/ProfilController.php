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
use App\Models\DataDesa;
use App\Models\DataUmum;
use App\Models\Desa;
use App\Models\Profil;
use function back;
use function basename;
use function compact;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function is_img;
use function pathinfo;
use const PATHINFO_EXTENSION;
use function redirect;
use function request;
use function strtolower;
use function strval;
use function substr;

use function ucwords;

use function view;

class ProfilController extends Controller
{
    protected $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = new Client();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        // $host = config('app.host_pantau');
        // $token = config('app.token');
        // try {
        //     $response = $this->client->get("{$host}/index.php/api/wilayah/list_wilayah", [
        //         'query' => [
        //             'token' => $token,
        //             'provinsi' => 'SULAWESI SELATAN',
        //             'kabupaten' => 'WAJO',
        //             'kecamatan' => 'PITUMPANUA',
        //         ]
        //     ]);

        //     if ($response->getStatusCode() === 200) {
        //         $response = json_decode($response->getBody(), true);
        //     }
        // } catch (RequestException $e) {
        //     $response = $e->getResponse();
        // }


        // dd ($response);

        // $result = $res->getBody();

        // $profil = Profil::find(1);
        // $profil->nama_provinsi = $data->nama_provinsi;
        // $profil->nama_kabupaten = $data->nama_kabupaten;
        // $profil->nama_kecamatan = $data->nama_kecamatan;
        // $profil->update();

        // echo $result;
        $profil = $this->profil;

        $profil->file_struktur_organisasi = is_img($this->profil->file_struktur_organisasi);
        $profil->file_logo                = is_img($this->profil->file_logo);
        $profil->foto_kepala_wilayah      = is_img($this->profil->foto_kepala_wilayah);

        $page_title       = 'Ubah Profil';
        $page_description = ucwords(strtolower($this->sebutan_wilayah).' : ' . $this->profil->namae_kecamatan);
        return view('data.profil.edit', compact('page_title', 'page_description', 'profil'));

        // $profil = Profil::find(1);
        // $profil->nama_provinsi = DB::table('ref_wilayah')->where('provinsi_id', $profil->provinsi_id)->first()->nama;
        // $profil->nama_kabupaten = DB::table('ref_wilayah')->where('kabupaten_id', $profil->kabupaten_id)->first()->nama;
        // $profil->nama_kecamatan = DB::table('ref_wilayah')->where('kecamatan_id', $profil->kecamatan_id)->first()->nama;
        // $profil->update();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $page_title       = 'Tambah';
        $page_description = 'Tambah Profil';
        $profil           = new Profil();

        return view('data.profil.create', compact('page_title', 'page_description', 'profil'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        // Save Request
        try {
            request()->validate([
                'kecamatan_id' => 'required',
                'alamat'       => 'required',
                'kode_pos'     => 'required',
                'email'        => 'email',
                'nama_camat'   => 'required',
            ]);

            $profil = new Profil();
            $profil->fill($request->all());
            $profil->kabupaten_id = substr($profil->kecamatan_id, 0, 5);
            $profil->provinsi_id  = substr($profil->kecamatan_id, 0, 2);

            if ($request->hasFile('file_struktur_organisasi')) {
                $file     = $request->file('file_struktur_organisasi');
                $fileName = $file->getClientOriginalName();
                $request->file('file_struktur_organisasi')->move("storage/profil/struktur_organisasi/", $fileName);
                $profil->file_struktur_organisasi = 'storage/profil/struktur_organisasi/' . $fileName;
            }

            if ($request->hasFile('file_logo')) {
                $target_dir        = "uploads/";
                $target_file       = $target_dir . basename($_FILES["file_logo"]["name"]);
                $imageFileType     = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                $profil->file_logo = $target_file . $imageFileType;
            }

            if ($profil->save()) {
                $id = DataUmum::create(['profil_id' => $profil->id, 'kecamatan_id' => $profil->kecamatan_id, 'embed_peta' => 'Edit Peta Pada Menu Data Umum.'])->id;
            }
            $desa      = Desa::where('kecamatan_id', '=', $profil->kecamatan_id)->get();
            $data_desa = [];
            foreach ($desa as $val) {
                $data_desa[] = [
                    'desa_id'      => $val->id,
                    'kecamatan_id' => strval($profil->kecamatan_id),
                    'nama'         => $val->nama,
                ];
            }

            DataDesa::insert($data_desa);
            return redirect()->route('data.profil.success', $id)->with('success', 'Profil berhasil disimpan!');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Profil gagal disimpan!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show()
    {
        $desa              = Desa::where('kecamatan_id', '=', '1107062')->get();
        $data_desa = [];
        foreach ($desa as $val) {
            $data_desa[] = [
                'desa_id'      => strval($val->id),
                'kecamatan_id' => strval('1107062'),
                'nama'         => $val->nama,
            ];
        }

        DataDesa::insert($data_desa);
        return $data_desa;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        $profil = Profil::findOrFail($id);
        if ($profil->file_struktur_organisasi == '') {
            $profil->file_struktur_organisasi = 'http://placehold.it/600x400';
        }
        $page_title       = 'Ubah';
        $page_description = 'Ubah Profil Kecamatan';

        return view('data.profil.edit', compact('page_title', 'page_description', 'profil'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        request()->validate([
            'kecamatan_id'             => 'required',
                'alamat'                   => 'required',
                'kode_pos'                 => 'required',
                'email'                    => 'email',
                'nama_camat'               => 'required',
                'file_logo'                => 'image|mimes:jpg,jpeg,bmp,png,gif|max:1024',
                'file_struktur_organisasi' => 'image|mimes:jpg,jpeg,png,bmp,gif|max:1024',
                'foto_kepala_wilayah'      => 'image|mimes:jpg,jpeg,png,bmp,gif|max:1024',
            ], []);

        try {
            $profil = Profil::find($id);
            $profil->fill($request->all());
            $profil->kabupaten_id = substr($profil->kecamatan_id, 0, 5);
            $profil->provinsi_id  = substr($profil->kecamatan_id, 0, 2);

            $dataumum               = DataUmum::where('profil_id', $id)->first();
            $dataumum->kecamatan_id = $profil->kecamatan_id;

            if ($request->file('file_struktur_organisasi') == "") {
                $profil->file_struktur_organisasi = $profil->file_struktur_organisasi;
            } else {
                $file     = $request->file('file_struktur_organisasi');
                $fileName = $file->getClientOriginalName();
                $request->file('file_struktur_organisasi')->move("storage/profil/struktur_organisasi/", $fileName);
                $profil->file_struktur_organisasi = 'storage/profil/struktur_organisasi/' . $fileName;
            }

            if ($request->file('file_logo') == "") {
                $profil->file_logo = $profil->file_logo;
            } else {
                $fileLogo     = $request->file('file_logo');
                $fileLogoName = $fileLogo->getClientOriginalName();
                $request->file('file_logo')->move("storage/profil/file_logo/", $fileLogoName);
                $profil->file_logo = 'storage/profil/file_logo/' . $fileLogoName;
            }
            if ($request->file('foto_kepala_wilayah') == "") {
                $profil->foto_kepala_wilayah = $profil->foto_kepala_wilayah;
            } else {
                $fileFoto     = $request->file('foto_kepala_wilayah');
                $fileFotoName = $fileFoto->getClientOriginalName();
                $request->file('foto_kepala_wilayah')->move("storage/profil/pegawai/", $fileFotoName);
                $profil->foto_kepala_wilayah = 'storage/profil/pegawai/' . $fileFotoName;
            }

            $profil->update();
            $dataumum->update();
            return redirect()->route('data.profil.success', $profil->dataumum->id)->with('success', 'Update Profil sukses!');
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Update Profil gagal!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        try {
            $profil = Profil::findOrFail($id);
            $profil->dataUmum()->delete();
            $profil->dataDesa()->delete();
            $profil->delete();

            return redirect()->route('data.profil.index')->with('success', 'Profil sukses dihapus!');
        } catch (Exception $e) {
            return redirect()->route('data.profil.index')->with('error', 'Profil gagal dihapus!');
        }
    }

    /**
     * Redirect to edit Data Umum if success
     *
     * @param  int $id
     * @return Response
     */
    public function success($id)
    {
        $page_title       = 'Konfirmasi?';
        $page_description = '';
        return view('data.profil.save_success', compact('id', 'page_title', 'page_description'));
    }
}
