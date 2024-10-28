<template>
    <form id="show_form" v-bind="getCustomAttributes(form_custom_attributes)">
        <div class="row">
            <template
                v-for="(element, index) in formFields">
                <div
                    v-if="!_.includes(['page_break'], element.type)"
                    :key="index"
                    :class="[element.col ? element.col : 'col-md-12']"
                    v-show="togglePageVisibility(element.page_num)">
                    <fieldGenerator 
                        :element="element"
                        :settings="settings"
                        :submitted_data="submitted_data"
                        @apply_conditions="applyConditions">
                    </fieldGenerator>
                </div>
            </template>
        </div>
        <div class="g-recaptcha" :data-sitekey="settings.recaptcha.site_key" v-if="settings.recaptcha.is_enable"></div>
        <div class="row"
            :class="[!isCardFormLayout() ? settings.submit.btn_alignment : 'float-right']"
            v-show="isSubmitVisible()">
            <button v-if="!actionBy.length && settings.is_enabled_draft_submit"
                formnovalidate="formnovalidate" type="submit" class="btn m-1 draft_btn"
                :class="[settings.submit.btn_size, settings.submit.btn_style == 'default' ? 'btn-warning': 'btn-outline-warning']" name="status" value="incomplete">
                <i class="fas " :class="settings.submit.btn_icon" v-if="settings.submit.btn_icon != 'none' && settings.submit.icon_position == 'left'"></i>
                {{trans('messages.draft')}}
                <i class="fas " :class="settings.submit.btn_icon" v-if="settings.submit.btn_icon != 'none' && settings.submit.icon_position == 'right'"></i>
            </button>
            <button type="submit" class="btn submit_btn ladda-button m-1" :class="[settings.submit.btn_color, settings.submit.btn_size]" data-style="expand-right" name="status" value="complete">
                <i class="fas " :class="settings.submit.btn_icon" v-if="settings.submit.btn_icon != 'none' && settings.submit.icon_position == 'left'"></i>
                <span class="btn_text ladda-label"> {{settings.submit.text}} </span>
                <i class="fas " :class="settings.submit.btn_icon" v-if="settings.submit.btn_icon != 'none' && settings.submit.icon_position == 'right'"></i>
            </button>
        </div>
        <div class="row" v-if="isCardFormLayout()">
            <div class="col-md-12 d-flex justify-content-between">
                <button v-show="current_page_number > 0" type="button"
                    class="btn bg-gradient-primary mr-auto"
                    @click="changePage('prev')">
                    <i class="fas fa-arrow-circle-left"></i> {{trans('messages.previous')}}
                </button>
                <button v-show="current_page_number < page_number" type="button"
                    class="btn bg-gradient-primary ml-auto" @click="changePage('next')">
                    {{trans('messages.next')}} <i class="fas fa-arrow-circle-right"></i>
                </button>
            </div>
        </div>
        <div class="alert alert-success mt-5 cursor-pointer" role="alert"
            v-show="is_available_form_editable_url"
            @click="copyLink">
            <h4 class="alert-heading cursor-pointer"
                v-html="trans('messages.form_editable_url')">
            </h4>
            <hr>
            <span id="form_editable_url" class="cursor-pointer"></span>
            <i class="far fa-copy float-right fa-lg cursor-pointer"></i>
        </div>
    </form>
</template>

<script>
    import fieldGenerator from "./FieldGenerator";

    export default{
        props: ['form', 'actionBy'],
        components: {
            fieldGenerator
        },
        data() {
            return {
                schema: [],
                form_parsed: [],
                settings:{},
                conditional_fields:[],
                is_available_form_editable_url: false,
                submitted_data:{},
                token:'',
                form_data_id: '',
                form_custom_attributes : [],
                formFields:[],
                page_number: 0,
                current_page_number:0,
                is_page_active:'page#0',
            };
        },
        mounted() {
            const self = this;
            let rules = {};
            let messages = {};
            self.formFields.forEach(function(field) {
                if(!_.isEmpty(field?.allowed_input?.values)) {
                    let tempRule = {
                        remote: {
                            url: "/validate-input-value",
                            type: "post",
                            data: {
                                field: function() {
                                    return JSON.stringify(field);
                                },
                                field_value: function() {
                                    return $(`#${field.name}`).val();
                                }
                            }
                        }
                    }

                    let tempMsg = {
                        remote: field?.allowed_input?.error_msg || 'This value is not allowed.'
                    }

                    rules = {...rules, ...{[field.name]:tempRule}};
                    messages = {...messages, ...{[field.name]:tempMsg}};
                }
            });

            var validator = $('#show_form').validate({
                ignore: ".note-editor *, .hide",
                rules: rules,
                messages: messages,
                submitHandler: function(form, e) {
                    e.preventDefault();
                    var form_data = $('#show_form').serialize();
                    $("button.submit_btn, button.draft_btn").attr('disabled', 'disabled');
                    var status = $("input[name=status]").val();
                    if (status == 'complete') {
                        $("span.btn_text").text(self.settings.submit.loading_text);
                        var ladda = Ladda.create(document.querySelector('.ladda-button'));
                    } else if (status == 'incomplete') {
                        var ladda = Ladda.create(document.querySelector('.draft_btn'));
                    }

                    ladda.start();

                    if(typeof mpfg_form_submitted === "function"){
                        var form_array_data = $('#show_form').serializeArray();
                        mpfg_form_submitted(self.form_parsed.id, form_array_data);
                    }

                    let url = '/form-data/' + self.form_parsed.id + '?token=' + self.token + '&form_data_id=' + self.form_data_id;
                    if (self.actionBy.length && self.actionBy == 'admin') {
                        url = '/update/'+self.form_parsed.id+'/data/'+self.form_data_id;
                    }

                    axios
                        .post(url, {form_data})
                        .then(function(response) {
                            //set token & form data id for form
                            if (status == 'incomplete') {
                                self.token = response.data.notification.token;
                                self.form_data_id = response.data.notification.form_data_id;
                            }

                            //remove disabled attr.
                            $("button.submit_btn, button.draft_btn").removeAttr("disabled");
                            //set submit text to submit btn
                            $("span.btn_text").text(self.settings.submit.text);

                            ladda.stop();
                            window.onbeforeunload = null;

                            if (response.data.success == true) {

                                //if notification action is redirect & status is compelete, redirect user to given url
                                if ((response.data.notification.post_submit_action === 'redirect') && (status == 'complete')) {
                                    toastr.success(response.data.notification.success_msg);
                                    window.parent.location = response.data.notification.redirect_url;
                                }

                                //if submit action is on same page & status is complete, replace url to form view url after 10 sec
                                if (status == 'complete' && !_.includes(['redirect'], response.data.notification.post_submit_action)) {
                                    $('.card-body').html(response.data.notification.submission_msg);
                                    if (response.data.notification.qr_code_text) {
                                        try {
                                            let qr_option = {
                                                title: self.form_parsed.name,
                                                titleFont: "bold 16px Arial",
                                                titleColor: "#1b294b",
                                                titleBackgroundColor: "#ffffff",
                                                titleHeight: 20,
                                                titleTop: 0,
                                                text: response.data.notification.qr_code_text,
                                                margin: 0,
                                                width: 200,
                                                height: 200,
                                                quietZone: 20,
                                                colorDark: "#1b294b",
                                                colorLight: "#ffffffff",
                                                correctLevel : QRCode.CorrectLevel.L
                                            }
                                            let qr_code = new QRCode(document.getElementById("qrcode"), qr_option);
                                            $('#qrcode').find('canvas').attr('id', 'canvas');
                                            let canvas = document.getElementById('canvas');
                                            $('#download_qrcode').attr('href', canvas.toDataURL());
                                        } catch (error) {
                                            $('#qrcode, #download_qrcode').hide();
                                        }
                                    }
                                }

                                //if status is incomplete display form editable url
                                if (status == 'incomplete') {
                                    toastr.success(response.data.notification.success_msg);
                                    if (response.data.notification.form_editable_url) {
                                        self.is_available_form_editable_url = true;
                                        $('span#form_editable_url').text(response.data.notification.form_editable_url);
                                    } else {
                                        location.reload();
                                    }
                                }
                            } else {
                                toastr.error(response.data.msg);
                            }
                        })
                        .catch(function(error) {                            
                            toastr.error(error);
                        });
                }
            });
            //show aleart before page reloading
            var form_obj = $('form#show_form');
            var orig_forn_data = form_obj.serialize();
            window.onbeforeunload = function() {
                if($('form#show_form').length == 1){
                    if (form_obj.serialize() != orig_forn_data) {
                        return 'Are you sure?';
                    }
                }
            }
        },
        created() {
            this.form_parsed = JSON.parse(this.form);
            this.schema = this.form_parsed.schema.form;
            this.settings = this.form_parsed.schema.settings;
            this.conditional_fields = this.form_parsed.schema.conditional_fields;
            this.form_custom_attributes = this.form_parsed.schema.form_attributes;
            if (!_.isEmpty(this.form_parsed.data)) {
                this.submitted_data = this.form_parsed.data[0].data;
                this.token = this.form_parsed.data[0].token;
                this.form_data_id = this.form_parsed.data[0].id;
            }
            this.getUrlParameters();
            this.customizeFieldsArrayForLayout();
            this.$eventBus.$on('callApplyConditions', (data) => {
                this.applyConditions();
            });

            //if page break enabled, set layout 'card_form'
            if (
                this.form_parsed.schema.contains_page_break &&
                this.form_parsed.schema.contains_page_break
            ) {
                this.settings.layout = 'card_form';
            }
        },
        beforeDestroy() {
            this.$eventBus.$off('callApplyConditions');
        },
        methods: {
            applyConditions(){
                var schema = this.schema;
                var is_condition_satisfied = true;
                _.forEach(this.conditional_fields, function(conditional_field) {
                    _.forEach(conditional_field.conditions, function(element) {
                        if (!_.isNull(element.condition)) {
                            //find element as condition is present & if present check its type and find it value
                           var index = schema.findIndex(field => field.name === element.condition);
                           var form_element_value = '';
                           var logical_operator = element?.logical_operator || 'AND';
                            if (schema[index].type === 'radio') {

                               form_element_value = $('input[name='+element.condition+']:checked').val();

                            } else if (schema[index].type === 'checkbox') {

                                var checked_value = [];

                                $('input[name='+'"'+element.condition+'[]"'+']:checked').each(function() {
                                    checked_value.push($(this).val());
                                });

                                if (_.includes(checked_value, element.value)) {
                                    form_element_value = element.value;
                                }

                            } else if (schema[index].type === 'dropdown' && schema[index].multiselect) {

                                var selected_value = $('select[name='+'"'+element.condition+'[]"'+']').val();

                                if (_.includes(selected_value, element.value)) {
                                    form_element_value = element.value;
                                }

                            } else if(schema[index].type === 'calendar') {
                                form_element_value = $('input[name='+element.condition+']').val();
                            } else if(schema[index].type === 'terms_and_condition') {
                                form_element_value = $('input[name='+element.condition+']').is(':checked') ? 'true' : 'false';
                            } else if(schema[index].type === 'switch') {
                                form_element_value = $('input[name='+element.condition+']').is(':checked') ? '1' : '0';
                            } else {
                                form_element_value = document.getElementById(element.condition).value;
                            }
                            
                            //check if condition_satisfied or not
                            if(logical_operator == 'AND') {
                                if (element.operator == '==') {
                                    if (form_element_value == element.value) {
                                        is_condition_satisfied = true && is_condition_satisfied;
                                    } else {
                                        is_condition_satisfied = false;
                                    }
                                } else if (element.operator == '!=') {
                                    if (form_element_value != element.value) {
                                        is_condition_satisfied = true && is_condition_satisfied;
                                    } else {
                                        is_condition_satisfied = false;
                                    }
                                } else if (element.operator == 'empty') {
                                    if (form_element_value.length == 0) {
                                        is_condition_satisfied = true && is_condition_satisfied;
                                    } else {
                                        is_condition_satisfied = false;
                                    }
                                } else if (element.operator == 'not_empty') {
                                    if (form_element_value.length > 0) {
                                        is_condition_satisfied = true && is_condition_satisfied;
                                    } else {
                                        is_condition_satisfied = false;
                                    }
                                }
                            } else {
                                if (element.operator == '==') {
                                    is_condition_satisfied = (form_element_value == element.value) || is_condition_satisfied;
                                } else if (element.operator == '!=') {
                                    is_condition_satisfied = (form_element_value != element.value) || is_condition_satisfied;
                                } else if (element.operator == 'empty') {
                                    is_condition_satisfied = (form_element_value.length == 0) || is_condition_satisfied;
                                } else if (element.operator == 'not_empty') {
                                    is_condition_satisfied = (form_element_value.length > 0) || is_condition_satisfied;
                                }
                            }
                        }

                    });
                    //check if element is exist or not in schema
                    var index = schema.findIndex(element => element.name === conditional_field.element);
                    var action = conditional_field.action;
                    //if element exist then toggle conditional class
                    if (index !== -1) {
                        if (is_condition_satisfied) {
                            schema[index].conditional_class = conditional_field.action;
                        } else {
                            action = (conditional_field.action === 'show') ? 'hide' : 'show';
                            schema[index].conditional_class = (conditional_field.action === 'show') ? 'hide' : 'show'
                        }
                        is_condition_satisfied = true;

                        if(action == 'hide') {
                            if(schema[index]['type'] == 'radio') {
                                $(`input[name="${schema[index]['name']}"]`).prop('checked',false).trigger("change");
                            } else if(schema[index]['type'] == 'checkbox') {
                                $(`input[name="${schema[index]['name']}"]:checkbox`).prop('checked',false).trigger("change");
                            } else if(schema[index].type === 'dropdown' && schema[index].multiselect) {
                                $(`select[name="${schema[index]['name']}"]`).val('').trigger("change");
                            } else if(schema[index].type === 'terms_and_condition' || schema[index].type === 'switch') {
                                $(`input[name="${schema[index]['name']}"]`).prop('checked',false).trigger("change");
                            } else {
                                $(`input[name="${schema[index]['name']}"]`).val('').trigger("change");
                            }
                        }
                    }
                });
            },
            getUrlParameters(){
                const self = this;
                let url = window.location.href;
                let parameters = url.split('?')[1];
                if (
                    !_.isEmpty(parameters) &&
                    _.isEmpty(self.submitted_data)
                ) {
                    let query_string = new URLSearchParams(parameters);
                    let data = {};
                    //convert query string to key value object
                    for (let parameter_pair of query_string.entries()) {
                       data[parameter_pair[0]] = parameter_pair[1];
                    }
                    self.submitted_data = data;
                }
            },
            customizeFieldsArrayForLayout(){
                const self = this;
                let tempArrayEls = [];
                if (!_.isEmpty(self.schema)) {
                    let maxIndex = self.schema.length - 1;
                    _.forEach(self.schema, function(element, index){
                        element['page_num'] = `page#${self.page_number}`;
                        tempArrayEls.push(element);
                        let nextEl = self.schema[index+1];//find next field
                        //increase page num if page break exists & not consecutive page break
                        if (
                            _.includes(['page_break'], element.type) &&
                            index > 0 //remove first index page break
                            && nextEl &&
                            !_.includes(['page_break'], nextEl.type)
                        ) {
                            self.page_number += 1;
                        }
                        //if layout is card.. & does not contain page break, insert one
                        if(
                            _.includes(['card_form'], self.settings.layout) &&
                            !self.form_parsed.schema.contains_page_break &&
                            (index < maxIndex)
                        ) {
                            self.page_number += 1;
                        }
                    });
                }
                self.formFields = tempArrayEls;
            },
            isSubmitVisible() {
                const self = this;
                let is_visible = true;
                if (self.isCardFormLayout() && (self.current_page_number < self.page_number)) {
                    is_visible = false;
                }
                return is_visible;
            },
            isCardFormLayout() {
                const self = this;
                if (
                    self.settings.layout && 
                    _.includes(['card_form'], self.settings.layout)
                ) {
                    return true
                }
                return false;
            },
            togglePageVisibility(page_num) {
                const self = this;

                if (!self.isCardFormLayout()) return true;

                if (self.isCardFormLayout() && _.includes([self.is_page_active],page_num)) return true;

                return false;
            },
            changePage(move) {
                const self = this;
                if (move == 'next') {
                    //validate fields before proceeding
                    let pageEles = self.formFields
                                    .filter((el) => el.page_num == `page#${self.current_page_number}`);
                    let is_page_valid = true; //default true

                    for(let el of pageEles){
                        if (!_.includes(['page_break'], el.type)) {
                            is_page_valid = self.validateElement(el);
                            if(!is_page_valid) break;
                        }
                    }

                    //if page's field validated, move to next page
                    if (
                        is_page_valid &&
                        (self.current_page_number < self.page_number)
                    ) {
                        self.current_page_number += 1;
                        self.is_page_active = `page#${self.current_page_number}`;
                    }
                } else if (move == 'prev') {
                    if (self.current_page_number != 0) {
                        self.current_page_number -= 1;
                        self.is_page_active = `page#${self.current_page_number}`;
                    }
                }
            },
            validateElement(element) {
                const self = this;
                let element_name = element.name;
                let is_element_valid = true;
                if (
                    _.includes(['checkbox', 'file_upload'], element.type) ||
                    (element.type == 'dropdown' && element.multiselect === true)
                ) {
                    element_name = element_name + '[]';
                }

                if (
                    !_.includes(['heading', 'hr', 'html_text'], element.type) &&
                    $('[name="' + element_name + '"]').rules()
                ) {
                    is_element_valid =  $('[name="' + element_name + '"]').valid();
                }
                
                return is_element_valid;
            },
            copyLink(){
                const self = this;
                let textarea = document.createElement('textarea');
                textarea.innerHTML = $("#form_editable_url").text();
                document.body.appendChild(textarea);
                textarea.select();
                textarea.setSelectionRange(0, 99999);
                document.execCommand("copy");
                document.body.removeChild(textarea);
                Swal.fire(self.trans('messages.link_copied'));
            }
        }
    }
</script>