<x-layouts.app>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

        <x-subagents.profile
        :user="$user"
        :agent="$agent"
    />

</x-layouts.app>