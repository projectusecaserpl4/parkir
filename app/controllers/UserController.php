<?php
class UserController extends Controller
{
    public function index(): void
    {
        requireLogin();
        $this->view('users/index',[
            'title'=>'Data User','items'=>$this->model('User')->all()
        ]);
    }

    public function create(): void
    {
        requireLogin();
        $this->view('users/form',[
            'title'=>'Tambah User','item'=>null,'action'=>'create',
            'roles'=>$this->model('Role')->all(),'isSelf'=>false
        ]);
    }

    public function store(): void
    {
        requireLogin();
        $data = $this->input();
        $errors = $this->validate($data, 0, true);
        if ($errors) {
            $this->fail($errors, $data, 'users/create');
        }

        try {
            $this->model('User')->create($data);
            flash('success','User berhasil ditambahkan.');
        } catch(PDOException $e) {
            error_log('[User::store] ' . $e->getMessage());
            $this->fail(['Gagal menyimpan user. Username mungkin sudah digunakan.'], $data, 'users/create');
        }
        $this->redirect('users');
    }

    public function edit(): void
    {
        requireLogin();
        $id = queryId();
        $item = $this->model('User')->find($id);
        if(!$item) {
            flash('error', 'Data user tidak ditemukan.');
            $this->redirect('users');
        }
        $this->view('users/form',[
            'title'=>'Edit User','item'=>$item,'action'=>'edit',
            'roles'=>$this->model('Role')->all(),
            'isSelf'=>$id === (int) $_SESSION['user']['id_user']
        ]);
    }

    public function update(): void
    {
        requireLogin();
        $id = queryId();
        $model = $this->model('User');
        $current = $model->find($id);
        if (!$current) {
            flash('error', 'Data user tidak ditemukan.');
            $this->redirect('users');
        }

        $data = $this->input();
        $isSelf = $id === (int) $_SESSION['user']['id_user'];
        $errors = $this->validate($data, $id, false);

        if (!$errors && $isSelf) {
            // Cegah admin mengunci dirinya sendiri dari sistem.
            if ((int) $current['id_role'] !== $data['id_role']) {
                $errors[] = 'Anda tidak dapat mengubah role akun yang sedang Anda gunakan.';
            }
            if ($data['status'] !== 'aktif') {
                $errors[] = 'Anda tidak dapat menonaktifkan akun yang sedang Anda gunakan.';
            }
        }

        if (!$errors && strtolower((string) $current['nama_role']) === 'admin' && $current['status'] === 'aktif') {
            $newRole = $this->model('Role')->find($data['id_role']);
            $stillActiveAdmin = $newRole && strtolower($newRole['nama_role']) === 'admin' && $data['status'] === 'aktif';
            if (!$stillActiveAdmin && $model->activeAdminCount($id) === 0) {
                $errors[] = 'Perubahan ditolak karena sistem harus memiliki minimal satu Admin aktif.';
            }
        }

        if ($errors) {
            $this->fail($errors, $data, 'users/edit?id=' . $id);
        }

        try {
            $model->update($id, $data);
            if ($isSelf) {
                $_SESSION['user']['nama_lengkap'] = $data['nama_lengkap'];
                $_SESSION['user']['username'] = $data['username'];
            }
            flash('success','User berhasil diubah.');
        } catch(PDOException $e) {
            error_log('[User::update] ' . $e->getMessage());
            $this->fail(['Gagal mengubah user. Username mungkin sudah digunakan.'], $data, 'users/edit?id=' . $id);
        }
        $this->redirect('users');
    }

    public function delete(): void
    {
        requireLogin();
        $id = queryId();
        $model = $this->model('User');
        $user = $model->find($id);

        if (!$user) {
            flash('error', 'Data user tidak ditemukan.');
            $this->redirect('users');
        }

        if ($id === (int)$_SESSION['user']['id_user']) {
            flash('error','Anda tidak dapat menghapus akun yang sedang digunakan.');
            $this->redirect('users');
        }

        if (strtolower((string) $user['nama_role']) === 'admin' && $user['status'] === 'aktif' && $model->activeAdminCount($id) === 0) {
            flash('error', 'User ini adalah satu-satunya Admin aktif sehingga tidak dapat dihapus.');
            $this->redirect('users');
        }

        $count = $model->transactionCount($id);
        if ($count > 0) {
            flash('error', 'User tidak dapat dihapus karena sudah memiliki ' . $count . ' data transaksi parkir. Ubah statusnya menjadi Nonaktif.');
            $this->redirect('users');
        }

        $ok = $model->delete($id);
        flash($ok?'success':'error',$ok?'User berhasil dihapus.':'User tidak dapat dihapus karena masih memiliki data transaksi.');
        $this->redirect('users');
    }

    private function input(): array
    {
        return [
            'id_role'      => (int) post('id_role'),
            'nama_lengkap' => titleCase(cleanText(post('nama_lengkap'))),
            'username'     => strtolower(trim(post('username'))),
            'password'     => post('password'),
            'no_hp'        => normalizePhone(post('no_hp')),
            'status'       => post('status') !== '' ? post('status') : 'aktif',
        ];
    }

    private function validate(array $d, int $ignoreId, bool $passwordRequired): array
    {
        $errors = [];
        $model = $this->model('User');

        if (!$d['id_role'] || !$this->model('Role')->find($d['id_role'])) {
            $errors[] = 'Role wajib dipilih dari daftar yang tersedia.';
        }

        if ($d['nama_lengkap'] === '') {
            $errors[] = 'Nama lengkap wajib diisi.';
        } elseif (textLength($d['nama_lengkap']) < 3 || textLength($d['nama_lengkap']) > 100 || preg_match('/^\p{L}[\p{L} .\'\-\/,]*$/u', $d['nama_lengkap']) !== 1) {
            $errors[] = 'Nama lengkap minimal 3 karakter, maksimal 100 karakter, hanya huruf, spasi, titik, koma, apostrof, tanda hubung, dan garis miring (tidak boleh angka).';
        }

        if ($d['username'] === '') {
            $errors[] = 'Username wajib diisi.';
        } elseif (preg_match('/^[a-z0-9_.]{4,30}$/', $d['username']) !== 1) {
            $errors[] = 'Username 4-30 karakter, hanya huruf kecil, angka, titik, dan garis bawah (tanpa spasi).';
        } elseif ($model->usernameExists($d['username'], $ignoreId)) {
            $errors[] = 'Username "' . $d['username'] . '" sudah digunakan.';
        }

        if ($d['password'] === '') {
            if ($passwordRequired) {
                $errors[] = 'Password wajib diisi.';
            }
        } elseif (strlen($d['password']) < 6) {
            $errors[] = 'Password minimal 6 karakter.';
        } elseif (strlen($d['password']) > 72) {
            $errors[] = 'Password maksimal 72 karakter.';
        }

        if ($d['no_hp'] !== '' && !isValidPhone($d['no_hp'])) {
            $errors[] = 'No. HP tidak valid. Gunakan angka saja, diawali 0 atau +62 (10-14 digit), contoh: 081234567890.';
        }

        if (!in_array($d['status'], ['aktif', 'nonaktif'], true)) {
            $errors[] = 'Status user tidak valid.';
        }

        return $errors;
    }
}
