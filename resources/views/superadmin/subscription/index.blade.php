@extends('layouts.app')
@section('content')
<div class="container">
	<div class="row justify-content-center">
		<div class="col-md-12">
			<div class="card card-outline card-info">
				<div class="card-header">
					<div class="card-title">
						<h3>
							<i class="fas fa-sync-alt"></i>
							@lang('messages.package_subscription')
						</h3>
					</div>
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
									                <label for="filter_by_package">
									                    @lang('messages.package')
									                </label>
									                <select name="filter_by_package" id="filter_by_package" class="form-control">
									                	<option value="">
								                            @lang('messages.all')
								                        </option>
								                        @if(!empty($packages))
									                        @foreach($packages as $id => $package)
										                        <option value="{{$id}}">
										                            {{$package}}
										                        </option>
									                        @endforeach
									                    @endif
									                </select>
									            </div>
											</div>
											<div class="col-md-3">
												<div class="form-group">
									                <label for="filter_by_status">
									                    @lang('messages.status')
									                </label>
									                <select name="filter_by_status" id="filter_by_status" class="form-control">
									                	<option value="">
								                            @lang('messages.all')
								                        </option>
								                        @if(!empty($subscription_status))
									                        @foreach($subscription_status as $id => $status)
										                        <option value="{{$id}}">
										                            {{$status}}
										                        </option>
									                        @endforeach
									                    @endif
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
								<table class="table" id="package_subscription_table">
									<thead>
										<tr>
											<th>@lang('messages.name')</th>
											<th>@lang('messages.email')</th>
											<th>@lang('messages.package_name')</th>
											<th>@lang('messages.status')</th>
											<th>@lang('messages.start_date')</th>
											<th>@lang('messages.end_date')</th>
											<th>@lang('messages.price')</th>
											<th>@lang('messages.paid_via')</th>
											<th>@lang('messages.transaction_id')</th>
											<th>@lang('messages.action')</th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
@section('footer')
<script type="text/javascript">
	$(document).ready(function(){
		var package_subscription_table = $("#package_subscription_table").DataTable({
				processing: true,
                serverSide: true,
                ajax:{
                    url: '/superadmin/package-subscription',
                    data: function(d) {
                        d.package_id = $('#filter_by_package').val();
                        d.status = $('#filter_by_status').val();
                    }
                },
                buttons: [
				   	{
				       	extend: 'csv',
				       	footer: true,
				       	exportOptions: {
				            columns: [0,1,2,3,4,5,6,7,8]
				        },
				        title: '{{config("app.name") ."-". __("messages.package_subscription")}}'
				   	},
				   	{
				       	extend: 'excelHtml5',
				       	footer: false,
				       	exportOptions: {
				            columns: [0,1,2,3,4,5,6,7,8]
				        },
				        title: '{{config("app.name") ."-". __("messages.package_subscription")}}'
				   }         
				],
                fixedHeader: false,
                columnDefs: [
	                {
	                    targets: [2],
	                    orderable: false,
	                    searchable: false,
	                },
	            ],
                columns: [
                    { data: 'user' , name: 'users.name'},
                    { data: 'user_email' , name: 'users.email'},
                    { data: 'package' , name: 'package'},
                    { data: 'status' , name: 'status'},
                    { data: 'start_date' , name: 'start_date'},
                    { data: 'end_date', name: 'end_date'},
                    { data: 'package_price' , name: 'package_price'},
                    { data: 'paid_via', name: 'paid_via'},
                    { data: 'payment_transaction_id' , name: 'payment_transaction_id'},
                    { data: 'action', name: 'action', sortable:false }
                ],
                "fnDrawCallback": function (oSettings) {
              __convert_currency_in_datatable($('#package_subscription_table'));
          }
		});

		package_subscription_table.buttons().container().appendTo($('#export-btns'));
		
		$(document).on('click', '.edit_subscription', function(){
			var url = $(this).data('href');
			$.ajax({
                method: "GET",
                url: url,
                dataType: "html",
                success: function(response) {
                    $("#modal_div").html(response).modal("show");
                }
            });
		});

		$(document).on('submit', 'form#edit_subscription', function(e){
            e.preventDefault();
            var data = $('form#edit_subscription').serialize();
            var url = $('form#edit_subscription').attr('action');
            $.ajax({
                method:"PUT",
                url: url,
                data: data,
                dataType: "json",
                success:function(response) {
                    if(response.success == true){
                        toastr.success(response.msg);
                        package_subscription_table.ajax.reload();
                        $("#modal_div").modal("hide");
                    } else {    
                        toastr.error(response.msg);
                    }
                }
            });
	    });

	    $(document).on('change', '#filter_by_package, #filter_by_status', function() {
			package_subscription_table.ajax.reload();
		});
	});
</script>
@endsection