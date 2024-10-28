<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">
                @lang('messages.upgrade_account')
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            
            @if(count($active_packages) <= 0)
                <div class="alert alert-danger" role="alert">
                    <span class="text-white">@lang('messages.no_packages_found')</span>
                </div>
            @endif
            
            <div class="row">
                @foreach($active_packages as $package)
                    <div class="col-md-6">
                        <div class="card card-outline card-success on_hover">
                            <div class="card-header">
                                <div class="text-center">
                                    <h5>
                                        {{$package->name}}
                                    </h5>
                                </div>
                            </div>
                            <div class="card-body text-center">
                                @if($package->no_of_active_forms != 0)
                                    <span>
                                        <i class="far fa-check-circle text-success"></i>
                                        @lang('messages.no_of_forms',[
                                        'active_form' => $package->no_of_active_forms])
                                    </span>
                                @else
                                    <span>
                                        <i class="far fa-check-circle text-success"></i>
                                        @lang('messages.unlimited_forms')
                                    </span>
                                @endif
                                <hr>
                                @if($package->is_form_downloadable)
                                    <span>
                                        <i class="far fa-check-circle text-success"></i>
                                        @lang('messages.form_code_download')
                                    </span>
                                @else
                                    <span>
                                        <i class="far fa-times-circle text-danger"></i>
                                        @lang('messages.form_code_download')
                                    </span>
                                @endif
                                <hr>
                                @php
                                    $price_interval = __('messages.'.$package->price_interval);
                                @endphp
                                @if($package->price != 0)
                                    <h4>
                                    <span class="currency">
                                        {{ number_format((float)$package->price, 2, '.', '')}} 
                                    </span>
                                    <small class="text-muted">
                                        @lang('messages.subscription_price',[
                                            'interval' => $package->interval,
                                            'price_interval' => $price_interval
                                        ])
                                    </small>
                                    </h4>
                                @else
                                    <h4>
                                    @lang('messages.free_for_interval', [
                                        'interval' => $package->interval,
                                        'price_interval' => $price_interval
                                    ])
                                    </h4>
                                @endif
                            </div>
                            <div class="card-footer text-center">
                                <button type="button" class="btn btn-block btn-success btn-sm confirm-subscription"
                                    data-href="{{action([\App\Http\Controllers\Superadmin\SubscriptionPaymentController::class, 'confirmAdminSubscription'], [$package->id, $user->id])}}">
                                    @lang('messages.subscribe')
                                </button>
                                {{$package->description}}
                            </div>
                        </div>
                    </div>
                    @if($loop->iteration%3 == 0)
                        <div class="clearfix"></div>
                    @endif
                @endforeach
            
            </div>     
        <div class="modal-footer">
            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">
                @lang('messages.close')
            </button>
        </div>
    </div>
</div>