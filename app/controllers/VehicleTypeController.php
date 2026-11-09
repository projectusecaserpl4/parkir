<?php
class VehicleTypeController extends Controller
{
    /** Jenis bawaan yang dipakai logika pencocokan slot; tidak boleh diganti nama / dihapus. */
    private const PROTECTED_TYPES = ['motor', 'mobil'];

    public function index(): void
    {
        requireLogin();
        $this->view('vehicle_types/index', [
            'title'=>'Jenis Kendaraan',
            'items'=>$this->model('VehicleType')->all()
        ]);
    }

    public function create(): void
    {
        requireLogin();
        $this->view('vehicle_types/form', ['title'=>'Tambah Jenis Kendaraan','item'=>null,'action'=>'create']);
    }

    public function store(): void
    {
        requireLogin();
        $data = $this->input();
        $errors = $this->validate($data, 0);
        if ($errors) {
            $this->fail($errors, $data, 'vehicle-types/create');
        }

        try {
            $this->model('VehicleType')->create($data);
            flash('success','Jenis kendaraan berhasil ditambahkan.');
        } catch (PDOException $e) {
            error_log('[VehicleType::store] ' . $e->getMessage());
            $this->fail(['Gagal menyimpan jenis kendaraan. Nama jenis mungkin sudah digunakan.'], $data, 'vehicle-types/create');
        }
        $this->redirect('vehicle-types');
    }

    public function edit(): void
    {
        requireLogin();
        $id = queryId();
        $item = $this->model('VehicleType')->find($id);
        if(!$item) {
            flash('error', 'Data jenis kendaraan tidak ditemukan.');
            $this->redirect('vehicle-types');
        }
        $this->view('vehicle_types/form',['title'=>'Edit Jenis Kendaraan','item'=>$item,'action'=>'edit']);
    }

    public function update(): void
    {
        requireLogin();
        $id = queryId();
        $model = $this->model('VehicleType');
        $current = $model->find($id);
        if (!$current) {
            flash('error', 'Data jenis kendaraan tidak ditemukan.');
            $this->redirect('vehicle-types');
        }

        $data = $this->input();
        $errors = $this->validate($data, $id);

        if (!$errors
            && in_array(strtolower($current['nama_jenis']), self::PROTECTED_TYPES, true)
            && strtolower($data['nama_jenis']) !== strtolower($current['nama_jenis'])) {
            $errors[] = 'Jenis bawaan "' . $current['nama_jenis'] . '" tidak dapat diganti namanya karena dipakai untuk pencocokan slot parkir.';
        }

        if ($errors) {
            $this->fail($errors, $data, 'vehicle-types/edit?id=' . $id);
        }

        try {
            $model->update($id, $data);
            flash('success','Jenis kendaraan berhasil diubah.');
        } catch(PDOException $e) {
            error_log('[VehicleType::update] ' . $e->getMessage());
            $this->fail(['Gagal mengubah jenis kendaraan. Nama jenis mungkin sudah digunakan.'], $data, 'vehicle-types/edit?id=' . $id);
        }
        $this->redirect('vehicle-types');
    }

    public function delete(): void
    {
        requireLogin();
        $id = queryId();
        $model = $this->model('VehicleType');
        $item = $model->find($id);
        if (!$item) {
            flash('error', 'Data jenis kendaraan tidak ditemukan.');
            $this->redirect('vehicle-types');
        }

        if (in_array(strtolower($item['nama_jenis']), self::PROTECTED_TYPES, true)) {
            flash('error', 'Jenis bawaan "' . $item['nama_jenis'] . '" tidak dapat dihapus karena dipakai oleh sistem.');
            $this->redirect('vehicle-types');
        }

        $usage = $model->usage($id);
        if ($usage['kendaraan'] > 0 || $usage['tarif'] > 0) {
            flash('error', 'Jenis kendaraan masih digunakan (' . $usage['kendaraan'] . ' kendaraan, ' . $usage['tarif'] . ' tarif) dan tidak dapat dihapus.');
            $this->redirect('vehicle-types');
        }

        $ok = $model->delete($id);
        flash($ok?'success':'error',$ok?'Jenis kendaraan berhasil dihapus.':'Jenis kendaraan masih digunakan dan tidak dapat dihapus.');
        $this->redirect('vehicle-types');
    }

    private function input(): array
    {
        return [
            'nama_jenis' => titleCase(cleanText(post('nama_jenis'))),
            'deskripsi'  => cleanText(post('deskripsi')),
        ];
    }

    private function validate(array $d, int $ignoreId): array
    {
        $errors = [];

        if ($d['nama_jenis'] === '') {
            $errors[] = 'Nama jenis wajib diisi.';
        } elseif (textLength($d['nama_jenis']) < 3 || textLength($d['nama_jenis']) > 50 || preg_match('/^\p{L}[\p{L} ]*$/u', $d['nama_jenis']) !== 1) {
            $errors[] = 'Nama jenis minimal 3 karakter, maksimal 50 karakter, hanya huruf dan spasi (tidak boleh angka atau simbol).';
        } elseif ($this->model('VehicleType')->nameExists($d['nama_jenis'], $ignoreId)) {
            $errors[] = 'Jenis kendaraan "' . $d['nama_jenis'] . '" sudah ada.';
        }

        if (textLength($d['deskripsi']) > 255) {
            $errors[] = 'Deskripsi maksimal 255 karakter.';
        }

        return $errors;
    }
}
