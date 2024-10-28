<div class="modal-dialog modal-lg">
    <form data-type="form" action="{{action([\App\Http\Controllers\Superadmin\SubscriptionPaymentController::class, 'adminSubscription'], [$package_id, $user_id])}}" method="PUT">
        {{ csrf_field() }}
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    @lang('messages.confirm_upgrade_account')
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-check">
                            <input type="checkbox" id="disable_all_packages" name="disable_all_packages" value="1" class="form-check-input" aria-describedby="disable_all_packagesHelp" checked>
                            <label class="form-check-label" for="disable_all_packages">
                                @lang('messages.disable_current_packages')
                            </label>
                        </div>
                        <p>
                            <small id="disable_all_packagesHelp" class="form-text text-muted">
                                @lang('messages.disable_current_packages_help_text')
                            </small>
                        </p>
                    </div>
                </div>     
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">
                    @lang('messages.close')
                </button>
                <button type='submit' class="btn btn-sm btn-success btn-sm submit_btn">
                    @lang('messages.upgrade_account')
                </button>
            </div>
        </div>
    </form>
</div>