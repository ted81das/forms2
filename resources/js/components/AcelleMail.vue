<template>
	<section class="mt-3">
		<div class="custom-control custom-checkbox">
			<input type="checkbox" 
				class="custom-control-input"
				id="is_enabled_acelle_mail"
				v-model="acelleMail.is_enable"
				value="1">
			<label class="custom-control-label" for="is_enabled_acelle_mail">
				{{trans('messages.enable')}}
			</label>
		</div>
		<template
			v-if="acelleMail.is_enable">
			<div class="text-left text-muted text-center">
				<small>
					<i class="fa fa-info-circle"></i>
					{{trans('messages.not_in_downloaded_code')}}
				</small>
				<br/>
			</div>
			<div class="form-group row">
				<label for="api_token" class="col-sm-3 col-form-label">
					{{trans('messages.api_token')}}:
					<span class="error">*</span>
					<i class="fa fa-info-circle"
						data-html="true"
						data-toggle="tooltip"
						:title="trans('messages.api_token_n_list_tooltip', {name:acelle_mail_name})">
					</i>
				</label>
				<div class="col-sm-9">
					<div class="input-group">
		                <input type="text" class="form-control" 
							id="api_token" 
							:placeholder="trans('messages.api_token')" 
							name="acelleMail.api_token" 
							:required="acelleMail.is_enable"
							v-model="acelleMail.api_token">
			            <div class="input-group-append">
			                <button type="button" class="btn btn-primary"
			                	@click="getAcelleListIds"
			                	:disabled="loading">
		                    	{{trans('messages.get_list')}}
		                    	<i class="fas fa-spinner fa-pulse fa-spin ml-1" v-if="loading"></i>
			                </button>
			            </div>
			        </div>
			        <small class="form-text text-danger"
			        	v-if="error_msg">
						{{error_msg}}
					</small>
				</div>
			</div>
		</template>
		<template v-if="!_.isEmpty(campaign_list)">
			<div class="form-group row">
				<label for="site_key" class="col-sm-3 col-form-label">
					{{acelle_mail_name}} {{trans('messages.list_id')}}:
					<span class="error">*</span>
				</label>
				<div class="col-sm-9">
					<select class="form-control"
						:required="acelleMail.is_enable"
						name="acelleMail.list_id"
						v-model="acelleMail.list_id"
						@change="getListFields">
						<option value="" v-text="trans('messages.please_select')"></option>
						<template
							v-for="campaign in campaign_list">
							<option
								:value="campaign.uid" 
								v-text="campaign.name">
							</option>
						</template>
					</select>
					<small class="form-text text-danger"
			        	v-if="field_error_msg">
						{{field_error_msg}}
					</small>
				</div>
			</div>
		</template>
		<template
			v-if="!_.isEmpty(acelleMail.campaign_fields)">
			<div class="form-group row"
				v-for="(campaign_field, index) in acelleMail.campaign_fields">
				<label class="col-sm-3 col-form-label">
					{{campaign_field.label}}
					<span class="error" v-if="campaign_field.required">*</span>
					<p class="mt-0 p-0">
						<small>
							<code>
								({{campaign_field.type}})
							</code>
						</small>
					</p>
				</label>
				<div class="col-sm-9">
					<select class="form-control"
						:required="campaign_field.required"
						:name="campaign_field.key"
						v-model="campaign_field.param_field_name">
						<option value="" v-text="trans('messages.please_select')"></option>
						<template
							v-for="element in selected_elements">
							<option
								:value="element.name" 
								v-if="!_.includes(['heading', 'hr', 'html_text', 'file_upload', 'signature', 'iframe', 'youtube', 'pdf', 'countdown'], element.type)"
								v-text="element.label">
							</option>
						</template>
					</select>
				</div>
			</div>
		</template>
	</section>
</template>
<script>
	export default {
		props: ['acelleMail', 'selected_elements'],
		data() {
			return {
                acelle_mail_name: APP.ACELLE_MAIL_NAME,
                campaign_list: [],
                error_msg: '',
                field_error_msg: '',
                loading: false,
			}
		},
		created() {
			const self = this;
			if (!_.isEmpty(self.acelleMail.api_token)) {
				self.getAcelleListIds();
				self.getListFields();
			}
		},
		methods:{
			getAcelleListIds() {
				const self = this;
				if (self.acelleMail.api_token) {
					self.loading = true;
					axios
                    .get('/get-acelle-list-ids',{
				        params: {
						    token: self.acelleMail.api_token
						}
				    })
                    .then(function(response) {
                    	self.loading = false;
                        if (response.data.success) {
                        	self.campaign_list = response.data.list;
                        	self.error_msg = _.isEmpty(response.data.list) ? self.trans('messages.no_acelle_list_please_create') : '';
                        } else {
                        	self.error_msg = response.data.msg;
                        }
                    })
                    .catch(function(error) {
                        console.log(error);
                    });
				} else{
					self.error_msg = self.trans('messages.api_token_required');
				}
			},
			getListFields() {
				const self = this;
				if (self.acelleMail.api_token && self.acelleMail.list_id) {
					self.field_error_msg = self.trans('messages.retriving_fields');
					axios
                    .get('/get-acelle-list-info',{
				        params: {
						    token: self.acelleMail.api_token,
						    list_id: self.acelleMail.list_id,
						}
				    })
                    .then(function(response) {
                    	if (response.data.success) {
                    		self.field_error_msg = '';
                        	self.filterExistingFields(response.data.fields);
                        } else {
                        	self.field_error_msg = response.data.msg;
                        }
                    })
                    .catch(function(error) {
                        console.log(error);
                    });
				}
			},
			filterExistingFields(fields) {
				const self = this;
				if (self.acelleMail.campaign_fields) {
					fields.forEach(function(field){
						self.acelleMail.campaign_fields.forEach(function(campField){
							if(
								(field.key == campField.key) && 
								!_.isUndefined(campField.param_field_name))
							{
								field['param_field_name'] = campField.param_field_name;
								return false;
							}
						});
					});
				}
				self.acelleMail.campaign_fields = fields;
			}
		}
	}
</script>