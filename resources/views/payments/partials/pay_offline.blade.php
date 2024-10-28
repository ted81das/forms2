<div class="col-md-12">
	<form action="{{action([\App\Http\Controllers\Superadmin\SubscriptionPaymentController::class, 'confirmPayment'], [$package->id])}}" method="POST">
	 	{{ csrf_field() }}
	 	<input type="hidden" name="paid_via" value="{{$k}}">

	 	<button type="submit" class="btn btn-success btn-sm">
	 		<i class="fas fa-hand-holding-usd"></i>
	 		{{$v}}
	 	</button>
	 	<small class="form-text text-muted">
	 		@lang('messages.offline_pay_helptext')
	 	</small>
	</form>
</div>