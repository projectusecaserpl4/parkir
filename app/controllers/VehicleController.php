<?php
class VehicleController extends Controller
{
    private function formData(): array
    {
        return [
            'customers'=>$this->model('Customer')->all(),
            'types'=>$this->model('VehicleType')->all()
        ];
    }

    public function index(): void
    {
        requireLogin();
        $this->view('vehicles/index', [
            'title'=>'Data Kendaraan',
            'vehicles'=>$this->model('Vehicle')->all()
        ]);
    }

    public function create(): void
    {
        requireLogin();
        $this->view('vehicles/form', array_merge(
            ['title'=>'Tambah Kendaraan','vehicle'=>null,'action'=>'create'],
            $this->formData()
        ));
    }

    public function store(): void
    {
        requireLogin();
        $data = $this->input();
        $errors = $this->validate($data, 0);
        if ($errors) {
            $this->fail($errors, $data, 'vehicles/create');
        }

        try {
            $this->model('Vehicle')->create($data);
            flash('success','Kendaraan berhasil ditambahkan.');
        } catch(PDOException $e) {
            error_log('[Vehicle::store] ' . $e->getMessage());
            $this->fail(['Gagal menyimpan kendaraan. Nomor polisi mungkin sudah digunakan.'], $data, 'vehicles/create');
        }
        $this->redirect('vehicles');
    }

    public function edit(): void
    {
        requireLogin();
        $id = queryId();
        $vehicle = $this->model('Vehicle')->find($id);
        if(!$vehicle) {
            flash('error', 'Data kendaraan tidak ditemukan.');
            $this->redirect('vehicles');
        }
        $this->view('vehicles/form', array_merge(
            ['title'=>'Edit Kendaraan','vehicle'=>$vehicle,'action'=>'edit'],
            $this->formData()
        ));
    }

    public function update(): void
    {
        requireLogin();
        $id = queryId();
        $model = $this->model('Vehicle');

        if (!$model->find($id)) {
            flash('error', 'Data kendaraan tidak ditemukan.');
            $this->redirect('vehicles');
        }

        $data = $this->input();
        $errors = $this->validate($data, $id);
        if ($errors) {
            $this->fail($errors, $data, 'vehicles/edit?id=' . $id);
        }

        try {
            $model->update($id, $data);
            flash('success','Kendaraan berhasil diubah.');
        } catch(PDOException $e) {
            error_log('[Vehicle::update] ' . $e->getMessage());
            $this->fail(['Gagal mengubah kendaraan. Nomor polisi mungkin sudah digunakan.'], $data, 'vehicles/edit?id=' . $id);
        }
        $this->redirect('vehicles');
    }

    public function delete(): void
    {
        requireLogin();
        $id = queryId();
        $model = $this->model('Vehicle');

        if (!$model->find($id)) {
            flash('error', 'Data kendaraan tidak ditemukan.');
            $this->redirect('vehicles');
        }

        if ($model->hasActiveParking($id)) {
            flash('error', 'Kendaraan sedang parkir dan tidak dapat dihapus. Proses kendaraan keluar terlebih dahulu.');
            $this->redirect('vehicles');
        }

        if ($model->entryCount($id) > 0) {
            flash('error', 'Kendaraan sudah memiliki riwayat transaksi parkir sehingga tidak dapat dihapus. Ubah statusnya menjadi Nonaktif jika tidak dipakai lagi.');
            $this->redirect('vehicles');
        }

        $ok = $model->delete($id);
        flash($ok ? 'success' : 'error', $ok ? 'Kendaraan berhasil dihapus.' : 'Kendaraan tidak dapat dihapus karena masih digunakan.');
        $this->redirect('vehicles');
    }

    private function input(): array
    {
        return [
            'id_customer'        => (int) post('id_customer'),
            'id_jenis_kendaraan' => (int) post('id_jenis_kendaraan'),
            'nomor_polisi'       => cleanText(post('nomor_polisi')),
            'merk'               => cleanText(post('merk')),
            'model'              => cleanText(post('model')),
            'warna'              => titleCase(cleanText(post('warna'))),
            'tahun'              => trim(post('tahun')),
            'status'             => post('status') !== '' ? post('status') : 'aktif',
        ];
    }

    private function validate(array &$d, int $ignoreId): array
    {
        $errors = [];
        $vehicleModel = $this->model('Vehicle');

        // Pemilik
        $customer = $d['id_customer'] > 0 ? $this->model('Customer')->find($d['id_customer']) : null;
        if (!$customer) {
            $errors[] = 'Pemilik (customer) wajib dipilih dari daftar yang tersedia.';
        }

        // Jenis kendaraan
        $type = $d['id_jenis_kendaraan'] > 0 ? $this->model('VehicleType')->find($d['id_jenis_kendaraan']) : null;
        if (!$type) {
            $errors[] = 'Jenis kendaraan wajib dipilih dari daftar yang tersedia.';
        }

        // Nomor polisi
        if ($d['nomor_polisi'] === '') {
            $errors[] = 'Nomor polisi wajib diisi.';
        } else {
            $plate = normalizePlate($d['nomor_polisi']);
            if ($plate === null) {
                $errors[] = 'Format nomor polisi tidak valid. Contoh yang benar: D 1234 AB (1-2 huruf, 1-4 angka, 0-3 huruf).';
            } else {
                $d['nomor_polisi'] = $plate;
                if ($vehicleModel->plateExists($plate, $ignoreId)) {
                    $errors[] = 'Nomor polisi ' . $plate . ' sudah terdaftar.';
                }
            }
        }

        // Merk / model / warna
        if (textLength($d['merk']) > 50) {
            $errors[] = 'Merk maksimal 50 karakter.';
        } elseif ($d['merk'] !== '' && preg_match('/^[\p{L}0-9][\p{L}0-9 .\'\-]*$/u', $d['merk']) !== 1) {
            $errors[] = 'Merk hanya boleh berisi huruf, angka, spasi, titik, apostrof, dan tanda hubung.';
        }
        if (textLength($d['model']) > 50) {
            $errors[] = 'Model maksimal 50 karakter.';
        } elseif ($d['model'] !== '' && preg_match('/^[\p{L}0-9][\p{L}0-9 .\'\-\/]*$/u', $d['model']) !== 1) {
            $errors[] = 'Model hanya boleh berisi huruf, angka, spasi, titik, apostrof, tanda hubung, dan garis miring.';
        }
        if (textLength($d['warna']) > 30) {
            $errors[] = 'Warna maksimal 30 karakter.';
        } elseif ($d['warna'] !== '' && preg_match('/^\p{L}[\p{L} \-]*$/u', $d['warna']) !== 1) {
            $errors[] = 'Warna hanya boleh berisi huruf (contoh: Hitam, Putih Mutiara).';
        }

        // Kesesuaian jenis kendaraan dengan merk/model (mis. Mobil "Honda Beat" tidak masuk akal)
        if ($type) {
            $typeName = strtolower((string) $type['nama_jenis']);
            if (in_array($typeName, ['motor', 'mobil'], true)) {
                $detected = detectVehicleCategory($d['merk'] . ' ' . $d['model']);
                if ($detected !== null && $detected !== $typeName) {
                    $errors[] = 'Merk/model "' . trim($d['merk'] . ' ' . $d['model']) . '" termasuk kendaraan ' . $detected
                        . ', tidak sesuai dengan jenis kendaraan "' . $type['nama_jenis'] . '". Periksa kembali jenis atau merk/model.';
                }
            }
        }

        // Tahun
        if ($d['tahun'] !== '') {
            $maxYear = (int) date('Y') + 1;
            if (preg_match('/^\d{4}$/', $d['tahun']) !== 1 || (int) $d['tahun'] < 1950 || (int) $d['tahun'] > $maxYear) {
                $errors[] = 'Tahun harus 4 digit angka antara 1950 dan ' . $maxYear . '.';
            }
        }

        // Status
        if (!in_array($d['status'], ['aktif', 'nonaktif'], true)) {
            $errors[] = 'Status kendaraan tidak valid.';
        }

        // Kendaraan yang sedang parkir tidak boleh diubah jenis/statusnya.
        if ($ignoreId > 0 && !$errors) {
            $current = $vehicleModel->find($ignoreId);
            if ($current && $vehicleModel->hasActiveParking($ignoreId)) {
                if ((int) $current['id_jenis_kendaraan'] !== $d['id_jenis_kendaraan']) {
                    $errors[] = 'Jenis kendaraan tidak dapat diubah karena kendaraan sedang parkir. Proses kendaraan keluar terlebih dahulu.';
                }
                if ($d['status'] !== 'aktif') {
                    $errors[] = 'Kendaraan tidak dapat dinonaktifkan karena sedang parkir. Proses kendaraan keluar terlebih dahulu.';
                }
            }
        }

        return $errors;
    }
}
