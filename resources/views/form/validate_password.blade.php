@extends('layouts.app')
@section('content')
<div class="container mt-5">
    <div class="col-md-6 offset-md-3 mt-5">
        <div class="card card-outline card-primary card-widget widget-user shadow pb-5">
            <div class="card-body">
                <form id="password-form" action="{{route('post.validate.protected.form', ['id' => $id])}}"
                        method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <h4 class="text-center">
                                <i class="far fa-file-archive fa-5x"></i>
                            </h4>
                            <h4 class="text-center">
                                @lang('messages.this_form_is_password_protected')
                            </h4>
                            <p class="text-center form-text text-muted">
                                {{__('messages.enter_your_password_to_view_the_form')}}
                            </p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-key"></i>
                                    </span>
                                </div>
                                <input type="password" class="form-control"
                                    placeholder="@lang('messages.password')"
                                    required
                                    id="password">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-sm bg-primary">
                                        @lang('messages.continue')
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="error-msg text-danger"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('footer')
<script type="text/javascript">
    $(function() {
        $("form#password-form").validate({
            submitHandler: function(form, e) {
                e.preventDefault();
                if ($('form#password-form').valid()) {
                    $(".error-msg").html('');
                    $.ajax({
                        method:"POST",
                        url: $("form#password-form").attr("action"),
                        data: {
                            password: $("#password").val()
                        },
                        dataType: "json",
                        success: function(response) {
                            if(response.success == true){
                                window.location = response.redirect;
                            } else {    
                                $(".error-msg").html(response.msg);
                            }
                        }
                    });
                }
            }
        });
    });
</script>
@endsection