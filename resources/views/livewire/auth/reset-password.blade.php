<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->string('email');
    }

    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            fn ($user) => tap($user)->forceFill([
                'password'       => Hash::make($this->password),
                'remember_token' => Str::random(60),
            ])->save() && event(new PasswordReset($user)),
        );

        if ($status !== Password::PASSWORD_RESET) {
            $this->dispatch('toast', [
                'type'    => 'error',
                'title'   => __('Password Reset Failed'),
                'message' => __($status),
            ]);
            return;
        }

        $this->dispatch('toast', [
            'type'    => 'success',
            'title'   => __('Password Reset'),
            'message' => __('Your password has been reset successfully. Redirecting to login…'),
        ]);

        $this->js("setTimeout(() => \$wire.redirectToLogin(), 2000)");
    }

    public function redirectToLogin(): void
    {
        $this->redirectRoute('login', navigate: true);
    }
}; ?>

@section('title', 'Reset Password')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

<div>
    {{-- Toast Container --}}
    <div
        id="toast-container"
        class="toast-container position-fixed top-0 end-0 p-3"
        style="z-index: 9999;"
        x-data="toastManager()"
        x-on:toast.window="addToast($event.detail[0])"
    >
        <template x-for="(toast, index) in toasts" :key="toast.id">
            <div
                class="toast align-items-center border-0 mb-2"
                :class="{
                    'bg-success text-white': toast.type === 'success',
                    'bg-danger text-white':  toast.type === 'error',
                    'bg-warning text-dark':  toast.type === 'warning',
                    'bg-info text-white':    toast.type === 'info',
                }"
                role="alert"
                x-show="toast.visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-4"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
            >
                <div class="d-flex">
                    <div class="toast-body">
                        <div class="fw-semibold" x-text="toast.title"></div>
                        <div class="small opacity-75" x-text="toast.message"></div>
                    </div>
                    <button
                        type="button"
                        class="btn-close btn-close-white me-2 m-auto"
                        x-on:click="dismiss(index)"
                        aria-label="Close"
                    ></button>
                </div>
            </div>
        </template>
    </div>

    <h4 class="mb-1">{{ __('Reset Password') }} 🔑</h4>
    <p class="mb-6">{{ __('Your new password must be different from previously used passwords') }}</p>

    <form wire:submit="resetPassword" class="mb-6">
        <div class="mb-6">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input
                wire:model="email"
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                required
                autocomplete="email"
                placeholder="{{ __('Enter your email') }}"
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-6 form-password-toggle">
            <label class="form-label" for="password">{{ __('New Password') }}</label>
            <div class="input-group input-group-merge">
                <input
                    wire:model="password"
                    type="password"
                    class="form-control @error('password') is-invalid @enderror"
                    id="password"
                    required
                    autocomplete="new-password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                >
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-6 form-password-toggle">
            <label class="form-label" for="password_confirmation">{{ __('Confirm Password') }}</label>
            <div class="input-group input-group-merge">
                <input
                    wire:model="password_confirmation"
                    type="password"
                    class="form-control @error('password_confirmation') is-invalid @enderror"
                    id="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                >
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                @error('password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button
            type="submit"
            class="btn btn-primary d-grid w-100 mb-6"
            wire:loading.attr="disabled"
            wire:target="resetPassword"
        >
            <span wire:loading.remove wire:target="resetPassword">{{ __('Set New Password') }}</span>
            <span wire:loading wire:target="resetPassword">
                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                {{ __('Resetting…') }}
            </span>
        </button>

        <div class="text-center">
            <a href="{{ route('login') }}" class="d-flex justify-content-center" wire:navigate>
                <i class="bx bx-chevron-left scaleX-n1-rtl me-1"></i>
                {{ __('Back to login') }}
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function toastManager() {
        return {
            toasts: [],
            addToast(toast) {
                const id = Date.now();
                this.toasts.push({ id, visible: true, ...toast });
                // Auto-dismiss after 5s (or 3s on success since we redirect anyway)
                const delay = toast.type === 'success' ? 3000 : 5000;
                setTimeout(() => this.dismiss(this.toasts.findIndex(t => t.id === id)), delay);
            },
            dismiss(index) {
                if (this.toasts[index]) {
                    this.toasts[index].visible = false;
                    setTimeout(() => this.toasts.splice(index, 1), 300);
                }
            },
        };
    }
</script>
@endpush