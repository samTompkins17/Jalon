@php
    $messages = collect([
        'succes' => session('succes'),
        'erreur' => session('erreur'),
        'info' => session('info') ?? session('statut'),
    ])->filter();

    if ($errors->any() && ! session()->has('erreur')) {
        $messages['erreur'] = $errors->first();
    }
@endphp

<div class="toasts" role="status" aria-live="polite">
    @foreach ($messages as $type => $message)
        <div class="toast toast--{{ $type }}" data-toast>
            <span class="point"></span>
            <span>{{ $message }}</span>
        </div>
    @endforeach
</div>
