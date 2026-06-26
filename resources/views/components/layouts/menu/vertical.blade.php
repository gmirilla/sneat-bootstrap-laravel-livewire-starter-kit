                <style>
                    .menu-item.active > .menu-link {
                        background-color: #B18752;
                        color: #161616;
                    }

                    .menu-item.active > .menu-link:hover {
                        background-color: #B18752;
                        color: #161616;
                    }

                    .menu-item.active.open > .menu-link {
                        background-color: #B18752;
                        color: #161616;
                    }

                    .menu-item.active.open > .menu-link:hover {
                        background-color: #B18752;
                        color: #161616;
                    }
                    .menu-link {
                        color: #B18752 !important;
                    }
                    </style>
                @php
                    $user    = Auth::user();
                    $isAdmin = in_array($user->role, ['admin', 'superadmin']);
                @endphp
                <!-- Menu -->
                <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme" style="background: #161616; color:#B18752; font-weight: bold; font-size: 1.2rem; padding: 0.5rem 1rem;">
                    <div class="app-brand demo" style="background: #161616; font-weight: bold; font-size: 1.2rem; padding: 0.5rem 1rem;">
                        <a href="{{ url('/') }}" class="app-brand-link"><x-app-logo /></a>
                    </div>

                    <div class="menu-inner-shadow"></div>

                    <ul class="menu-inner py-1">

                        <!-- Dashboard -->
                        <li class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('dashboard') }}" wire:navigate>
                                <i class="menu-icon fa fa-tachometer"></i>
                                {{ __('Dashboard') }}
                            </a>
                        </li>

                        @if ($user->getAgentDetails()?->canregistersubagent)
                        <!-- Subagent Management -->
                        <li class="menu-item {{ request()->routeIs('list_sub_agents') || request()->routeIs('list_policy_subagents') ? 'active open' : '' }}">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <i class="menu-icon fa fa-users"></i>
                                <div class="text-truncate">{{ __('Subagent Mgmt') }}</div>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item {{ request()->routeIs('list_sub_agents') ? 'active' : '' }}">
                                    <a class="menu-link" href="{{ route('list_sub_agents') }}" wire:navigate>{{ __('List of Sub Agents') }}</a>
                                </li>
                                <li class="menu-item {{ request()->routeIs('list_policy_subagents') ? 'active' : '' }}">
                                    <a class="menu-link" href="{{ route('list_policy_subagents') }}" wire:navigate>{{ __('Sub Agent Policies') }}</a>
                                </li>
                            </ul>
                        </li>
                        @endif

                        <!-- Policy Management -->
                        <li class="menu-item {{ request()->is('list_policy') ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('list_policy') }}" wire:navigate>
                                <i class="menu-icon fa fa-pencil"></i>{{ __('Motor Policy Mgmt') }}
                            </a>
                        </li>

                        <!-- Claim Notification (all roles) -->
                        <li class="menu-item {{ request()->routeIs('claim.notify.*') || request()->routeIs('claim.notifications') ? 'active open' : '' }}">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <i class="menu-icon bx bx-notepad"></i>
                                <div class="text-truncate">{{ __('Claim Notification') }}</div>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item {{ request()->routeIs('claim.notify.*') ? 'active' : '' }}">
                                    <a class="menu-link" href="{{ route('claim.notify.lookup') }}" wire:navigate>{{ __('Submit Notification') }}</a>
                                </li>
                                <li class="menu-item {{ request()->routeIs('claim.notifications') ? 'active' : '' }}">
                                    <a class="menu-link" href="{{ route('claim.notifications') }}" wire:navigate>{{ __('My Notifications') }}</a>
                                </li>
                            </ul>
                        </li>

                        @if ($isAdmin)
                        <!-- Police eCMR -->
                        <li class="menu-item {{ request()->is('ecmr_index') ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('ecmrs.index') }}" wire:navigate>
                                <i class="menu-icon fa fa-id-card-o"></i>{{ __('Police eCMR') }}
                            </a>
                        </li>

                        <!-- Claims Admin -->
                        @php $pendingCount = \App\Models\User::where('account_status', 'pending')->count(); @endphp
                        <li class="menu-item {{ request()->routeIs('admin.pending_accounts') || request()->routeIs('claim.notifications') ? 'active open' : '' }}">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <i class="menu-icon bx bx-briefcase"></i>
                                <div class="text-truncate">{{ __('Claims Admin') }}</div>
                                @if ($pendingCount > 0)
                                    <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem">{{ $pendingCount }}</span>
                                @endif
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item {{ request()->routeIs('claim.notifications') ? 'active' : '' }}">
                                    <a class="menu-link" href="{{ route('claim.notifications') }}" wire:navigate>{{ __('All Notifications') }}</a>
                                </li>
                                <li class="menu-item {{ request()->routeIs('admin.pending_accounts') ? 'active' : '' }}">
                                    <a class="menu-link" href="{{ route('admin.pending_accounts') }}" wire:navigate>
                                        {{ __('Pending Accounts') }}
                                        @if ($pendingCount > 0)
                                            <span class="badge bg-warning text-dark ms-auto" style="font-size:.65rem">{{ $pendingCount }}</span>
                                        @endif
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- User Management -->
                        <li class="menu-item {{ request()->routeIs('list_users') ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('list_users') }}" wire:navigate>
                                <i class="menu-icon fa fa-user"></i>{{ __('User Mgmt') }}
                            </a>
                        </li>
                        @endif

                        @if ($user->role === 'superadmin')
                        <!-- Agent Management -->
                        <li class="menu-item {{ request()->is('list_agents') ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('list_agents') }}" wire:navigate>
                                <i class="menu-icon fa fa-address-card"></i>{{ __('Agent Mgmt') }}
                            </a>
                        </li>
                        <!-- NIIP Code Management -->
                        <li class="menu-item {{ request()->is('niip_code_mgmt') ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('niip_code_mgmt') }}" wire:navigate>
                                <i class="menu-icon fa fa-desktop"></i>{{ __('NIIP Code Mgmt') }}
                            </a>
                        </li>
                        @endif

                        <!-- Settings -->
                        <li class="menu-item {{ request()->is('settings/*') ? 'active open' : '' }}">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <i class="menu-icon tf-icons bx bx-cog"></i>
                                <div class="text-truncate">{{ __('Settings') }}</div>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item {{ request()->routeIs('settings.profile') ? 'active' : '' }}">
                                    <a class="menu-link" href="{{ route('settings.profile') }}" wire:navigate>{{ __('Profile') }}</a>
                                </li>
                                <li class="menu-item {{ request()->routeIs('settings.password') ? 'active' : '' }}">
                                    <a class="menu-link" href="{{ route('settings.password') }}" wire:navigate>{{ __('Password') }}</a>
                                </li>
                            </ul>
                        </li>

                    </ul>
                </aside>
                <!-- / Menu -->

                <script>
                    document.querySelectorAll('.menu-toggle').forEach(function (menuToggle) {
                        menuToggle.addEventListener('click', function () {
                            menuToggle.closest('.menu-item').classList.toggle('open');
                        });
                    });
                </script>
