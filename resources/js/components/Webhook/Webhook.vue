<template>
	<section class="mt-3">
		<div class="row">
			<div class="col-md-12">
				<div class="custom-control custom-checkbox">
					<input type="checkbox" 
						class="custom-control-input"
						id="is_enabled_webhook"
						v-model="webhookInfo.is_enable"
						value="1">
					<label class="custom-control-label" for="is_enabled_webhook">
						{{trans('messages.enable')}}
					</label>
				</div>
			</div>
		</div>
		<template
			v-if="webhookInfo.is_enable">
			<div class="row">
				<div class="col-md-12">
					<div class="text-left text-muted text-center">
						<small>
							<i class="fa fa-info-circle"></i>
							{{trans('messages.not_in_downloaded_code')}}
						</small>
						<br/>
					</div>
				</div>
			</div>
			<div class="form-group row">
				<label for="webhook_url" class="col-sm-3 col-form-label">
					{{trans('messages.webhook_url')}}:
					<span class="error" v-if="webhookInfo.is_enable">*</span>
				</label>
				<div class="col-sm-9">
					<div class="form-group">
		                <input type="url" class="form-control" 
		                	pattern="https://.*"
							id="webhook_url" 
							:placeholder="trans('messages.webhook_url')" 
							name="webhookInfo.webhook_url" 
							:required="webhookInfo.is_enable"
							v-model="webhookInfo.url">
			        </div>
				</div>
			</div>
			<div class="form-group row">
				<label for="secret_key" class="col-sm-3 col-form-label">
					{{trans('messages.secret_key')}}:
					<span class="error" v-if="webhookInfo.is_enable">*</span>
				</label>
				<div class="col-sm-9">
					<div class="input-group">
		                <input type="text" class="form-control" 
							id="secret_key" 
							:placeholder="trans('messages.secret_key')" 
							name="webhookInfo.secret_key" 
							:required="webhookInfo.is_enable"
							v-model="webhookInfo.secret_key"
							readonly>
			            <div class="input-group-append">
			                <button type="button" class="btn btn-primary"
			                	@click="generateSecretKey"
			                	:disabled="loading">
		                    	{{trans('messages.generate_secret_key')}}
		                    	<i class="fas fa-spinner fa-pulse fa-spin ml-1" v-if="loading"></i>
			                </button>
			            </div>
			        </div>
				</div>
			</div>
		</template>
	</section>
</template>
<script>
	export default {
		props:['webhookInfo'],
		data() {
			return {
                loading: false,
			}
		},
		methods:{
			generateSecretKey() {
				const self = this;
				self.loading = true;
				self.webhookInfo.secret_key = self.generateRandomString(18);
				setTimeout(() => {
					self.loading = false;
				}, 1000);
			}
		}
	}
</script>