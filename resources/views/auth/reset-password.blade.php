<x-layouts.guest>
    <livewire:auth.reset-password :token="request()->route('token')" :email="request()->email" />
</x-layouts.guest>