<?php
class ParkingAreaController extends Controller
{
    public function index(): void
    {
        requireLogin();
        $this->view('parking_areas/index',[
            'title'=>'Area Parkir','items'=>$this->model('ParkingArea')->all()
        ]);
    }

    public function create(): void
    {
        requireLogin();
        $this->view('parking_areas/form',['title'=>'Tambah Area Parkir','item'=>null,'action'=>'create']);
    }

    public function store(): void
    {
        requireLogin();
        $data = $this->input();
        $errors = $this->validate($data, 0);
        if ($errors) {
            $this->fail($errors, $data, 'parking-areas/create');
        }

        try {
            $this->model('ParkingArea')->create($data);
            flash('success','Area parkir berhasil ditambahkan.');
        } catch(PDOException $e) {
            error_log('[ParkingArea::store] ' . $e->getMessage());
            $this->fail(['Gagal menyimpan area parkir.'], $data, 'parking-areas/create');
        }
        $this->redirect('parking-areas');
    }

    public function edit(): void
    {
        requireLogin();
        $id = queryId();
        $item = $this->model('ParkingArea')->find($id);
        if(!$item) {
            flash('error', 'Data area parkir tidak ditemukan.');
            $this->redirect('parking-areas');
        }
        $this->view('parking_areas/form',['title'=>'Edit Area Parkir','item'=>$item,'action'=>'edit']);
    }

    public function update(): void
    {
        requireLogin();
        $id = queryId();
        $model = $this->model('ParkingArea');
        if (!$model->find($id)) {
            flash('error', 'Data area parkir tidak ditemukan.');
            $this->redirect('parking-areas');
        }

        $data = $this->input();
        $errors = $this->validate($data, $id);
        if ($errors) {
            $this->fail($errors, $data, 'parking-areas/edit?id=' . $id);
        }

        try {
            $model->update($id, $data);
            flash('success','Area parkir berhasil diubah.');
        } catch(PDOException $e) {
            error_log('[ParkingArea::update] ' . $e->getMessage());
            $this->fail(['Gagal mengubah area parkir.'], $data, 'parking-areas/edit?id=' . $id);
        }
        $this->redirect('parking-areas');
    }

    public function delete(): void
    {
        requireLogin();
        $id = queryId();
        $model = $this->model('ParkingArea');
        if (!$model->find($id)) {
            flash('error', 'Data area parkir tidak ditemukan.');
            $this->redirect('parking-areas');
        }

        $slots = $model->slotCount($id);
        if ($slots > 0) {
            flash('error', 'Area masih memiliki ' . $slots . ' slot parkir dan tidak dapat dihapus. Hapus slotnya terlebih dahulu.');
            $this->redirect('parking-areas');
        }

        $ok = $model->delete($id);
        flash($ok?'success':'error',$ok?'Area parkir berhasil dihapus.':'Area masih digunakan dan tidak dapat dihapus.');
        $this->redirect('parking-areas');
    }

    private function input(): array
    {
        return [
            'nama_area' => cleanText(post('nama_area')),
            'lokasi'    => cleanText(post('lokasi')),
            'kapasitas' => trim(post('kapasitas')),
            'status'    => post('status') !== '' ? post('status') : 'tersedia',
        ];
    }

    private function validate(array &$d, int $ignoreId): array
    {
        $errors = [];
        $model = $this->model('ParkingArea');

        if ($d['nama_area'] === '') {
            $errors[] = 'Nama area wajib diisi.';
        } elseif (textLength($d['nama_area']) < 3 || textLength($d['nama_area']) > 100) {
            $errors[] = 'Nama area minimal 3 karakter dan maksimal 100 karakter.';
        } elseif (preg_match('/^[\p{L}0-9][\p{L}0-9 .\'\-\/]*$/u', $d['nama_area']) !== 1) {
            $errors[] = 'Nama area hanya boleh berisi huruf, angka, spasi, titik, apostrof, tanda hubung, dan garis miring.';
        } elseif ($model->nameExists($d['nama_area'], $ignoreId)) {
            $errors[] = 'Nama area "' . $d['nama_area'] . '" sudah digunakan.';
        }

        if (textLength($d['lokasi']) > 255) {
            $errors[] = 'Lokasi maksimal 255 karakter.';
        }

        if (preg_match('/^\d{1,5}$/', $d['kapasitas']) !== 1 || (int) $d['kapasitas'] < 1 || (int) $d['kapasitas'] > 10000) {
            $errors[] = 'Kapasitas harus berupa bilangan bulat antara 1 dan 10.000.';
        } else {
            $d['kapasitas'] = (int) $d['kapasitas'];
            if ($ignoreId > 0) {
                $slots = $model->slotCount($ignoreId);
                if ($d['kapasitas'] < $slots) {
                    $errors[] = 'Kapasitas (' . $d['kapasitas'] . ') tidak boleh lebih kecil dari jumlah slot yang sudah dibuat di area ini (' . $slots . ').';
                }
            }
        }

        if (!in_array($d['status'], ['tersedia', 'ditutup'], true)) {
            $errors[] = 'Status area tidak valid.';
        }

        return $errors;
    }
}
