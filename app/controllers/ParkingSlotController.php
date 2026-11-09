<?php
class ParkingSlotController extends Controller
{
    private function areas(): array
    {
        return $this->model('ParkingArea')->all();
    }

    public function index(): void
    {
        requireLogin();
        $this->view('parking_slots/index',[
            'title'=>'Slot Parkir','items'=>$this->model('ParkingSlot')->all()
        ]);
    }

    public function create(): void
    {
        requireLogin();
        $this->view('parking_slots/form',[
            'title'=>'Tambah Slot Parkir','item'=>null,'action'=>'create','areas'=>$this->areas(),'locked'=>false
        ]);
    }

    public function store(): void
    {
        requireLogin();
        $data = $this->input();
        $errors = $this->validate($data, 0);
        if ($errors) {
            $this->fail($errors, $data, 'parking-slots/create');
        }

        try {
            $this->model('ParkingSlot')->create($data);
            flash('success','Slot parkir berhasil ditambahkan.');
        } catch(PDOException $e) {
            error_log('[ParkingSlot::store] ' . $e->getMessage());
            $this->fail(['Gagal menyimpan slot. Kode slot mungkin sudah digunakan.'], $data, 'parking-slots/create');
        }
        $this->redirect('parking-slots');
    }

    public function edit(): void
    {
        requireLogin();
        $id = queryId();
        $model = $this->model('ParkingSlot');
        $item = $model->find($id);
        if(!$item) {
            flash('error', 'Data slot tidak ditemukan.');
            $this->redirect('parking-slots');
        }
        $this->view('parking_slots/form',[
            'title'=>'Edit Slot Parkir','item'=>$item,'action'=>'edit','areas'=>$this->areas(),
            'locked'=>$model->hasActiveEntry($id)
        ]);
    }

    public function update(): void
    {
        requireLogin();
        $id = queryId();
        $model = $this->model('ParkingSlot');
        $current = $model->find($id);
        if (!$current) {
            flash('error', 'Data slot tidak ditemukan.');
            $this->redirect('parking-slots');
        }

        // Slot yang sedang dipakai kendaraan tidak boleh diubah (menghindari data parkir rusak).
        if ($model->hasActiveEntry($id)) {
            flash('error', 'Slot ' . $current['kode_slot'] . ' sedang dipakai kendaraan yang parkir sehingga tidak dapat diubah. Proses kendaraan keluar terlebih dahulu.');
            $this->redirect('parking-slots');
        }

        $data = $this->input();
        $errors = $this->validate($data, $id);
        if ($errors) {
            $this->fail($errors, $data, 'parking-slots/edit?id=' . $id);
        }

        try {
            $model->update($id, $data);
            flash('success','Slot parkir berhasil diubah.');
        } catch(PDOException $e) {
            error_log('[ParkingSlot::update] ' . $e->getMessage());
            $this->fail(['Gagal mengubah slot parkir. Kode slot mungkin sudah digunakan.'], $data, 'parking-slots/edit?id=' . $id);
        }
        $this->redirect('parking-slots');
    }

    public function delete(): void
    {
        requireLogin();
        $id = queryId();
        $model = $this->model('ParkingSlot');
        $item = $model->find($id);
        if (!$item) {
            flash('error', 'Data slot tidak ditemukan.');
            $this->redirect('parking-slots');
        }

        if ($model->hasActiveEntry($id)) {
            flash('error', 'Slot ' . $item['kode_slot'] . ' sedang dipakai kendaraan dan tidak dapat dihapus.');
            $this->redirect('parking-slots');
        }

        if ($model->entryCount($id) > 0) {
            flash('error', 'Slot ' . $item['kode_slot'] . ' sudah memiliki riwayat parkir sehingga tidak dapat dihapus. Ubah statusnya menjadi Rusak jika tidak ingin dipakai lagi.');
            $this->redirect('parking-slots');
        }

        $ok = $model->delete($id);
        flash($ok?'success':'error',$ok?'Slot berhasil dihapus.':'Slot masih digunakan dan tidak dapat dihapus.');
        $this->redirect('parking-slots');
    }

    private function input(): array
    {
        return [
            'id_area'         => (int) post('id_area'),
            'kode_slot'       => strtoupper(preg_replace('/\s+/', '', trim(post('kode_slot'))) ?? ''),
            'jenis_kendaraan' => post('jenis_kendaraan') !== '' ? post('jenis_kendaraan') : 'motor',
            'status'          => post('status') !== '' ? post('status') : 'kosong',
        ];
    }

    private function validate(array $d, int $ignoreId): array
    {
        $errors = [];
        $model = $this->model('ParkingSlot');

        $area = $d['id_area'] > 0 ? $this->model('ParkingArea')->find($d['id_area']) : null;
        if (!$area) {
            $errors[] = 'Area parkir wajib dipilih dari daftar yang tersedia.';
        }

        if ($d['kode_slot'] === '') {
            $errors[] = 'Kode slot wajib diisi.';
        } elseif (preg_match('/^[A-Z0-9][A-Z0-9\-]{1,19}$/', $d['kode_slot']) !== 1) {
            $errors[] = 'Kode slot 2-20 karakter, hanya huruf, angka, dan tanda hubung (contoh: M-A01).';
        } elseif ($model->codeExists($d['kode_slot'], $ignoreId)) {
            $errors[] = 'Kode slot ' . $d['kode_slot'] . ' sudah digunakan.';
        }

        if (!in_array($d['jenis_kendaraan'], ['motor', 'mobil', 'lainnya'], true)) {
            $errors[] = 'Jenis kendaraan slot tidak valid.';
        }

        // Status "terisi" hanya diatur otomatis oleh sistem saat kendaraan masuk.
        if (!in_array($d['status'], ['kosong', 'rusak'], true)) {
            $errors[] = 'Status slot hanya boleh Kosong atau Rusak. Status Terisi diatur otomatis saat kendaraan masuk.';
        }

        // Jumlah slot tidak boleh melebihi kapasitas area.
        if ($area) {
            $others = $model->countInArea($d['id_area'], $ignoreId);
            if ($others >= (int) $area['kapasitas']) {
                $errors[] = 'Kapasitas area "' . $area['nama_area'] . '" (' . (int) $area['kapasitas'] . ' slot) sudah penuh. Tambah kapasitas area atau pilih area lain.';
            }
        }

        return $errors;
    }
}
