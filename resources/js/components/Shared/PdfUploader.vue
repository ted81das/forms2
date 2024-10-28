<template>
	<div class="row mb-1">
		<!-- remove pdf -->
		<div class="card border-secondary mb-2"
			v-show="!_.isEmpty(element.pdf)">
			<div class="row">
				<div class="col-md-4 mt-auto mb-auto">
					<i class="fas fa-file-pdf fa-5x ml-1 text-primary"></i>
				</div>
				<div class="col-md-8">
					<div class="card-body">
						<p class="card-text text-truncate">
				        	{{element.pdf}}
				        </p>
				        <p class="card-text text-warning cursor-pointer"
				        	@click="removePdf">
				        	{{trans('messages.remove_pdf')}}
				        </p>
				    </div>
				</div>
			</div>
		</div>
		<!-- dropzone -->
		<div class="col-md-12 mb-1"
			v-show="_.isEmpty(element.pdf)">
			<label for="pdf_upload">
				{{trans('messages.upload_pdf')}}
			</label>
			<div class="dropzone" id="pdf_upload"></div>
		</div>
		<!-- pdf properties -->
		<div v-if="!_.isEmpty(element.pdf)">
	        <div class="mb-1">
                <label>
                    {{trans('messages.width')}}
                </label>
                <div class="input-group mb-2">
                    <input type="number" class="form-control" v-model="element.width">
                    <div class="input-group-append">
	                    <span class="input-group-text">
	                        %
	                    </span>
	                </div>
                </div>
            </div>
            <div class="mb-1">
                <label>
                    {{trans('messages.height')}}
                </label>
                <div class="input-group mb-2">
                    <input type="number" class="form-control" v-model="element.height">
                   	<div class="input-group-append">
	                    <span class="input-group-text">
	                        px
	                    </span>
	                </div>
                </div>
            </div>
	    </div>
	</div>
</template>
<script>
	export default {
		props: {
			element: Object,
		},
		data(){
            return{
                MAX_UPLOAD_SIZE: APP.MAX_PDF_UPLOAD_SIZE,
				dropzone_has_error: false,
				dropzone_error_msg: ''
            }
        },
		mounted() {
			const self = this;
			self.initPdfUploader();
		},
		methods:{
			initPdfUploader() {
				const self = this;
	            if (self.dropzone) {
	                self.dropzone.destroy();
	            }
	            self.dropzone = new Dropzone('div#pdf_upload', {
	                url: `${APP.APP_URL}/file-upload`,
	                addRemoveLinks: true,
	                uploadMultiple: false,
	                dictDefaultMessage: self.trans('messages.drop_a_pdf_here'),
	                maxFiles: 1,
	                maxFilesize: self.MAX_UPLOAD_SIZE,
	                acceptedFiles: '.pdf',
	                autoProcessQueue: true,
	                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
	                init: function() {
	                    //function to be use on removeing a file
		                this.on("removedfile", function(file) {
		                    $.ajax({
		                        url: `${APP.APP_URL}/file-delete`,
		                        data: { "file_name": file.uploaded_as },
		                        type: "POST",
		                        success: function(result) {
		                            if(result.success == 1){
		                                self.element.pdf = '';
		                            } else {
		                                toastr.error(result.msg);
		                            }
		                        }
		                    });
		                });
		                this.on('success', function(file, response) {
	                    	self.dropzone_has_error = false;
	                    	self.dropzone_error_msg = '';
	                    });
	                    this.on("error", function(file, message) { 
	                    	self.dropzone_has_error = true;
			                self.dropzone_error_msg = message;
					    });
	                },
	                success: function(file, response) {
	                    if (response.success == true) {
	                    	file.uploaded_as = response.path;
	                    	self.element.pdf = response.path;
							toastr.success(response.msg);
	                    } else {
							toastr.error(response.msg);
						}
	                },
	            });
			},
			removePdf() {
                const self = this;
				Swal.fire({
                  title: 'Are you sure?',
                  icon: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  confirmButtonText: 'Yes, remove it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                       $.ajax({
	                        url: `${APP.APP_URL}/file-delete`,
	                        data: { "file_name": self.element.pdf},
	                        type: "POST",
	                        success: function(result) {
	                            if(result.success == 1){
	                                self.element.pdf = '';
	                                self.dropzone.removeAllFiles();
	                            } else {
	                                toastr.error(result.msg);
	                            }
	                        }
	                    });
                    }
                });
			}
		}
	}
</script>