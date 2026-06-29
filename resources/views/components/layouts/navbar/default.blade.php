@php
    use App\Models\agentsdetailsModel;
    $usercheck       = Auth::user();
    $isAdmin         = in_array($usercheck->role, ['admin', 'superadmin']);
    $agent           = agentsdetailsModel::where('uid', $usercheck->id)->first();
    $creditleft      = null;
    if (in_array($usercheck->role, ['agent', 'subagent']) && $agent) {
        $creditleft = $usercheck->role === 'agent'
            ? $agent->noallocated - $agent->noused
            : $agent->subcreditassigned - $agent->subcreditused;
    }
    $pendingCount   = $isAdmin ? \App\Models\User::where('account_status', 'pending')->count() : 0;
    $unreadBell     = $usercheck->role === 'broker'
        ? $usercheck->unreadNotifications()->count()
        : 0;
@endphp

<nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
     id="layout-navbar">

    <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">

        <ul class="navbar-nav flex-row align-items-center ms-md-auto">

            {{-- Credit balance (agents/subagents only) --}}
            @if ($creditleft !== null)
                <li class="nav-item lh-1 me-4">
                    <span class="fw-semibold">{{ $creditleft }} Credits</span>
                </li>
                <li class="nav-item lh-1 me-4">
                    {{ $usercheck->name }} | <b>{{ strtoupper($usercheck->role) }}</b>
                </li>
            @endif

            {{-- Pending accounts badge (admin shortcut) --}}
            @if ($isAdmin && $pendingCount > 0)
                <li class="nav-item me-3">
                    <a href="{{ route('admin.pending_accounts') }}" wire:navigate
                       class="btn btn-sm btn-warning text-dark fw-semibold">
                        <i class="bx bx-user-check me-1"></i>{{ $pendingCount }} Pending
                    </a>
                </li>
            @endif

            {{-- Broker notification bell --}}
            @if ($usercheck->role === 'broker')
                <li class="nav-item me-3">
                    <a href="{{ route('broker.tickets') }}" class="nav-link position-relative p-1" wire:navigate
                       title="{{ $unreadBell }} unread notification(s)">
                        <i class="bx bx-bell" style="font-size:1.4rem;"></i>
                        @if ($unreadBell > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                  style="font-size:.6rem;margin-top:4px;margin-left:-10px;">
                                {{ $unreadBell > 99 ? '99+' : $unreadBell }}
                            </span>
                        @endif
                    </a>
                </li>
            @endif

            {{-- User avatar dropdown --}}
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow p-0"
                   href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ $usercheck->profile_photo_url ?? asset('assets/img/avatars/2.png') }}"
                             alt class="w-px-40 h-auto rounded-circle">
                    </div>
                </a>

                <ul class="dropdown-menu dropdown-menu-end">
                    {{-- Profile header --}}
                    <li>
                        <a class="dropdown-item" href="{{ route('settings.profile') }}" wire:navigate>
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="{{ $usercheck->profile_photo_url ?? asset('assets/img/avatars/2.png') }}"
                                             alt class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $usercheck->name }}</h6>
                                    <small class="text-body-secondary">{{ ucfirst($usercheck->role) }}</small>
                                </div>
                            </div>
                        </a>
                    </li>

                    <li><div class="dropdown-divider my-1"></div></li>

                    {{-- Navigation shortcuts --}}
                    <li>
                        <a class="dropdown-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                           href="{{ route('dashboard') }}" wire:navigate>
                            <i class="menu-icon fa fa-tachometer me-2"></i>{{ __('Dashboard') }}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ request()->is('list_policy') ? 'active' : '' }}"
                           href="{{ route('list_policy') }}" wire:navigate>
                            <i class="menu-icon fa fa-pencil me-2"></i>{{ __('Policy Mgmt') }}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ request()->routeIs('claim.notify.*') ? 'active' : '' }}"
                           href="{{ route('claim.notify.lookup') }}" wire:navigate>
                            <i class="menu-icon bx bx-notepad me-2"></i>{{ __('Submit Claim Notification') }}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ request()->routeIs('claim.notifications') ? 'active' : '' }}"
                           href="{{ route('claim.notifications') }}" wire:navigate>
                            <i class="menu-icon bx bx-list-ul me-2"></i>{{ __('My Notifications') }}
                        </a>
                    </li>

                    @if ($usercheck->getAgentDetails()?->canregistersubagent)
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('list_sub_agents') ? 'active' : '' }}"
                               href="{{ route('list_sub_agents') }}" wire:navigate>
                                <i class="menu-icon fa fa-users me-2"></i>{{ __('List of Sub Agents') }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('list_policy_subagents') ? 'active' : '' }}"
                               href="{{ route('list_policy_subagents') }}" wire:navigate>
                                <i class="menu-icon fa fa-check me-2"></i>{{ __('Sub Agents Policies') }}
                            </a>
                        </li>
                    @endif

                    @if ($isAdmin)
                        <li>
                            <a class="dropdown-item {{ request()->is('ecmr_index') ? 'active' : '' }}"
                               href="{{ route('ecmrs.index') }}" wire:navigate>
                                <i class="menu-icon fa fa-id-card-o me-2"></i>{{ __('Police eCMR') }}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('admin.pending_accounts') ? 'active' : '' }}"
                               href="{{ route('admin.pending_accounts') }}" wire:navigate>
                                <i class="menu-icon bx bx-user-check me-2"></i>{{ __('Pending Accounts') }}
                                @if ($pendingCount > 0)
                                    <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">{{ $pendingCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('list_users') ? 'active' : '' }}"
                               href="{{ route('list_users') }}" wire:navigate>
                                <i class="menu-icon fa fa-user me-2"></i>{{ __('User Mgmt') }}
                            </a>
                        </li>
                    @endif

                    @if ($usercheck->role === 'superadmin')
                        <li>
                            <a class="dropdown-item {{ request()->is('list_agents') ? 'active' : '' }}"
                               href="{{ route('list_agents') }}" wire:navigate>
                                <i class="menu-icon fa fa-address-card me-2"></i>{{ __('Agent Mgmt') }}
                            </a>
                        </li>
                    @endif

                    <li><div class="dropdown-divider my-1"></div></li>

                    <li>
                        <a class="dropdown-item {{ request()->routeIs('settings.profile') ? 'active' : '' }}"
                           href="{{ route('settings.profile') }}" wire:navigate>
                            <i class="icon-base bx bx-user icon-md me-3"></i>{{ __('My Profile') }}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ request()->routeIs('settings.password') ? 'active' : '' }}"
                           href="{{ route('settings.password') }}" wire:navigate>
                            <i class="icon-base bx bx-cog icon-md me-3"></i>{{ __('Settings') }}
                        </a>
                    </li>

                    <li><div class="dropdown-divider my-1"></div></li>

                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item" type="submit">
                                <i class="icon-base bx bx-power-off icon-md me-3"></i>{{ __('Log Out') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </li>

        </ul>
    </div>
</nav>
