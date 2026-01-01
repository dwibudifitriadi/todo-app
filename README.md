# Todo List API Documentation

Dokumentasi lengkap seluruh endpoint di folder `/api`. Semua respons berbentuk JSON. Operasi penulisan data membutuhkan user yang sudah login (session PHP) dan token CSRF yang valid.

## Base URL
Gunakan base: `https://www.dwibudifitriadi.me/api/` (encode spasi pada path `UAS PW`).

## Konvensi Umum
- Autentikasi: wajib session login; jika tidak akan mengembalikan 401.
- CSRF: semua `POST` memerlukan field `csrf_token` yang valid.
- Konten: kirim parameter sebagai `application/x-www-form-urlencoded` atau `multipart/form-data`.
- Struktur sukses: biasanya `{ "success": true, ... }`.
- Struktur error: `{ "success": false, "message": "..." }` dengan status 4xx/5xx.

## HTTP Status
| Kode | Arti |
| ---- | ---- |
| 200 | OK |
| 400 | Bad Request (parameter kurang/invalid) |
| 401 | Unauthorized (belum login) |
| 403 | Forbidden (bukan pemilik data / CSRF invalid) |
| 404 | Not Found |
| 500 | Internal Server Error |

## Endpoint

### 1. Create Todo
- URL: `/api/create.php`
- Method: POST (login + CSRF)
- Body:
  - `title` (wajib)
  - `description` (opsional)
  - `status` (opsional, default `pending`)
  - `priority` (opsional)
  - `energy_level` (opsional)
  - `tags` (opsional, string id tag dipisah koma, mis. `1,2,3`)
  - `csrf_token`
- Response berhasil: `{ "success": true, "id": <todo_id> }`
- Response gagal (contoh): `{ "success": false, "message": "Title is required" }`

Contoh:
```bash
curl -X POST "https://www.dwibudifitriadi.me/api/create.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "title=Belajar PHP&description=Pelajari OOP&status=pending&priority=high&energy_level=medium&tags=1,2&csrf_token=YOUR_CSRF_TOKEN"
```

### 2. Get Todo Detail
- URL: `/api/get.php`
- Method: GET (login)
- Query: `id` (wajib, integer)
- Response berhasil: `{ "success": true, "todo": { id, user_id, title, description, status, created_at } }`
- Response gagal: 404 jika tidak ditemukan.

Contoh:
```bash
curl "https://www.dwibudifitriadi.me/api/get.php?id=42"
```

### 3. Edit Todo
- URL: `/api/edit.php`
- Method: POST (login + CSRF)
- Body:
  - `id` (wajib)
  - Opsional: `status`, `title`, `description`, `priority`, `energy_level`
  - `tags` (opsional; array atau string ID, akan menimpa asosiasi tag yang ada)
  - `csrf_token`
- Jika tidak ada field yang diubah, akan mengembalikan `"Nothing to update"`.
- Response berhasil: `{ "success": true, "affected": 1 }`

Contoh:
```bash
curl -X POST "https://www.dwibudifitriadi.me/api/edit.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "id=42&status=in-progress&priority=high&csrf_token=YOUR_CSRF_TOKEN"
```

### 4. Delete Todo
- URL: `/api/delete.php`
- Method: POST (login + CSRF)
- Body: `id`, `csrf_token`
- Response berhasil: `{ "success": true, "affected": <jumlah_dihapus> }`

Contoh:
```bash
curl -X POST "https://www.dwibudifitriadi.me/api/delete.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "id=42&csrf_token=YOUR_CSRF_TOKEN"
```

### 5. Tags - List Semua Tag
- URL: `/api/tags.php`
- Method: GET (login)
- Response: array tag `[ { id, name, color } ]`

Contoh:
```bash
curl "https://www.dwibudifitriadi.me/api/tags.php"
```

### 6. Tags - Todos per Tag
- URL: `/api/tags.php?action=get_todos&tag_id=<id>`
- Method: GET (login)
- Response: array todo `[ { id, title, description, status, priority } ]`

Contoh:
```bash
curl "https://www.dwibudifitriadi.me/api/tags.php?action=get_todos&tag_id=1"
```

### 7. Tags - Create
- URL: `/api/tags.php`
- Method: POST (login + CSRF)
- Body: `action=create`, `name` (wajib), `color` (opsional, default `#6366f1`), `csrf_token`
- Response berhasil: `{ "success": true, "tag_id": <id>, "id": <id>, "name": "...", "color": "..." }`
- Error duplikat: `{ "success": false, "message": "Tag sudah ada" }`

Contoh:
```bash
curl -X POST "https://www.dwibudifitriadi.me/api/tags.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=create&name=Urgent&color=%23FF0000&csrf_token=YOUR_CSRF_TOKEN"
```

### 8. Tags - Update
- URL: `/api/tags.php`
- Method: POST (login + CSRF)
- Body: `action=update`, `id`, `name`, `color` (opsional), `csrf_token`
- Response berhasil: `{ "success": true, "message": "Tag updated" }`

Contoh:
```bash
curl -X POST "https://www.dwibudifitriadi.me/api/tags.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=update&id=3&name=Very%20Urgent&color=%23FF6600&csrf_token=YOUR_CSRF_TOKEN"
```

### 9. Tags - Delete
- URL: `/api/tags.php`
- Method: POST (login + CSRF)
- Body: `action=delete`, `id`, `csrf_token`
- Response berhasil: `{ "success": true, "message": "Tag deleted" }`

Contoh:
```bash
curl -X POST "https://www.dwibudifitriadi.me/api/tags.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=delete&id=3&csrf_token=YOUR_CSRF_TOKEN"
```

### 10. Todo-Tags - Tambah Tag ke Todo
- URL: `/api/todo-tags.php`
- Method: POST (login + CSRF)
- Body: `action=add`, `todo_id`, `tag_id`, `csrf_token`
- Response berhasil: `{ "success": true }`
- Jika duplikat: `{ "success": false, "message": "Tag sudah ditambahkan" }`

Contoh:
```bash
curl -X POST "https://www.dwibudifitriadi.me/api/todo-tags.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=add&todo_id=42&tag_id=1&csrf_token=YOUR_CSRF_TOKEN"
```

### 11. Todo-Tags - Hapus Tag dari Todo
- URL: `/api/todo-tags.php`
- Method: POST (login + CSRF)
- Body: `action=remove`, `todo_id`, `tag_id`, `csrf_token`
- Response berhasil: `{ "success": true }`

Contoh:
```bash
curl -X POST "https://www.dwibudifitriadi.me/api/todo-tags.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=remove&todo_id=42&tag_id=1&csrf_token=YOUR_CSRF_TOKEN"
```

### 12. Todo-Tags - Daftar Tag per Todo
- URL: `/api/todo-tags.php`
- Method: POST (login + CSRF)
- Body: `action=get`, `todo_id`, `csrf_token`
- Response: `{ "success": true, "tags": [ { id, name, color } ] }`

Contoh:
```bash
curl -X POST "https://www.dwibudifitriadi.me/api/todo-tags.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=get&todo_id=42&csrf_token=YOUR_CSRF_TOKEN"
```

### 13. Work Sessions - Mulai Sesi
- URL: `/api/work-sessions.php`
- Method: POST (login + CSRF)
- Body: `action=start`, `todo_id`, `duration` (menit, default 25), `csrf_token`
- Response berhasil: `{ "success": true, "session_id": <id> }`

Contoh:
```bash
curl -X POST "https://www.dwibudifitriadi.me/api/work-sessions.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=start&todo_id=42&duration=25&csrf_token=YOUR_CSRF_TOKEN"
```

### 14. Work Sessions - Selesaikan Sesi
- URL: `/api/work-sessions.php`
- Method: POST (login + CSRF)
- Body: `action=complete`, `session_id`, `todo_id`, `actual_duration` (detik, opsional), `csrf_token`
- Response berhasil: `{ "success": true }`

Contoh:
```bash
curl -X POST "https://www.dwibudifitriadi.me/api/work-sessions.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=complete&session_id=15&todo_id=42&actual_duration=1500&csrf_token=YOUR_CSRF_TOKEN"
```

### 15. Work Sessions - Statistik per Todo
- URL: `/api/work-sessions.php`
- Method: POST (login + CSRF)
- Body: `action=get_stats`, `todo_id`, `csrf_token`
- Response: `{ "success": true, "total_sessions": <int>, "total_minutes": <int> }`

Contoh:
```bash
curl -X POST "https://www.dwibudifitriadi.me/api/work-sessions.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=get_stats&todo_id=42&csrf_token=YOUR_CSRF_TOKEN"
```

### 16. Work Sessions - Statistik Semua Sesi User
- URL: `/api/work-sessions.php`
- Method: GET (login)
- Response: `{ "success": true, "total_sessions": <int>, "total_minutes": <int> }`

Contoh:
```bash
curl "https://www.dwibudifitriadi.me/api/work-sessions.php"
```

## Contoh JavaScript (jQuery)
```javascript
$.ajax({
  url: 'api/create.php',
  method: 'POST',
  data: {
    title: 'Belajar PHP',
    description: 'Pelajari OOP',
    status: 'pending',
    priority: 'high',
    energy_level: 'medium',
    tags: '1,2',
    csrf_token: csrfToken
  },
  success: function (res) {
    if (res.success) console.log('Todo ID:', res.id);
  }
});
```

## Sumber Kode
- Endpoint todo: [api/create.php](api/create.php), [api/edit.php](api/edit.php), [api/delete.php](api/delete.php), [api/get.php](api/get.php)
- Tag: [api/tags.php](api/tags.php), [api/todo-tags.php](api/todo-tags.php)
- Pomodoro: [api/work-sessions.php](api/work-sessions.php)
- Session & CSRF helper: [includes/session.php](includes/session.php)
