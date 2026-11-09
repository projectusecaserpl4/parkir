<?php
class User extends Model
{
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.*, r.nama_role
             FROM users u
             INNER JOIN roles r ON r.id_role = u.id_role
             WHERE u.username = :username
             LIMIT 1'
        );
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    public function all(): array
    {
        return $this->db->query(
            "SELECT u.*, r.nama_role
             FROM users u INNER JOIN roles r ON r.id_role = u.id_role
             ORDER BY u.id_user DESC"
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT u.*, r.nama_role
             FROM users u INNER JOIN roles r ON r.id_role = u.id_role
             WHERE u.id_user = ?"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function usernameExists(string $username, int $ignoreId = 0): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM users WHERE username = ? AND id_user <> ? LIMIT 1"
        );
        $stmt->execute([$username, $ignoreId]);
        return (bool) $stmt->fetchColumn();
    }

    /** Jumlah user Admin aktif (untuk mencegah sistem tanpa admin). */
    public function activeAdminCount(int $ignoreId = 0): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM users u
             INNER JOIN roles r ON r.id_role = u.id_role
             WHERE LOWER(r.nama_role) = 'admin' AND u.status = 'aktif' AND u.id_user <> ?"
        );
        $stmt->execute([$ignoreId]);
        return (int) $stmt->fetchColumn();
    }

    public function transactionCount(int $id): int
    {
        $stmt = $this->db->prepare(
            "SELECT (SELECT COUNT(*) FROM parking_entries WHERE id_user = ?)
                  + (SELECT COUNT(*) FROM parking_exits WHERE id_user = ?)"
        );
        $stmt->execute([$id, $id]);
        return (int) $stmt->fetchColumn();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users
             (id_role, nama_lengkap, username, password, no_hp, status)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['id_role'],
            $data['nama_lengkap'],
            $data['username'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['no_hp'] ?: null,
            $data['status']
        ]);
    }

    public function update(int $id, array $data): bool
    {
        if ($data['password'] !== '') {
            $stmt = $this->db->prepare(
                "UPDATE users SET id_role = ?, nama_lengkap = ?, username = ?,
                 password = ?, no_hp = ?, status = ? WHERE id_user = ?"
            );
            return $stmt->execute([
                $data['id_role'], $data['nama_lengkap'], $data['username'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['no_hp'] ?: null, $data['status'], $id
            ]);
        }

        $stmt = $this->db->prepare(
            "UPDATE users SET id_role = ?, nama_lengkap = ?, username = ?,
             no_hp = ?, status = ? WHERE id_user = ?"
        );
        return $stmt->execute([
            $data['id_role'], $data['nama_lengkap'], $data['username'],
            $data['no_hp'] ?: null, $data['status'], $id
        ]);
    }

    public function setPassword(int $id, string $plainPassword): void
    {
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id_user = ?");
        $stmt->execute([password_hash($plainPassword, PASSWORD_DEFAULT), $id]);
    }

    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id_user = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
