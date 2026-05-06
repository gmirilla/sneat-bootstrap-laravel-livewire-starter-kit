<x-layouts.app>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-header"><strong>User Management</strong></div>
        <div class="card-body pb-2">
            <form action="{{ route('list_users') }}" method="get" class="row g-2 align-items-end">

                <div class="col-auto flex-grow-1">
                    <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Name or email…"
                           value="{{ $searchParams['search'] ?? '' }}">
                </div>

                <div class="col-auto">
                    <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Role</label>
                    <select name="role" class="form-select form-select-sm">
                        <option value="">All Roles</option>
                        @foreach (['superadmin' => 'Super Admin', 'admin' => 'Admin', 'agent' => 'Agent', 'subagent' => 'Sub Agent', 'user' => 'Direct User'] as $val => $label)
                            <option value="{{ $val }}" @selected(($searchParams['role'] ?? '') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Filter
                    </button>
                    <a href="{{ route('list_users') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                </div>

            </form>

            {{-- Active filter chips --}}
            @if (!empty(array_filter($searchParams)))
                <div class="mt-2 d-flex flex-wrap gap-1">
                    @if (!empty($searchParams['search']))
                        <span class="badge bg-secondary">"{{ $searchParams['search'] }}"</span>
                    @endif
                    @if (!empty($searchParams['role']))
                        <span class="badge bg-secondary">Role: {{ ucfirst($searchParams['role']) }}</span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-sm table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th style="width:4%">#</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th style="width:28%">Role</th>
                        <th style="width:8%"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <form action="{{ route('update_user') }}" method="post">
                                @csrf
                                <input type="hidden" name="uid" value="{{ $user->id }}">
                                <td class="text-muted small">{{ $user->id }}</td>
                                <td>{{ $user->firstname }}</td>
                                <td>{{ $user->lastname }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge
                                            @switch($user->role)
                                                @case('superadmin') bg-danger @break
                                                @case('admin')      bg-warning text-dark @break
                                                @case('agent')      bg-primary @break
                                                @case('subagent')   bg-info text-dark @break
                                                @default            bg-secondary
                                            @endswitch
                                        ">{{ $user->role }}</span>
                                        <select name="role" class="form-select form-select-sm" style="width:auto">
                                            <option value="superadmin" @selected($user->role === 'superadmin')>Super Admin</option>
                                            <option value="admin"      @selected($user->role === 'admin')>Admin</option>
                                            <option value="agent"      @selected($user->role === 'agent')>Agent</option>
                                            <option value="subagent"   @selected($user->role === 'subagent')>Sub Agent</option>
                                            <option value="user"       @selected($user->role === 'user')>Direct User</option>
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                </td>
                            </form>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</x-layouts.app>
