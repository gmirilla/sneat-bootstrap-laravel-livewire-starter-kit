@php
    $user = Auth::user();
@endphp

<nav class="w-full bg-white shadow-md border-b">
    <div class="max-w-7xl mx-auto px-6 flex items-center justify-between h-16">

        <!-- Logo -->
        <a href="{{ url('/') }}" class="flex items-center space-x-2">
            <x-app-logo class="h-8" />
        </a>

        <!-- Main Navigation -->
        <ul class="flex items-center space-x-8 text-gray-700 font-medium">

            <li>
                <a href="{{ route('dashboard') }}" 
                   class="{{ request()->is('dashboard') ? 'text-blue-600 font-semibold' : 'hover:text-blue-500' }}"
                   wire:navigate>
                    Dashboard
                </a>
            </li>

            @if (optional($user?->getAgentDetails())->canregistersubagent)
                <!-- Subagent Dropdown -->
                <li class="relative group">
                    <button class="hover:text-blue-500 flex items-center space-x-1">
                        <span>Subagent Mgmt</span>
                        <i class="fa fa-chevron-down text-xs"></i>
                    </button>

                    <ul class="absolute hidden group-hover:block bg-white shadow-lg rounded-md py-2 w-48 mt-2">
                        <li>
                            <a href="{{ route('list_sub_agents') }}" 
                               class="block px-4 py-2 hover:bg-gray-100"
                               wire:navigate>
                                List of Sub Agents
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('list_policy_subagents') }}" 
                               class="block px-4 py-2 hover:bg-gray-100"
                               wire:navigate>
                                Sub Agent Policies
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            <li>
                <a href="{{ route('list_policy') }}" 
                   class="{{ request()->is('list_policy') ? 'text-blue-600 font-semibold' : 'hover:text-blue-500' }}"
                   wire:navigate>
                    Motor Policy Mgmt
                </a>
            </li>

   
            <!-- Settings Dropdown -->
            <li class="relative group">
                <button class="hover:text-blue-500 flex items-center space-x-1">
                    <span>Settings</span>
                    <i class="fa fa-chevron-down text-xs"></i>
                </button>

                <ul class="absolute hidden group-hover:block bg-white shadow-lg rounded-md py-2 w-48 mt-2">
                    <li>
                        <a href="{{ route('settings.profile') }}" 
                           class="block px-4 py-2 hover:bg-gray-100"
                           wire:navigate>
                            Profile
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('settings.password') }}" 
                           class="block px-4 py-2 hover:bg-gray-100"
                           wire:navigate>
                            Password
                        </a>
                    </li>
                </ul>
            </li>

        </ul>
    </div>
</nav>