<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User — CCTV Kanjuruhan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #0b0f1a; color: #e2e8f0; margin: 0; }
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: 260px; background: linear-gradient(180deg, #111827 0%, #0f172a 100%); border-right: 1px solid rgba(255,255,255,.06); display: flex; flex-direction: column; z-index: 100; }
        .sidebar-brand { padding: 20px 24px 16px; border-bottom: 1px solid rgba(255,255,255,.06); }
        .sidebar-brand h1 { font-size: 14px; font-weight: 700; color: #60a5fa; margin: 0; letter-spacing: .5px; text-transform: uppercase; }
        .sidebar-brand p  { font-size: 11px; color: #64748b; margin: 4px 0 0; }
        .sidebar-nav { flex: 1; padding: 16px 12px; }
        .nav-section { font-size: 10px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 1px; padding: 12px 12px 6px; }
        .nav-link { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 8px; text-decoration: none; color: #94a3b8; font-size: 13px; font-weight: 500; margin-bottom: 2px; transition: all .15s; }
        .nav-link:hover, .nav-link.active { background: rgba(96,165,250,.12); color: #60a5fa; }
        .nav-link svg { width: 16px; height: 16px; flex-shrink: 0; }
        .sidebar-footer { padding: 16px 24px; border-top: 1px solid rgba(255,255,255,.06); font-size: 12px; color: #64748b; }
        .topbar { position: fixed; left: 260px; right: 0; top: 0; height: 60px; background: rgba(11,15,26,.95); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255,255,255,.06); display: flex; align-items: center; justify-content: space-between; padding: 0 24px; z-index: 99; }
        .topbar-title { font-size: 15px; font-weight: 600; color: #f1f5f9; }
        .main { margin-left: 260px; padding-top: 60px; min-height: 100vh; }
        .content { padding: 24px; }
        .card { background: #111827; border: 1px solid rgba(255,255,255,.06); border-radius: 16px; overflow: hidden; }
        .card-header { padding: 18px 24px; border-bottom: 1px solid rgba(255,255,255,.06); display: flex; align-items: center; justify-content: space-between; }
        .card-title { font-size: 15px; font-weight: 600; color: #f1f5f9; }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: all .15s; }
        .btn-primary { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; }
        .btn-primary:hover { background: linear-gradient(135deg, #60a5fa, #3b82f6); }
        .btn-danger  { background: rgba(239,68,68,.15); color: #f87171; border: 1px solid rgba(239,68,68,.2); }
        .btn-danger:hover  { background: rgba(239,68,68,.3); }
        .btn-edit    { background: rgba(96,165,250,.12); color: #60a5fa; border: 1px solid rgba(96,165,250,.2); }
        .btn-edit:hover    { background: rgba(96,165,250,.25); }
        .btn-sm { padding: 5px 10px; font-size: 11px; }
        .btn-logout { padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 500; background: rgba(239,68,68,.12); color: #f87171; border: 1px solid rgba(239,68,68,.2); text-decoration: none; transition: all .15s; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; }
        th { padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid rgba(255,255,255,.06); }
        td { padding: 12px 16px; font-size: 13px; color: #cbd5e1; border-bottom: 1px solid rgba(255,255,255,.04); }
        tr:hover td { background: rgba(255,255,255,.02); }
        .badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-admin    { background: rgba(168,85,247,.15); color: #c084fc; }
        .badge-operator { background: rgba(96,165,250,.15); color: #60a5fa; }
        .alert-success { background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.2); color: #4ade80; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; }
        .paginator { padding: 16px 24px; }
        .avatar { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #8b5cf6); display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; }
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-brand">
        <h1>CCTV Monitoring</h1>
        <p>Stadion Kanjuruhan</p>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">Monitoring</div>
        <a href="{{ route('dashboard') }}" class="nav-link">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
            Peta CCTV
        </a>
        <a href="{{ route('alerts.index') }}" class="nav-link">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            Log Alert
        </a>
        <div class="nav-section" style="margin-top:8px;">Administrasi</div>
        <a href="{{ route('admin.cameras.index') }}" class="nav-link">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82V15.18a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            Manajemen Kamera
        </a>
        <a href="{{ route('admin.users.index') }}" class="nav-link active">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            Manajemen User
        </a>
    </nav>
    <div class="sidebar-footer">
        <div style="font-weight:600;color:#94a3b8;">{{ auth()->user()->name }}</div>
        <div style="text-transform:capitalize;color:#60a5fa;font-size:11px;">{{ auth()->user()->role }}</div>
    </div>
</aside>
<header class="topbar">
    <div class="topbar-title">Manajemen User</div>
    <div>
        <form action="{{ route('logout') }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn-logout">Keluar</button>
        </form>
    </div>
</header>
<main class="main">
    <div class="content">
        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif
        <div class="card">
            <div class="card-header">
                <div class="card-title">Daftar User ({{ $users->total() }})</div>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ Tambah User</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div class="avatar">{{ strtoupper(substr($user->name,0,1)) }}</div>
                                <span style="font-weight:500;color:#e2e8f0;">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td style="font-family:monospace;font-size:13px;">{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge badge-{{ $user->role }}">{{ ucfirst($user->role) }}</span></td>
                        <td style="color:#64748b;font-size:12px;">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-edit btn-sm">Edit</a>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus user {{ $user->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" style="text-align:center;color:#475569;padding:40px;">Belum ada user.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="paginator">{{ $users->links() }}</div>
        </div>
    </div>
</main>
@livewireScripts
</body>
</html>
