<div class="col-md-12">

	<a href="{{action([\App\Http\Controllers\Superadmin\SubscriptionPaymentController::class, 'paypalExpressCheckout'], [$package->id])}}" class="btn btn-primary btn-sm">
		<i class="fab fa-paypal"></i>
		PayPal
	</a>
	
</div>