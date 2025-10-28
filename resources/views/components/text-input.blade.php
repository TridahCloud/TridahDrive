@props(['disabled' => false])

@php
    $classes = 'form-control';
    $inputName = $attributes->get('name');
    if ($inputName && $errors->has($inputName)) {
        $classes .= ' is-invalid';
    } elseif ($inputName && old($inputName)) {
        $classes .= ' is-valid';
    }
@endphp

<input @disabled($disabled) {{ $attributes->merge(['class' => $classes]) }}>
