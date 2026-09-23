<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->id ? 'Edit' : 'Tambah' }} User — CCTV Kanjuruhan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0b0f1a; color: #e2e8f0; margin: 0; display: flex; align-items: flex-start; justify-content: center; min-height: 100vh; padding: 40px 24px; }
        .form-card { background: #111827; border: 1px solid rgba(255,255,255,.08); border-radius: 20px; padding: 36px 40px; width: 100%; max-width: 500px; }
        h2 { font-size: 20px; font-weight: 700; color: #f1f5f9; margin: 0 0 6px; }
        .subtitle { font-size: 13px; color: #64748b; margin: 0 0 32px; }
        .field { margin-bottom: 20px; }
        label { display: block; font-size: 12px; font-weight: 600; color: #94a3b8; margin-bottom: 7px; text-transform: uppercase; letter-spacing: .4px; }
        input, select { width: 100%; padding: 10px 14px; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); border-radius: 10px; color: #e2e8f0; font-size: 14px; outline: none; transition: border-color .15s; font-family: inherit; }
        input:focus, select:focus { border-color: #3b82f6; background: rgba(59,130,246,.05); }
        select option { background: #1e293b; }
        .error { font-size: 11px; color: #f87171; margin-top: 5px; }
        .hint  { font-size: 11px; color: #64748b; margin-top: 5px; }
        .form-actions { display: flex; gap: 12px; margin-top: 28px; }
        .btn { display: inline-flex; align-items: center; padding: 10px 20px; border-radius: 10px; font-size: 14px; font-weight: 600; border: none; cursor: pointer; text-decoration: none; transition: all .15s; }
        .btn-primary { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; }
        .btn-primary:hover { background: linear-gradient(135deg, #60a5fa, #3b82f6); transform: translateY(-1px); }
        .btn-cancel  { background: rgba(255,255,255,.05); color: #94a3b8; border: 1px solid rgba(255,255,255,.1); }
        .btn-cancel:hover { background: rgba(255,255,255,.08); }
    </style>
</head>
<body>
<div class="form-card">
    <h2>{{ $user->id ? 'Edit User' : 'Tambah User Baru' }}</h2>
    <p class="subtitle">{{ $user->id ? 'Perbarui data akun operator/admin' : 'Buat akun baru untuk operator atau admin' }}</p>

    <form method="POST" action="{{ $user->id ? route('admin.users.update', $user) : route('admin.users.store') }}">
        @csrf
        @if($user->id) @method('PUT') @endif

        <div class="field">
            <label>Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
            @error('name') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>Username</label>
            <input type="text" name="username" value="{{ old('username', $user->username) }}" required>
            @error('username') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
            @error('email') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>Role</label>
            <select name="role" required>
                <option value="operator" {{ old('role', $user->role) === 'operator' ? 'selected' : '' }}>Operator</option>
                <option value="admin"    {{ old('role', $user->role) === 'admin'    ? 'selected' : '' }}>Admin</option>
            </select>
            @error('role') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>Password {{ $user->id ? '(kosongkan jika tidak diubah)' : '' }}</label>
            <input type="password" name="password" {{ $user->id ? '' : 'required' }}>
            @error('password') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>Konfirmasi Password</label>
            <input type="password" name="password_confirmation">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">{{ $user->id ? 'Simpan Perubahan' : 'Buat Akun' }}</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-cancel">Batal</a>
        </div>
    </form>
</div>
</body>
</html>
