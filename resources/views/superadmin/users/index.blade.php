@extends('layouts.app')
@section('content')
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-md-12">
				<div class="card card-outline card-info">
					<div class="card-header">
						<div class="card-title">
							<h3>
								<i class="fas fa-users-cog"></i>
								@lang('messages.all_users')
							</h3>
						</div>
						<button type="button" class="btn btn-sm btn-primary float-right" data-href="{{action([\App\Http\Controllers\Superadmin\ManageUsersController::class, 'create'])}}" id="add_user">
							<i class="fas fa-user-plus"></i>
							@lang('messages.add')
						</button>
					</div>
					<div class="card-body">
						<div class="row mb-4">
							<div class="col-md-12">
								<div class="box-group" id="accordion">
									<div class="panel box box-primary">
					                  <div class="box-header with-border">
					                    <h4 class="box-title">
					                      <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
					                      	<i class="fas fa-filter"></i>
					                        @lang('messages.filter')
					                      </a>
					                    </h4>
					                  </div>
					                  <div id="collapseTwo" class="panel-collapse collapse">
					                    <div class="box-body">
					                    	<div class="row">
									        	<div class="col-md-3">
													<div class="form-group">
										                <label for="filter_by_status">
										                    @lang('messages.status')
										                </label>
										                <select name="filter_by_status" id="filter_by_status" class="form-control">
										                	<option value="">
									                            @lang('messages.all')
									                        </option>
									                        <option value="active">
									                            @lang('messages.active')
									                        </option>
									                        <option value="inactive">
										                        @lang('messages.inactive')
										                    </option>
										                </select>
										            </div>
												</div>
									        </div>
					                    </div>
					                  </div>
					                </div>
				            	</div>
							</div>
						</div>
						<div class="row">
							<div class="col-md-12">
								<div class="table-responsive">
									<div id="export-btns" class="float-right"></div>
									<table class="table" id="users_table">
				                        <thead>
				                            <tr>
				                                <th>@lang('messages.name')</th>
				                                <th>@lang('messages.email')</th>
				                                <th>
				                                	@lang('messages.is_active')
				                                </th>
				                                <th>
				                                	@lang('messages.created_at')
				                                </th>
				                                <th>@lang('messages.action')</th>
				                            </tr>
				                        </thead>
				                        <tbody></tbody>
				                    </table>
				                </div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>		
	</div>
	<div class="modal" id="user_modal" tabindex="-1" role="dialog"></div>
@endsection
@section('footer')
<script type="text/javascript">
	$(document).ready(function(){
		var users_table = $('#users_table').DataTable({
						processing: true,
		                serverSide: true,
		                ajax:{
		                    url: '/superadmin/users',
		                    data: function(d) {
		                        d.status = $('#filter_by_status').val();
		                    }
		                },
		                buttons: [
						   	{
						       	extend: 'csv',
						       	footer: true,
						       	exportOptions: {
						            columns: [0,1,2,3]
						        },
						        title: '{{config("app.name") ."-". __("messages.all_users")}}'
						   	},
						   	{
						       	extend: 'excelHtml5',
						       	footer: false,
						       	exportOptions: {
						            columns: [0,1,2,3]
						        },
						        title: '{{config("app.name") ."-". __("messages.all_users")}}'
						   }         
						],
						dom: 'lBfrtip',
		                fixedHeader: false,
		                columns: [
		                    { data: 'name' , name: 'name'},
		                    { data: 'email' , name: 'email'},
		                    { data: 'is_active' , name: 'is_active'},
		                    { data: 'created_at', name: 'created_at'},
		                    { data: 'action', name: 'action', sortable:false }
		                ]
		});

		users_table.buttons().container().appendTo($('#export-btns'));

		$(document).on('click', '.toggle_is_active', function(){
			url = $(this).data('href');
			$.ajax({
				method:"GET",
				url: url,
				dataType: "json",
				success:function(response) {
					if(response.success == true){
                        toastr.success(response.msg);
                        users_table.ajax.reload();
                    } else {    
                        toastr.error(response.msg);
                    }
				}
			});
		});

		$(document).on('click', '.delete_user', function () {
			var url = $(this).data('href');

			if (confirm('Are you sure?')) {
				$.ajax({
					method:"DELETE",
					url: url,
					dataType: "json",
					success:function(response) {
						if(response.success == true){
	                        toastr.success(response.msg);
	                        users_table.ajax.reload();
	                    } else {    
	                        toastr.error(response.msg);
	                    }
					}
				});
			}
		});

		$(document).on('click', '#add_user', function () {
			var url = $(this).data('href');
			$.ajax({
				method: "GET",
				url: url,
				dataType: "html",
				success: function (response) {
					$("#user_modal").html(response).modal('show');
				}
			});
		});

		$(document).on('click', '.edit_user', function () {
			var url = $(this).data('href');
			$.ajax({
				method: "GET",
				url: url,
				dataType: "html",
				success: function (response) {
					$("#user_modal").html(response).modal('show');
				}
			});
		});

		$(document).on('click', '.upgrade_account', function () {
			var url = $(this).data('href');
			$.ajax({
				method: "GET",
				url: url,
				dataType: "html",
				success: function (response) {
					$("#user_modal").html(response).modal('show');
				}
			});
		});

		$("#user_modal").on('shown.bs.modal', function () {
			if ($("form#add_user_form").length) {
				$("form#add_user_form").validate({
					rules: {
			            email: {
			                email: true,
			                remote: {
			                    url: "/superadmin/users/check-email-exist",
			                    type: "post",
			                    data: {
			                        email: function() {
			                            return $( "#email" ).val();
			                        }
			                    }
			                }
			            }
			        },
			        messages: {
			            email: {
			                remote: '{{ __("validation.unique", ["attribute" => __("messages.email")]) }}'
			            }
			        }
		    	});
			}

			if ($("form#edit_user_form").length) {
				$("form#edit_user_form").validate({
					rules: {
			            email: {
			                email: true,
			                remote: {
			                    url: "/superadmin/users/check-email-exist",
			                    type: "post",
			                    data: {
			                        email: function() {
			                            return $( "#email" ).val();
			                        },
			                        user_id: $('input#user_id').val()
			                    }
			                }
			            }
			        },
			        messages: {
			            email: {
			                remote: '{{ __("validation.unique", ["attribute" => __("messages.email")]) }}'
			            }
			        }
		    	});
			}

			if ($("#form_design").length) {
				$(document).on('change', '#form_design', function(){
					if ($("#form_design").is(":checked")) {
						$("#form_view").attr('checked', true);
					} else {
						$("#form_view").attr('checked', false);
					}
				});
			}
		});

		$(document).on('submit', 'form#add_user_form', function (e) {
			e.preventDefault();
			var data = $("form#add_user_form").serialize();
			var url = $("form#add_user_form").attr('action');
			var ladda = Ladda.create(document.querySelector('.submit_btn'));
            ladda.start();
			$.ajax({
				method: "POST",
				url: url,
				dataType: "json",
				data: data,
				success: function (response) {
					ladda.stop();
					if (response.success) {
						$("#user_modal").modal('hide');
						toastr.success(response.msg);
                        users_table.ajax.reload();
					} else {
						toastr.error(response.msg);
					}
				}
			});
		});

		$(document).on('submit', 'form#edit_user_form', function (e) {
			e.preventDefault();
			var data = $("form#edit_user_form").serialize();
			var url = $("form#edit_user_form").attr('action');
			var ladda = Ladda.create(document.querySelector('.submit_btn'));
            ladda.start();
			$.ajax({
				method: "PUT",
				url: url,
				dataType: "json",
				data: data,
				success: function (response) {
					ladda.stop();
					if (response.success) {
						$("#user_modal").modal('hide');
						toastr.success(response.msg);
                        users_table.ajax.reload();
					} else {
						toastr.error(response.msg);
					}
				}
			});
		});
		
		$(document).on('submit', 'form[data-type="form"]', function (e) {
			e.preventDefault();
			var data = $(this).serialize();
			var url = $(this).attr('action');
			var ladda = Ladda.create(this.querySelector('.submit_btn'));
            ladda.start();
			$.ajax({
				method: "PUT",
				url: url,
				dataType: "json",
				data: data,
				success: function (response) {
					ladda.stop();
					if (response.success) {
						$("#user_modal").modal('hide');
						toastr.success(response.msg);
					} else {
						toastr.error(response.msg);
					}
				}
			});
		});

		$(document).on('change', '#filter_by_status', function() {
			users_table.ajax.reload();
		});

		$(document).on('click', '.confirm-subscription', function(e){
			let url = $(this).data('href');
			$.ajax({
				method: "GET",
				url: url,
				dataType: "html",
				success: function (response) {
					$("#user_modal").html(response).modal('show');
				}
			});
		});
	});
</script>
@endsection