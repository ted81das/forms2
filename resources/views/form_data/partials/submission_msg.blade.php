<div class="row">
	<div class="col-md-6 offset-md-3 text-center mt-3">
        <div class="alert alert-success">
            <h5>
                <i class="far fa-check-circle"></i>
                {!!$form->schema['settings']['notification']['success_msg']!!}
            </h5>
        </div>
    </div>
</div>
<div class="row justify-content-center">
	@if(isset($data['sub_ref_qr_code']) && !empty($data['sub_ref_qr_code']))
		<div class="col-md-4 text-center">
			<img src="{!!$data['sub_ref_qr_code']!!}" 
				alt="{{$form->name}} Ref Num Qr Code"
				style="padding: 2.5rem;"/>
			<a href="{!!$data['sub_ref_qr_code']!!}"
        		class="btn btn-outline-primary btn-block mt-2"
        		role="button"
        		download="{{$form->name}} Ref Num Qr Code">
        		<i class="far fa-arrow-alt-circle-down fa-fw fa-lg"></i>
        		{{__('messages.download_qr_code')}}
        	</a>
		</div>
	@elseif(isset($data['sub_ref_bar_code']) && !empty($data['sub_ref_bar_code']))
		<div class="col-md-4 text-center">
			<img src="{!!$data['sub_ref_bar_code']!!}" alt="{{$form->name}} Ref Num Bar Code"
				style="padding: 2.5rem;"/>
			<a href="{!!$data['sub_ref_bar_code']!!}"
        		class="btn btn-outline-primary btn-block mt-2"
        		role="button"
        		download="{{$form->name}} Ref Num Bar Code">
        		<i class="far fa-arrow-alt-circle-down fa-fw fa-lg"></i>
        		{{__('messages.download_bar_code')}}
        	</a>
		</div>
	@elseif(!in_array($form->schema['settings']['notification']['post_submit_action'], ['redirect']) &&
		isset($form->schema['settings']['is_qr_code_enabled']) && 
		$form->schema['settings']['is_qr_code_enabled'])
		<div class="col-md-4 text-center">
	        <div id="qrcode" style="padding: 1.2rem;"></div>
			<a href=""
        		class="btn btn-outline-primary btn-block mt-2"
        		role="button"
        		download="{{$form->name}} Qr Code"
        		id="download_qrcode">
        		<i class="far fa-arrow-alt-circle-down fa-fw fa-lg"></i>
        		{{__('messages.download_qr_code')}}
        	</a>
	    </div>
	@endif
</div>