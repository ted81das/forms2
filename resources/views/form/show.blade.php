@extends('layouts.app')

@section('content')
@php
    $bg_color = $form->schema['settings']['color']['background'];
    $error_msg_color = $form->schema['settings']['color']['error_msg'];
    $additional_css = $form->schema['additional_js_css']['css'];
    $additional_js = $form->schema['additional_js_css']['js'];
    $page_color = $form->schema['settings']['color']['page_color'] ?? '#f4f6f9';
@endphp
    
<div class="@if(!empty($iframe_enabled) && $iframe_enabled) container-fluid @else container @endif">
    <div class="row justify-content-center">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
    		@include('form.partials.form_card')
        </div>
    </div>
</div>
@endsection

@section('footer')
<!-- form theme -->
@if(isset($form->schema['settings']['theme']) && $form->schema['settings']['theme'] != 'default')
    <link href="https://stackpath.bootstrapcdn.com/bootswatch/4.4.1/{{$form->schema['settings']['theme']}}/bootstrap.min.css" rel="stylesheet">
@endif
<style type="text/css">
    .error {
        color:{{$error_msg_color}}
    }
    .content-wrapper {
        background-color: {{$page_color}} !important;
    }
</style>

<!-- Form additional JS -->
@if(!empty($additional_js))
    {!!$additional_js!!}
@endif

@endsection
