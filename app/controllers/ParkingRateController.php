<?php
class ParkingRateController extends Controller
{
    public function index(): void
    {
        requireLogin();
        $this->view('parking_rates/index',[
            'title'=>'Tarif Parkir','items'=>$this->model('ParkingRate')->all()
        ]);
    }

    public function create(): void
    {
        requireLogin();
        $this->view('parking_rates/form',[
            'title'=>'Tambah Tarif','item'=>null,'action'=>'create',
            'types'=>$this->model('VehicleType')->all()
        ]);
    }

    public function store(): void
    {
        requireLogin();
        $raw = $this->rawInput();
        $errors = $this->validate($raw, $data);
        if ($errors) {
            $this->fail($errors, $raw, 'parking-rates/create');
        }

        try {
            $this->model('ParkingRate')->create($data);
            flash('success','Tarif berhasil ditambahkan.');
        } catch(PDOException $e) {
            error_log('[ParkingRate::store] ' . $e->getMessage());
            $this->fail(['Gagal menyimpan tarif.'], $raw, 'parking-rates/create');
        }
        $this->redirect('parking-rates');
    }

    public function edit(): void
    {
        requireLogin();
        $id = queryId();
        $item = $this->model('ParkingRate')->find($id);
        if(!$item) {
            flash('error', 'Data tarif tidak ditemukan.');
            $this->redirect('parking-rates');
        }
        $this->view('parking_rates/form',[
            'title'=>'Edit Tarif','item'=>$item,'action'=>'edit',
            'types'=>$this->model('VehicleType')->all()
        ]);
    }

    public function update(): void
    {
        requireLogin();
        $id = queryId();
        $model = $this->model('ParkingRate');
        if (!$model->find($id)) {
            flash('error', 'Data tarif tidak ditemukan.');
            $this->redirect('parking-rates');
        }

        $raw = $this->rawInput();
        $errors = $this->validate($raw, $data);
        if ($errors) {
            $this->fail($errors, $raw, 'parking-rates/edit?id=' . $id);
        }

        try {
            $model->update($id, $data);
            flash('success','Tarif berhasil diubah.');
        } catch(PDOException $e) {
            error_log('[ParkingRate::update] ' . $e->getMessage());
            $this->fail(['Gagal mengubah tarif.'], $raw, 'parking-rates/edit?id=' . $id);
        }
        $this->redirect('parking-rates');
    }

    public function delete(): void
    {
        requireLogin();
        $id = queryId();
        $model = $this->model('ParkingRate');
        if (!$model->find($id)) {
            flash('error', 'Data tarif tidak ditemukan.');
            $this->redirect('parking-rates');
        }

        if ($model->paymentCount($id) > 0) {
            flash('error', 'Tarif sudah dipakai pada transaksi pembayaran sehingga tidak dapat dihapus. Ubah statusnya menjadi Nonaktif.');
            $this->redirect('parking-rates');
        }

        $ok = $model->delete($id);
        flash($ok?'success':'error',$ok?'Tarif berhasil dihapus.':'Tarif masih digunakan dan tidak dapat dihapus.');
        $this->redirect('parking-rates');
    }

    private function rawInput(): array
    {
        return [
            'id_jenis_kendaraan' => trim(post('id_jenis_kendaraan')),
            'tarif_per_jam'      => trim(post('tarif_per_jam')),
            'tarif_maksimal'     => trim(post('tarif_maksimal')),
            'berlaku_mulai'      => trim(post('berlaku_mulai')),
            'status'             => post('status') !== '' ? post('status') : 'aktif',
        ];
    }

    /**
     * Validasi isian mentah. Jika lolos, $data berisi nilai yang sudah dibersihkan
     * dan siap disimpan ke database.
     */
    private function validate(array $raw, &$data = null): array
    {
        $errors = [];
        $data = null;

        $typeId = (int) $raw['id_jenis_kendaraan'];
        $type = $typeId > 0 ? $this->model('VehicleType')->find($typeId) : null;
        if (!$type) {
            $errors[] = 'Jenis kendaraan wajib dipilih dari daftar yang tersedia.';
        }

        $perHour = parseDecimal($raw['tarif_per_jam']);
        if ($raw['tarif_per_jam'] === '') {
            $errors[] = 'Tarif per jam wajib diisi.';
        } elseif ($perHour === null) {
            $errors[] = 'Tarif per jam harus berupa angka (tanpa titik ribuan / huruf), contoh: 3000.';
        } elseif ($perHour <= 0) {
            $errors[] = 'Tarif per jam harus lebih besar dari 0.';
        } elseif ($perHour > 99999999) {
            $errors[] = 'Tarif per jam terlalu besar (maksimal 99.999.999).';
        }

        $max = null;
        if ($raw['tarif_maksimal'] !== '') {
            $max = parseDecimal($raw['tarif_maksimal']);
            if ($max === null) {
                $errors[] = 'Tarif maksimal harus berupa angka (tanpa titik ribuan / huruf), contoh: 15000.';
            } elseif ($max <= 0) {
                $errors[] = 'Tarif maksimal harus lebih besar dari 0, atau kosongkan jika tidak ada batas.';
            } elseif ($max > 99999999) {
                $errors[] = 'Tarif maksimal terlalu besar (maksimal 99.999.999).';
            } elseif ($perHour !== null && $perHour > 0 && $max < $perHour) {
                $errors[] = 'Tarif maksimal tidak boleh lebih kecil dari tarif per jam.';
            }
        }

        $start = date('Y-m-d H:i:s');
        if ($raw['berlaku_mulai'] !== '') {
            $parsed = parseDateTimeInput($raw['berlaku_mulai']);
            if ($parsed === null) {
                $errors[] = 'Format tanggal "Berlaku Mulai" tidak valid.';
            } else {
                $start = $parsed;
            }
        }

        if (!in_array($raw['status'], ['aktif', 'nonaktif'], true)) {
            $errors[] = 'Status tarif tidak valid.';
        }

        if (!$errors) {
            $data = [
                'id_jenis_kendaraan' => $typeId,
                'tarif_per_jam'      => $perHour,
                'tarif_maksimal'     => $max,
                'berlaku_mulai'      => $start,
                'status'             => $raw['status'],
            ];
        }

        return $errors;
    }
}
