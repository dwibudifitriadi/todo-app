# Todo List API Documentation

## Overview
Dokumentasi lengkap untuk semua endpoint API Todo List Application. API ini memerlukan autentikasi pengguna dan menggunakan CSRF token untuk operasi yang mengubah data.

---

## Authentication

Semua endpoint API memerlukan:
- **User Session**: Pengguna harus sudah login
- **CSRF Token**: Untuk request POST, PUT, DELETE

Jika autentikasi gagal:
```json
{
  "success": false,
  "message": "Unauthorized"
}
```
(HTTP Status: 401)

---

## Base URL
```
http://localhost/todo-list-app/api/
```

---

## Endpoints

### 1. CREATE TODO
**Membuat todo baru**

- **URL**: `/api/create.php`
- **Method**: `POST`
- **Auth Required**: Yes (Login)
- **CSRF Required**: Yes

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `title` | string | Yes | Judul todo (tidak boleh kosong) |
| `description` | string | No | Deskripsi/detail todo |
| `status` | string | No | Status todo: `pending`, `in-progress`, `completed`. Default: `pending` |
| `priority` | string | No | Prioritas: `low`, `medium`, `high` |
| `energy_level` | string | No | Level energi yang diperlukan: `low`, `medium`, `high` |
| `tags` | string | No | ID tag yang dipisahkan dengan koma (contoh: `1,2,3`) |
| `csrf_token` | string | Yes | CSRF token dari form |

#### Example Request
```bash
curl -X POST http://localhost/todo-list-app/api/create.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "title=Belajar PHP&description=Pelajari OOP&status=pending&priority=high&energy_level=medium&tags=1,2&csrf_token=YOUR_CSRF_TOKEN"
```

#### Success Response (201)
```json
{
  "success": true,
  "id": 42
}
```

#### Error Response (400)
```json
{
  "success": false,
  "message": "Title is required"
}
```

---

### 2. GET TODO
**Mengambil detail todo berdasarkan ID**

- **URL**: `/api/get.php`
- **Method**: `GET`
- **Auth Required**: Yes (Login)
- **CSRF Required**: No

#### Query Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | ID todo yang ingin diambil |

#### Example Request
```bash
curl http://localhost/todo-list-app/api/get.php?id=42
```

#### Success Response (200)
```json
{
  "success": true,
  "todo": {
    "id": 42,
    "user_id": 1,
    "title": "Belajar PHP",
    "description": "Pelajari OOP",
    "status": "pending",
    "created_at": "2026-01-01 10:30:00"
  }
}
```

#### Error Response (404)
```json
{
  "success": false,
  "message": "Not found"
}
```

---

### 3. EDIT TODO
**Mengupdate todo (status, title, description, priority, energy_level, atau tags)**

- **URL**: `/api/edit.php`
- **Method**: `POST`
- **Auth Required**: Yes (Login)
- **CSRF Required**: Yes

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | ID todo yang akan diupdate |
| `title` | string | No | Judul todo baru |
| `description` | string | No | Deskripsi baru |
| `status` | string | No | Status baru: `pending`, `in-progress`, `completed` |
| `priority` | string | No | Prioritas baru: `low`, `medium`, `high` |
| `energy_level` | string | No | Level energi baru: `low`, `medium`, `high` |
| `tags` | array/string | No | ID tag yang dipisahkan dengan koma |
| `csrf_token` | string | Yes | CSRF token dari form |

#### Example Request
```bash
curl -X POST http://localhost/todo-list-app/api/edit.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "id=42&status=in-progress&priority=high&csrf_token=YOUR_CSRF_TOKEN"
```

#### Success Response (200)
```json
{
  "success": true,
  "affected": 1
}
```

#### Error Response (400)
```json
{
  "success": false,
  "message": "Nothing to update"
}
```

---

### 4. DELETE TODO
**Menghapus todo**

- **URL**: `/api/delete.php`
- **Method**: `POST`
- **Auth Required**: Yes (Login)
- **CSRF Required**: Yes

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | ID todo yang akan dihapus |
| `csrf_token` | string | Yes | CSRF token dari form |

#### Example Request
```bash
curl -X POST http://localhost/todo-list-app/api/delete.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "id=42&csrf_token=YOUR_CSRF_TOKEN"
```

#### Success Response (200)
```json
{
  "success": true,
  "affected": 1
}
```

#### Error Response (400)
```json
{
  "success": false,
  "message": "Invalid id"
}
```

---

### 5. TAGS - Get All Tags
**Mengambil semua tag milik user**

- **URL**: `/api/tags.php`
- **Method**: `GET`
- **Auth Required**: Yes (Login)
- **CSRF Required**: No

#### Example Request
```bash
curl http://localhost/todo-list-app/api/tags.php
```

#### Success Response (200)
```json
[
  {
    "id": 1,
    "name": "Work",
    "color": "#FF5733"
  },
  {
    "id": 2,
    "name": "Personal",
    "color": "#33FF57"
  }
]
```

---

### 6. TAGS - Get Todos for a Tag
**Mengambil semua todo yang memiliki tag tertentu**

- **URL**: `/api/tags.php`
- **Method**: `GET`
- **Auth Required**: Yes (Login)
- **CSRF Required**: No

#### Query Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | Harus berisi `get_todos` |
| `tag_id` | integer | Yes | ID tag |

#### Example Request
```bash
curl "http://localhost/todo-list-app/api/tags.php?action=get_todos&tag_id=1"
```

#### Success Response (200)
```json
[
  {
    "id": 42,
    "title": "Belajar PHP",
    "description": "Pelajari OOP",
    "status": "pending",
    "priority": "high"
  }
]
```

---

### 7. TAGS - Create Tag
**Membuat tag baru**

- **URL**: `/api/tags.php`
- **Method**: `POST`
- **Auth Required**: Yes (Login)
- **CSRF Required**: Yes

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | Harus berisi `create` |
| `name` | string | Yes | Nama tag (tidak boleh duplikat) |
| `color` | string | No | Warna hex (default: `#6366f1`) |
| `csrf_token` | string | Yes | CSRF token dari form |

#### Example Request
```bash
curl -X POST http://localhost/todo-list-app/api/tags.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=create&name=Urgent&color=%23FF0000&csrf_token=YOUR_CSRF_TOKEN"
```

#### Success Response (201)
```json
{
  "success": true,
  "tag_id": 3,
  "id": 3,
  "name": "Urgent",
  "color": "#FF0000"
}
```

#### Error Response (400)
```json
{
  "success": false,
  "message": "Tag sudah ada"
}
```

---

### 8. TAGS - Update Tag
**Mengupdate tag (nama atau warna)**

- **URL**: `/api/tags.php`
- **Method**: `POST`
- **Auth Required**: Yes (Login)
- **CSRF Required**: Yes

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | Harus berisi `update` |
| `id` | integer | Yes | ID tag yang akan diupdate |
| `name` | string | Yes | Nama tag baru |
| `color` | string | No | Warna hex baru (default: `#6366f1`) |
| `csrf_token` | string | Yes | CSRF token dari form |

#### Example Request
```bash
curl -X POST http://localhost/todo-list-app/api/tags.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=update&id=3&name=Very Urgent&color=%23FF6600&csrf_token=YOUR_CSRF_TOKEN"
```

#### Success Response (200)
```json
{
  "success": true,
  "affected": 1
}
```

---

### 9. TAGS - Delete Tag
**Menghapus tag**

- **URL**: `/api/tags.php`
- **Method**: `POST`
- **Auth Required**: Yes (Login)
- **CSRF Required**: Yes

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | Harus berisi `delete` |
| `id` | integer | Yes | ID tag yang akan dihapus |
| `csrf_token` | string | Yes | CSRF token dari form |

#### Example Request
```bash
curl -X POST http://localhost/todo-list-app/api/tags.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=delete&id=3&csrf_token=YOUR_CSRF_TOKEN"
```

#### Success Response (200)
```json
{
  "success": true,
  "affected": 1
}
```

---

### 10. TODO-TAGS - Add Tag to Todo
**Menambahkan tag ke todo**

- **URL**: `/api/todo-tags.php`
- **Method**: `POST`
- **Auth Required**: Yes (Login)
- **CSRF Required**: Yes

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | Harus berisi `add` |
| `todo_id` | integer | Yes | ID todo |
| `tag_id` | integer | Yes | ID tag yang akan ditambahkan |
| `csrf_token` | string | Yes | CSRF token dari form |

#### Example Request
```bash
curl -X POST http://localhost/todo-list-app/api/todo-tags.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=add&todo_id=42&tag_id=1&csrf_token=YOUR_CSRF_TOKEN"
```

#### Success Response (200)
```json
{
  "success": true
}
```

---

### 11. TODO-TAGS - Remove Tag from Todo
**Menghapus tag dari todo**

- **URL**: `/api/todo-tags.php`
- **Method**: `POST`
- **Auth Required**: Yes (Login)
- **CSRF Required**: Yes

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | Harus berisi `remove` |
| `todo_id` | integer | Yes | ID todo |
| `tag_id` | integer | Yes | ID tag yang akan dihapus |
| `csrf_token` | string | Yes | CSRF token dari form |

#### Example Request
```bash
curl -X POST http://localhost/todo-list-app/api/todo-tags.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=remove&todo_id=42&tag_id=1&csrf_token=YOUR_CSRF_TOKEN"
```

#### Success Response (200)
```json
{
  "success": true
}
```

---

### 12. TODO-TAGS - Get Tags for Todo
**Mengambil semua tag yang dimiliki oleh todo**

- **URL**: `/api/todo-tags.php`
- **Method**: `POST`
- **Auth Required**: Yes (Login)
- **CSRF Required**: Yes

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | Harus berisi `get` |
| `todo_id` | integer | Yes | ID todo |
| `csrf_token` | string | Yes | CSRF token dari form |

#### Example Request
```bash
curl -X POST http://localhost/todo-list-app/api/todo-tags.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=get&todo_id=42&csrf_token=YOUR_CSRF_TOKEN"
```

#### Success Response (200)
```json
{
  "success": true,
  "tags": [
    {
      "id": 1,
      "name": "Work",
      "color": "#FF5733"
    }
  ]
}
```

---

### 13. WORK-SESSIONS - Start Work Session
**Memulai sesi kerja baru (Pomodoro tracking)**

- **URL**: `/api/work-sessions.php`
- **Method**: `POST`
- **Auth Required**: Yes (Login)
- **CSRF Required**: Yes

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | Harus berisi `start` |
| `todo_id` | integer | Yes | ID todo yang dikerjakan |
| `duration` | integer | No | Durasi sesi dalam menit (default: 25) |
| `csrf_token` | string | Yes | CSRF token dari form |

#### Example Request
```bash
curl -X POST http://localhost/todo-list-app/api/work-sessions.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=start&todo_id=42&duration=25&csrf_token=YOUR_CSRF_TOKEN"
```

#### Success Response (201)
```json
{
  "success": true,
  "session_id": 15
}
```

---

### 14. WORK-SESSIONS - Complete Work Session
**Menyelesaikan sesi kerja**

- **URL**: `/api/work-sessions.php`
- **Method**: `POST`
- **Auth Required**: Yes (Login)
- **CSRF Required**: Yes

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | Harus berisi `complete` |
| `session_id` | integer | Yes | ID sesi yang akan diselesaikan |
| `todo_id` | integer | Yes | ID todo |
| `actual_duration` | integer | No | Durasi aktual dalam detik |
| `csrf_token` | string | Yes | CSRF token dari form |

#### Example Request
```bash
curl -X POST http://localhost/todo-list-app/api/work-sessions.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=complete&session_id=15&todo_id=42&actual_duration=1500&csrf_token=YOUR_CSRF_TOKEN"
```

#### Success Response (200)
```json
{
  "success": true
}
```

---

### 15. WORK-SESSIONS - Get Stats (Per Todo)
**Mengambil statistik sesi kerja untuk todo tertentu**

- **URL**: `/api/work-sessions.php`
- **Method**: `POST`
- **Auth Required**: Yes (Login)
- **CSRF Required**: Yes

#### Request Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `action` | string | Yes | Harus berisi `get_stats` |
| `todo_id` | integer | Yes | ID todo |
| `csrf_token` | string | Yes | CSRF token dari form |

#### Example Request
```bash
curl -X POST http://localhost/todo-list-app/api/work-sessions.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "action=get_stats&todo_id=42&csrf_token=YOUR_CSRF_TOKEN"
```

#### Success Response (200)
```json
{
  "success": true,
  "total_sessions": 5,
  "total_minutes": 125
}
```

---

### 16. WORK-SESSIONS - Get Overall Stats
**Mengambil statistik sesi kerja untuk semua todo user**

- **URL**: `/api/work-sessions.php`
- **Method**: `GET`
- **Auth Required**: Yes (Login)
- **CSRF Required**: No

#### Example Request
```bash
curl http://localhost/todo-list-app/api/work-sessions.php
```

#### Success Response (200)
```json
{
  "success": true,
  "total_sessions": 15,
  "total_minutes": 375
}
```

---

## HTTP Status Codes

| Code | Description |
|------|-------------|
| `200` | OK - Request berhasil |
| `201` | Created - Resource berhasil dibuat |
| `400` | Bad Request - Parameter tidak valid |
| `401` | Unauthorized - User tidak login |
| `403` | Forbidden - User tidak memiliki akses atau CSRF token tidak valid |
| `404` | Not Found - Resource tidak ditemukan |
| `500` | Internal Server Error - Error di server |

---

## Error Handling

Semua response error menggunakan format JSON:
```json
{
  "success": false,
  "message": "Deskripsi error"
}
```

Selalu periksa field `success` untuk mengetahui apakah request berhasil atau tidak.

---

## Security Notes

1. **CSRF Protection**: Semua request POST memerlukan valid CSRF token
2. **User Isolation**: Setiap user hanya bisa mengakses data miliknya sendiri
3. **Session Required**: User harus terautentikasi untuk akses API
4. **Input Validation**: Semua input divalidasi di server

---

## Examples - JavaScript/jQuery

### Create Todo
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
  success: function(response) {
    if (response.success) {
      console.log('Todo created with ID:', response.id);
    }
  }
});
```

### Get Todo
```javascript
$.ajax({
  url: 'api/get.php',
  method: 'GET',
  data: { id: 42 },
  success: function(response) {
    if (response.success) {
      console.log('Todo:', response.todo);
    }
  }
});
```

### Update Todo Status
```javascript
$.ajax({
  url: 'api/edit.php',
  method: 'POST',
  data: {
    id: 42,
    status: 'completed',
    csrf_token: csrfToken
  },
  success: function(response) {
    if (response.success) {
      console.log('Todo updated');
    }
  }
});
```

### Start Work Session
```javascript
$.ajax({
  url: 'api/work-sessions.php',
  method: 'POST',
  data: {
    action: 'start',
    todo_id: 42,
    duration: 25,
    csrf_token: csrfToken
  },
  success: function(response) {
    if (response.success) {
      console.log('Session started with ID:', response.session_id);
    }
  }
});
```

---

## Contact & Support

Untuk pertanyaan atau masalah terkait API, silakan hubungi tim development.

---

**Last Updated**: January 1, 2026
**API Version**: 1.0
