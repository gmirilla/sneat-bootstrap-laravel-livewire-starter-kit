@php
    $user         = Auth::user();
    $isAdmin      = in_array($user->role, ['admin', 'superadmin']);
    $pendingCount = $isAdmin ? \App\Models\User::where('account_status', 'pending')->count() : 0;
@endphp

<nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center"
     id="layout-navbar"
     style="background:#161616; font-weight:bold;">

    {{-- Logo --}}
    <a href="{{ url('/') }}" class="navbar-brand app-brand-link me-4">
        <x-app-logo />
    </a>

    {{-- Mobile toggler --}}
    <button class="navbar-toggler border-0" type="button"
            data-bs-toggle="collapse" data-bs-target="#horizontalMenuCollapse"
            aria-controls="horizontalMenuCollapse" aria-expanded="false"
            aria-label="Toggle navigation">
        <i class="bx bx-menu" style="color:#B18752;font-size:1.4rem"></i>
    </button>

    <div class="collapse navbar-collapse" id="horizontalMenuCollapse">

        {{-- Left-side nav items --}}
        <ul class="navbar-nav me-auto align-items-xl-center gap-xl-1">

            {{-- Dashboard --}}
            <li class="nav-item">
                <a class="nav-link{{ request()->is('dashboard') ? ' active' : '' }}"
                   href="{{ route('dashboard') }}" wire:navigate>
                    <i class="fa fa-tachometer me-1"></i>{{ __('Dashboard') }}
                </a>
            </li>

            {{-- Subagent Mgmt (agents with canregistersubagent only) --}}
            @if ($user->getAgentDetails()?->canregistersubagent)
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle{{ request()->routeIs('list_sub_agents', 'list_policy_subagents') ? ' active' : '' }}"
                   href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-users me-1"></i>{{ __('Subagent Mgmt') }}
                </a>
                <ul class="dropdown-menu" style="background:#1e1e1e">
                    <li>
                        <a class="dropdown-item{{ request()->routeIs('list_sub_agents') ? ' active' : '' }}"
                           href="{{ route('list_sub_agents') }}" wire:navigate>
                            {{ __('List of Sub Agents') }}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item{{ request()->routeIs('list_policy_subagents') ? ' active' : '' }}"
                           href="{{ route('list_policy_subagents') }}" wire:navigate>
                            {{ __('Sub Agent Policies') }}
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            {{-- Motor Policy Mgmt --}}
            <li class="nav-item">
                <a class="nav-link{{ request()->is('list_policy') ? ' active' : '' }}"
                   href="{{ route('list_policy') }}" wire:navigate>
                    <i class="fa fa-pencil me-1"></i>{{ __('Motor Policy Mgmt') }}
                </a>
            </li>

            {{-- Claim Notification (all roles) --}}
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle{{ request()->routeIs('claim.notify.*') || request()->routeIs('claim.notifications') ? ' active' : '' }}"
                   href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-notepad me-1"></i>{{ __('Claim Notification') }}
                </a>
                <ul class="dropdown-menu" style="background:#1e1e1e">
                    <li>
                        <a class="dropdown-item{{ request()->routeIs('claim.notify.*') ? ' active' : '' }}"
                           href="{{ route('claim.notify.lookup') }}" wire:navigate>
                            {{ __('Submit Notification') }}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item{{ request()->routeIs('claim.notifications') ? ' active' : '' }}"
                           href="{{ route('claim.notifications') }}" wire:navigate>
                            {{ __('My Notifications') }}
                        </a>
                    </li>
                </ul>
            </li>

            @if ($isAdmin)

            {{-- Police eCMR --}}
            <li class="nav-item">
                <a class="nav-link{{ request()->is('ecmr_index') ? ' active' : '' }}"
                   href="{{ route('ecmrs.index') }}" wire:navigate>
                    <i class="fa fa-id-card-o me-1"></i>{{ __('Police eCMR') }}
                </a>
            </li>

            {{-- Claims Admin --}}
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle{{ request()->routeIs('admin.pending_accounts') || request()->routeIs('claim.notifications') ? ' active' : '' }}"
                   href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-briefcase me-1"></i>{{ __('Claims Admin') }}
                    @if ($pendingCount > 0)
                        <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">{{ $pendingCount }}</span>
                    @endif
                </a>
                <ul class="dropdown-menu" style="background:#1e1e1e">
                    <li>
                        <a class="dropdown-item{{ request()->routeIs('claim.notifications') ? ' active' : '' }}"
                           href="{{ route('claim.notifications') }}" wire:navigate>
                            {{ __('All Notifications') }}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item{{ request()->routeIs('admin.pending_accounts') ? ' active' : '' }}"
                           href="{{ route('admin.pending_accounts') }}" wire:navigate>
                            {{ __('Pending Accounts') }}
                            @if ($pendingCount > 0)
                                <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">{{ $pendingCount }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </li>

            {{-- User Mgmt --}}
            <li class="nav-item">
                <a class="nav-link{{ request()->routeIs('list_users') ? ' active' : '' }}"
                   href="{{ route('list_users') }}" wire:navigate>
                    <i class="fa fa-user me-1"></i>{{ __('User Mgmt') }}
                </a>
            </li>

            @endif

            @if ($user->role === 'superadmin')

            {{-- Agent Mgmt --}}
            <li class="nav-item">
                <a class="nav-link{{ request()->is('list_agents') ? ' active' : '' }}"
                   href="{{ route('list_agents') }}" wire:navigate>
                    <i class="fa fa-address-card me-1"></i>{{ __('Agent Mgmt') }}
                </a>
            </li>

            {{-- NIIP Code Mgmt --}}
            <li class="nav-item">
                <a class="nav-link{{ request()->is('niip_code_mgmt') ? ' active' : '' }}"
                   href="{{ route('niip_code_mgmt') }}" wire:navigate>
                    <i class="fa fa-desktop me-1"></i>{{ __('NIIP Code Mgmt') }}
                </a>
            </li>

            {{-- Elite Broker Portfolio --}}
            <li class="nav-item">
                <a class="nav-link{{ request()->routeIs('elite.brokers', 'elite.broker.policies') ? ' active' : '' }}"
                   href="{{ route('elite.brokers') }}" wire:navigate>
                    <i class="bx bx-buildings me-1"></i>{{ __('Elite Brokers') }}
                </a>
            </li>

            @endif

        </ul>

        {{-- Right-side: Settings --}}
        <ul class="navbar-nav ms-auto align-items-xl-center">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle{{ request()->is('settings/*') ? ' active' : '' }}"
                   href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-cog me-1"></i>{{ __('Settings') }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end" style="background:#1e1e1e">
                    <li>
                        <a class="dropdown-item{{ request()->routeIs('settings.profile') ? ' active' : '' }}"
                           href="{{ route('settings.profile') }}" wire:navigate>
                            <i class="bx bx-user me-2"></i>{{ __('Profile') }}
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item{{ request()->routeIs('settings.password') ? ' active' : '' }}"
                           href="{{ route('settings.password') }}" wire:navigate>
                            <i class="bx bx-lock me-2"></i>{{ __('Password') }}
                        </a>
                    </li>
                </ul>
            </li>
        </ul>

    </div>
</nav>

<style>
    #layout-navbar .nav-link {
        color: #B18752 !important;
        font-size: 1rem;
    }
    #layout-navbar .nav-link.active,
    #layout-navbar .nav-link:hover {
        color: #fff !important;
    }
    #layout-navbar .nav-link.active {
        background-color: #B18752;
        border-radius: .375rem;
        color: #161616 !important;
    }
    #layout-navbar .dropdown-menu .dropdown-item {
        color: #B18752;
        font-weight: bold;
    }
    #layout-navbar .dropdown-menu .dropdown-item:hover,
    #layout-navbar .dropdown-menu .dropdown-item.active {
        background-color: #B18752;
        color: #161616;
    }
    #layout-navbar .navbar-toggler:focus {
        box-shadow: none;
    }
</style>
