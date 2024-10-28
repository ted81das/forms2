<template>
	<section>
		<!-- form layout -->
        <label>
            {{trans('messages.layout')}}
        </label>
        <div class="row">
            <!-- classic -->
            <div class="col-md-6">
                <div class="card mb-1 bg-light-gray hvr-grow cursor-pointer"
                	:class="[_.includes(['classic'], choosen_layout) ? 'border-primary border-1 border-solid' : '']"
                	@click="setFormLayout('classic')">
                    <div class="card-body text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 121 125" class="jfWizard-list-item-icon-svg" width="187" height="204"><g fill="none" fill-rule="evenodd"><path d="M4.417 0h112a4 4 0 014 4v121h-120V4a4 4 0 014-4z" fill="#FFF" fill-rule="nonzero"></path><g transform="translate(10.417 13)"><path d="M17.001 31.122h79.14a2.858 2.858 0 010 5.716h-79.14a2.858 2.858 0 110-5.716z" fill="#DAE2ED" fill-rule="nonzero"></path><rect fill="#DAE2ED" fill-rule="nonzero" y="20.324" width="74.571" height="5.716" rx="2.858"></rect><rect fill="#DAE2ED" fill-rule="nonzero" width="99" height="8.257" rx="3"></rect><path d="M17.001 41.284h79.14a2.858 2.858 0 010 5.716h-79.14a2.858 2.858 0 110-5.716z" fill="#DAE2ED" fill-rule="nonzero"></path><g transform="translate(0 30.486)"><rect fill="#2F90FF" fill-rule="nonzero" width="6.429" height="6.351" rx="2"></rect><path d="M2.734 4.765a.33.33 0 01-.238-.103L1.304 3.424a.36.36 0 010-.495.328.328 0 01.477 0l.953.99L4.878 1.69a.328.328 0 01.477 0 .36.36 0 010 .496L2.972 4.662a.33.33 0 01-.238.103" fill="#FFF"></path></g><rect fill="#DAE2ED" fill-rule="nonzero" y="40.649" width="6.429" height="6.351" rx="2"></rect></g><g transform="translate(10.417 72)"><path d="M17.036 10.929h79.071a2.893 2.893 0 010 5.785H17.036a2.893 2.893 0 110-5.785zM2.893 0h68.786a2.893 2.893 0 110 5.786H2.893a2.893 2.893 0 010-5.786zm14.143 21.214h79.071a2.893 2.893 0 010 5.786H17.036a2.893 2.893 0 110-5.786z" fill="#DAE2ED" fill-rule="nonzero"></path><g transform="translate(0 10.286)"><rect fill="#2F90FF" fill-rule="nonzero" width="6.429" height="6.429" rx="2"></rect><path d="M2.734 4.823a.327.327 0 01-.238-.104L1.304 3.465a.367.367 0 010-.5.325.325 0 01.477 0l.953 1.002L4.878 1.71a.325.325 0 01.477 0 .367.367 0 010 .501L2.972 4.72a.328.328 0 01-.238.104" fill="#FFF"></path></g><rect fill="#DAE2ED" fill-rule="nonzero" y="20.571" width="6.429" height="6.429" rx="2"></rect><g transform="translate(0 20.571)"><rect fill="#2F90FF" fill-rule="nonzero" width="6.429" height="6.429" rx="2"></rect><path d="M2.734 4.823a.327.327 0 01-.238-.104L1.304 3.465a.367.367 0 010-.5.325.325 0 01.477 0l.953 1.002L4.878 1.71a.325.325 0 01.477 0 .367.367 0 010 .501L2.972 4.72a.328.328 0 01-.238.104" fill="#FFF"></path></g></g><rect fill="#2F90FF" fill-rule="nonzero" x="77.417" y="109" width="32" height="10" rx="4"></rect></g></svg>
                    </div>
                </div>
                <h6 class="text-center mt-0 mb-0">
            		{{trans('messages.classic_form')}}
            	</h6>
            	<p class="text-center mt-0 mb-0">
            		<small>{{trans('messages.classic_form_subtitle')}}</small>
            	</p>
            </div>
            <!-- card form -->
            <div class="col-md-6">
                <div class="card mb-1 bg-light-gray hvr-grow cursor-pointer"
                	:class="[_.includes(['card_form'], choosen_layout) ? 'border-primary border-1 border-solid' : '']"
                	@click="setFormLayout('card_form')">
                    <div class="card-body text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 121 92" class="jfWizard-list-item-icon-svg" width="166" height="146"><g fill="none" fill-rule="evenodd"><rect fill="#FFF" fill-rule="nonzero" x="7.934" width="105.131" height="62.153" rx="4"></rect><path d="M7.934 49.59h105.132v9.224a4 4 0 01-4 4H11.934a4 4 0 01-4-4V49.59z" fill="#2A5ACA" fill-rule="nonzero"></path><g transform="translate(15.208 11.24)"><rect fill="#DAE2ED" fill-rule="nonzero" x="10.579" y="11.24" width="80.667" height="5.951" rx="2.975"></rect><rect fill="#DAE2ED" fill-rule="nonzero" width="50.913" height="5.951" rx="2.975"></rect><path d="M13.555 21.82H88.27a2.975 2.975 0 010 5.95H13.555a2.975 2.975 0 010-5.95z" fill="#DAE2ED" fill-rule="nonzero"></path><g transform="translate(0 10.58)"><rect fill="#2F90FF" fill-rule="nonzero" width="6.612" height="6.612" rx="2"></rect><path d="M2.812 4.96a.337.337 0 01-.245-.107L1.34 3.564a.378.378 0 010-.515.334.334 0 01.49 0l.98 1.031 2.206-2.32a.334.334 0 01.49 0 .378.378 0 010 .515l-2.45 2.578a.337.337 0 01-.245.107" fill="#FFF"></path></g><rect fill="#DAE2ED" fill-rule="nonzero" y="21.158" width="6.612" height="6.612" rx="2"></rect></g><rect fill="#FFF" fill-rule="nonzero" width="121" height="62.153" rx="4"></rect><path d="M0 49.59h121v9.224a4 4 0 01-4 4H4a4 4 0 01-4-4V49.59z" fill="#51DCA9" fill-rule="nonzero"></path><g transform="translate(9.257 11.24)"><path d="M14.877 11.24H98.85a2.975 2.975 0 010 5.951H14.877a2.975 2.975 0 110-5.95zM2.975 0h50.913a2.975 2.975 0 110 5.95H2.975a2.975 2.975 0 110-5.95zm11.902 21.82H98.85a2.975 2.975 0 010 5.95H14.877a2.975 2.975 0 110-5.95z" fill="#DAE2ED" fill-rule="nonzero"></path><g transform="translate(0 10.58)"><rect fill="#2F90FF" fill-rule="nonzero" width="6.612" height="6.612" rx="2"></rect><path d="M2.812 4.96a.337.337 0 01-.245-.107L1.34 3.564a.378.378 0 010-.515.334.334 0 01.49 0l.98 1.031 2.206-2.32a.334.334 0 01.49 0 .378.378 0 010 .515l-2.45 2.578a.337.337 0 01-.245.107" fill="#FFF"></path></g><rect fill="#DAE2ED" fill-rule="nonzero" y="21.158" width="6.612" height="6.612" rx="2"></rect></g><path stroke="#319BF3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M8.596 86.646h104.47"></path><circle stroke="#319BF3" stroke-width="1.5" fill="#51DCA9" fill-rule="nonzero" cx="60.831" cy="86.646" r="3.967"></circle><circle fill="#319BF3" fill-rule="nonzero" cx="9.918" cy="86.646" r="3.967"></circle><circle fill="#319BF3" fill-rule="nonzero" cx="111.743" cy="86.646" r="3.967"></circle></g></svg>
                    </div>
                </div>
                <h6 class="text-center mt-0 mb-0">
            		{{trans('messages.card_form')}}
            	</h6>
            	<p class="text-center mt-0 mb-0">
            		<small>{{trans('messages.card_form_subtitle')}}</small>
            	</p>
            </div>
            <div
                v-if="_.some(selected_elements, {type: 'page_break'})"
                class="col-md-12 text-center text-danger">
                <i class="fas fa-info-circle mr-1"></i>
                <small v-text="trans('messages.card_layout_disabled')"></small>
            </div>
        </div>
        <!-- /form layout -->
	</section>
</template>
<script>
	export default {
		props:{
            selected_elements: Array,
            settings:Object,
        },
        data(){
    		return {
    			layout: 'classic',
                choosen_layout: 'classic'
    		}
    	},
	    created() {
			const self = this;
			if (_.isUndefined(self.settings.layout)) {
				Vue.set(self.settings, 'layout', self.layout);
            } else {
                self.choosen_layout = self.settings.layout;
            }
	    },
	    methods:{
	    	setFormLayout(layout) {
	    		const self = this;
                if (!_.some(self.selected_elements, {type: 'page_break'})) {
                    self.settings.layout = layout;
                    self.choosen_layout = layout;
                }
	    	}
	    }
	}
</script>
<style scoped>
    svg{
        max-width: 100%;
        height: 100px;
    }
</style>