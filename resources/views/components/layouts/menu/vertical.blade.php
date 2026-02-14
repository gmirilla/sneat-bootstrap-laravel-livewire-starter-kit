                @php

                    Auth::check();
                    $user = Auth::user();
                @endphp
                <!-- Menu -->
                <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                    <div class="app-brand demo">
                        <a href="{{ url('/') }}" class="app-brand-link"><x-app-logo /></a>
                    </div>

                    <div class="menu-inner-shadow"></div>

                    <ul class="menu-inner py-1">
                        <!-- Dashboards -->
                        <li class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('dashboard') }}" wire:navigate>
                                <i class="menu-icon fa fa-tachometer"></i>
                                {{ __('Dashboard') }}</a>
                        </li>
                        @if ($user->getAgentDetails()?->canregistersubagent)
                            <li
                                class="nav-item dropdown menu-item {{ request()->routeIs('list_sub_agents') || request()->routeIs('list_policy_subagents') ? 'active' : '' }}">
                                <a class="nav-link menu-link dropdown-toggle" href="#" id="subAgentDropdown" role="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa fa-users"></i> {{ __('Sub Agent Mgmt') }}
                                </a>

                                <ul class="dropdown-menu" aria-labelledby="subAgentDropdown">
                                    <li class="{{ request()->routeIs('list_sub_agents') ? 'active' : '' }}">
                                        <a class="dropdown-item" href="{{ route('list_sub_agents') }}" wire:navigate>
                                            {{ __('List Sub Agents') }}
                                        </a>
                                    </li>
                                    <li class="{{ request()->routeIs('list_policy_subagents') ? 'active' : '' }}">
                                        <a class="dropdown-item" href="{{ route('list_policy_subagents') }}"
                                            wire:navigate>
                                            {{ __('Sub Agents Policies') }}
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif

                        
                        <!-- Policy Management -->
                        <li class="menu-item {{ request()->is('list_policy') ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('list_policy') }}" wire:navigate>
                                <i class="menu-icon fa fa-pencil"></i>{{ __('Motor Policy Mgmt') }}</a>
                        </li>
                        <!-- SIPP Policy Management -->
                        <li class="menu-item {{ request()->is('list_policy') ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('list_policy') }}" wire:navigate>
                                <i class="menu-icon fa fa-line-chart"></i>{{ __('SIPP Policy Mgmt') }}</a>
                        </li>
                        <!-- Occupiers Liability Policy Management -->
                        <li class="menu-item {{ request()->is('list_policy') ? 'active' : '' }}">
                            <a class="menu-link" href="{{ route('list_policy') }}" wire:navigate>
                                <i class="menu-icon fa fa-building"></i>{{ __('Occupiers Liability Policy Mgmt') }}</a>
                        </li>


                        @if ($user->role == 'admin' or $user->role == 'superadmin')
                            <!-- User Management -->
                            <li class="menu-item {{ request()->is('list_users') ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('list_users') }}" wire:navigate>
                                    <i class="menu-icon fa fa-user"></i>{{ __('User Mgmt') }}
                                </a>
                            </li>
                        @endif




                        @if ($user->role == 'superadmin')
                            <!-- Agent Management -->
                            <li class="menu-item {{ request()->is('list_agents') ? 'active' : '' }}">
                                <a class="menu-link" href="{{ route('list_agents') }}" wire:navigate>
                                    <i class="menu-icon fa fa-address-card"></i>{{ __('Agent Mgmt') }}
                                </a>
                            </li>
                            <!-- NIIP CODE MANAGEMENT -->
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
                                    <a class="menu-link" href="{{ route('settings.profile') }}"
                                        wire:navigate>{{ __('Profile') }}</a>
                                </li>
                                <li class="menu-item {{ request()->routeIs('settings.password') ? 'active' : '' }}">
                                    <a class="menu-link" href="{{ route('settings.password') }}"
                                        wire:navigate>{{ __('Password') }}</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </aside>
                <!-- / Menu -->

                <script>
                    // Toggle the 'open' class when the menu-toggle is clicked
                    document.querySelectorAll('.menu-toggle').forEach(function(menuToggle) {
                        menuToggle.addEventListener('click', function() {
                            const menuItem = menuToggle.closest('.menu-item');
                            // Toggle the 'open' class on the clicked menu-item
                            menuItem.classList.toggle('open');
                        });
                    });
                </script>
