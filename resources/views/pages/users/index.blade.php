@extends('layouts.app')

@push('styles')
@endpush

@section('content')
<div class="page-hdr">
    <h2>{{ __('Users & Roles') }}</h2>
</div>

<div class="split">
    <div>
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                <h3>{{ __('Users') }}</h3>
                <button class="btn btn-sm btn-pr" onclick="openUserModal()">➕ {{ __('Add User') }}</button>
            </div>
            <div class="table-wrap">
                <table>
                    <tr>
                        <th>{{ __('Username') }}</th>
                        <th>{{ __('Display Name') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                    @forelse($users as $u)
                        <tr>
                            <td>{{ $u->username }}</td>
                            <td>{{ $u->displayName ?? $u->name }}</td>
                            <td><span class="badge badge-pr">{{ $u->role->name ?? '—' }}</span></td>
                            <td class="no-sort">
                                <div class="action-btns">
                                    <button class="btn btn-sm btn-soft-primary rounded-circle" style="width:30px;height:30px;" onclick='openUserModal(@json($u))' title="{{ __('Edit User') }}">✏️</button>
                                    @if($u->id !== 1 && $u->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $u->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-soft-danger rounded-circle" style="width:30px;height:30px;" title="{{ __('Delete User') }}">🗑️</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty-state">{{ __('No data yet') }}</td></tr>
                    @endforelse
                </table>
            </div>
        </div>
        
        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                <h3>{{ __('Roles') }}</h3>
                <button class="btn btn-sm btn-pr" onclick="openRoleModal()">➕ {{ __('Add Role') }}</button>
            </div>
            <div class="table-wrap">
                <table>
                    <tr>
                        <th>{{ __('Role Name') }}</th>
                        <th>{{ __('Permissions') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                    @forelse($roles as $r)
                        <tr>
                            <td><strong>{{ $r->name }}</strong></td>
                            <td style="font-size:11px">
                                @php
                                    $perms = is_string($r->permissions) ? json_decode($r->permissions, true) : $r->permissions;
                                    echo implode(', ', array_map('__', $perms ?? []));
                                @endphp
                            </td>
                            <td class="no-sort">
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-soft-primary rounded-circle" style="width:30px;height:30px;" onclick='openRoleModal(@json($r))' title="{{ __('Edit Role') }}">✏️</button>
                                    @if($r->id !== 1)
                                        <form action="{{ route('roles.destroy', $r->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-soft-danger rounded-circle" style="width:30px;height:30px;" title="{{ __('Delete Role') }}">🗑️</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty-state">{{ __('No data yet') }}</td></tr>
                    @endforelse
                </table>
            </div>
        </div>
    </div>
    
    <div class="card">
        <h3 style="margin-bottom:12px">{{ __('Audit Trail') }}</h3>
        <div class="table-wrap" style="max-height:500px;overflow-y:auto">
            <table>
                <tr>
                    <th>{{ __('Action') }}</th>
                    <th>{{ __('By User ID') }}</th>
                    <th>{{ __('At') }}</th>
                </tr>
                @forelse($audit as $a)
                    <tr>
                        <td>{{ $a->action }}</td>
                        <td>{{ $a->user_id }}</td>
                        <td>{{ \Carbon\Carbon::parse($a->created_at)->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="empty-state">{{ __('No data yet') }}</td></tr>
                @endforelse
            </table>
        </div>
    </div>
</div>

<!-- Modal Container -->
<div class="modal-overlay" id="modal-overlay" onclick="if(event.target===this)closeModal()">
  <div class="modal" id="modal-box" style="background:var(--bg2); padding: 20px; border-radius: var(--radius); width: 100%; max-width: 500px; display: inline-block;"></div>
</div>
@endsection

@push('scripts')
<script>
    function openUserModal(user = null) {
        const isEdit = user !== null;
        const actionUrl = isEdit ? `{{ url('users') }}/${user.id}` : `{{ route('users.store') }}`;
        const methodField = isEdit ? `@method('PUT')` : '';
        
        let u = isEdit ? user : { username: '', name: '', email: '', role_id: '' };

        const html = `
            <h3>${isEdit ? '{{ __('Edit User') }}' : '{{ __('Add User') }}'}</h3>
            <form action="${actionUrl}" method="POST">
                @csrf
                ${methodField}
                <div style="margin-bottom: 12px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Username') }}</label>
                    <input name="username" value="${u.username || ''}" required>
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Display Name') }}</label>
                    <input name="name" value="${u.name || (u.displayName || '')}" required>
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Email (optional)') }}</label>
                    <input name="email" type="email" value="${u.email || ''}">
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Password') }} ${isEdit ? '({{ __('Keep blank to stay same') }})' : ''}</label>
                    <input name="password" type="password" ${isEdit ? '' : 'required'}>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Role') }}</label>
                    <select name="role_id" required style="width:100%; padding:8px; border:1px solid var(--border); border-radius:var(--radius);">
                        <option value="">-- {{ __('Select Role') }} --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" ${u.role_id == {{ $role->id }} ? 'selected' : ''}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex; gap:8px;">
                    <button type="button" class="btn btn-o" onclick="closeModal()">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-pr" style="flex:1;">${isEdit ? '{{ __('Update') }}' : '{{ __('Save') }}'}</button>
                </div>
            </form>
        `;
        document.getElementById('modal-box').innerHTML = html;
        document.getElementById('modal-overlay').classList.add('active');
        document.getElementById('modal-overlay').style.display = 'flex';
    }

    function openRoleModal(role = null) {
        const isEdit = role !== null;
        const actionUrl = isEdit ? `{{ url('roles') }}/${role.id}` : `{{ route('roles.store') }}`;
        const methodField = isEdit ? `@method('PUT')` : '';
        
        let r = isEdit ? role : { name: '', permissions: [] };
        const allPerms = ['dashboard','pos','inventory','suppliers','customers','offers','returns','finance','reports','warranty','transfers','settings','users'];

        let permsHtml = '';
        allPerms.forEach(p => {
            const checked = r.permissions && r.permissions.includes(p) ? 'checked' : '';
            permsHtml += `
                <label style="display:flex;align-items:center;gap:6px;font-size:12px;margin-bottom:6px;width:50%;">
                    <input type="checkbox" name="permissions[]" value="${p}" ${checked}> ${p.charAt(0).toUpperCase() + p.slice(1)}
                </label>
            `;
        });

        const html = `
            <h3>${isEdit ? '{{ __('Edit Role') }}' : '{{ __('Add Role') }}'}</h3>
            <form action="${actionUrl}" method="POST">
                @csrf
                ${methodField}
                <div style="margin-bottom: 12px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Role Name') }}</label>
                    <input name="name" value="${r.name || ''}" required>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:var(--tx2);margin-bottom:4px;">{{ __('Permissions') }}</label>
                    <div style="display:flex; flex-wrap:wrap;">
                        ${permsHtml}
                    </div>
                </div>
                <div style="display:flex; gap:8px;">
                    <button type="button" class="btn btn-o" onclick="closeModal()">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-pr" style="flex:1;">${isEdit ? '{{ __('Update') }}' : '{{ __('Save') }}'}</button>
                </div>
            </form>
        `;
        document.getElementById('modal-box').innerHTML = html;
        document.getElementById('modal-overlay').classList.add('active');
        document.getElementById('modal-overlay').style.display = 'flex';
    }

    function closeModal() { 
        document.getElementById('modal-overlay').classList.remove('active'); 
        document.getElementById('modal-overlay').style.display = 'none';
    }
</script>
@endpush
