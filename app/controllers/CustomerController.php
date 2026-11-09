<?php
class CustomerController extends Controller
{
    public function index(): void
    {
        requireLogin();
        $model = $this->model('Customer');
        $this->view('customers/index', ['title'=>'Data Customer', 'customers'=>$model->all()]);
    }

    public function create(): void
    {
        requireLogin();
        $this->view('customers/form', [
            'title'=>'Tambah Customer',
            'customer'=>null,
            'action'=>'create'
        ]);
    }

    public function store(): void
    {
        requireLogin();

        $data = $this->input();
        $errors = $this->validate($data, 0);
        if ($errors) {
            $this->fail($errors, $data, 'customers/create');
        }

        try {
            $this->model('Customer')->create($data);
            flash('success', 'Customer berhasil ditambahkan.');
        } catch (PDOException $e) {
            error_log('[Customer::store] ' . $e->getMessage());
            $this->fail(['Gagal menyimpan customer. NIK mungkin sudah digunakan.'], $data, 'customers/create');
        }
        $this->redirect('customers');
    }

    public function edit(): void
    {
        requireLogin();
        $id = queryId();
        $customer = $this->model('Customer')->find($id);
        if (!$customer) {
            flash('error', 'Data customer tidak ditemukan.');
            $this->redirect('customers');
        }

        $this->view('customers/form', [
            'title'=>'Edit Customer', 'customer'=>$customer, 'action'=>'edit'
        ]);
    }

    public function update(): void
    {
        requireLogin();
        $id = queryId();
        $model = $this->model('Customer');

        if (!$model->find($id)) {
            flash('error', 'Data customer tidak ditemukan.');
            $this->redirect('customers');
        }

        $data = $this->input();
        $errors = $this->validate($data, $id);
        if ($errors) {
            $this->fail($errors, $data, 'customers/edit?id=' . $id);
        }

        try {
            $model->update($id, $data);
            flash('success', 'Customer berhasil diubah.');
        } catch (PDOException $e) {
            error_log('[Customer::update] ' . $e->getMessage());
            $this->fail(['Gagal mengubah customer. NIK mungkin sudah digunakan.'], $data, 'customers/edit?id=' . $id);
        }
        $this->redirect('customers');
    }

    public function delete(): void
    {
        requireLogin();
        $id = queryId();
        $model = $this->model('Customer');

        if (!$model->find($id)) {
            flash('error', 'Data customer tidak ditemukan.');
            $this->redirect('customers');
        }

        $vehicles = $model->vehicleCount($id);
        if ($vehicles > 0) {
            flash('error', 'Customer tidak dapat dihapus karena masih memiliki ' . $vehicles . ' kendaraan terdaftar. Hapus kendaraannya terlebih dahulu.');
            $this->redirect('customers');
        }

        $ok = $model->delete($id);
        flash($ok ? 'success' : 'error', $ok ? 'Customer berhasil dihapus.' : 'Customer tidak dapat dihapus karena masih digunakan.');
        $this->redirect('customers');
    }

    private function input(): array
    {
        return [
            'nama_customer' => titleCase(cleanText(post('nama_customer'))),
            'nik'           => preg_replace('/\s+/', '', trim(post('nik'))) ?? '',
            'no_hp'         => normalizePhone(post('no_hp')),
            'alamat'        => trim(post('alamat')),
        ];
    }

    private function validate(array $d, int $ignoreId): array
    {
        $errors = [];
        $model = $this->model('Customer');

        if ($d['nama_customer'] === '') {
            $errors[] = 'Nama customer wajib diisi.';
        } elseif (!isValidPersonName($d['nama_customer'])) {
            $errors[] = 'Nama customer minimal 3 karakter, maksimal 100 karakter, hanya huruf, spasi, titik, apostrof, dan tanda hubung (tidak boleh angka).';
        }

        if ($d['nik'] !== '') {
            if (!isValidNik($d['nik'])) {
                $errors[] = 'NIK harus terdiri dari tepat 16 digit angka.';
            } elseif ($model->nikExists($d['nik'], $ignoreId)) {
                $errors[] = 'NIK ' . $d['nik'] . ' sudah terdaftar pada customer lain.';
            }
        }

        if ($d['no_hp'] !== '' && !isValidPhone($d['no_hp'])) {
            $errors[] = 'No. HP tidak valid. Gunakan angka saja, diawali 0 atau +62 (10-14 digit), contoh: 081234567890.';
        }

        if (textLength($d['alamat']) > 255) {
            $errors[] = 'Alamat maksimal 255 karakter.';
        }

        if ($d['nama_customer'] !== '' && !$errors && $model->duplicateExists($d['nama_customer'], $d['no_hp'], $ignoreId)) {
            $errors[] = 'Customer dengan nama dan No. HP yang sama sudah ada. Gunakan data yang berbeda atau perbarui data yang sudah ada.';
        }

        return $errors;
    }
}
