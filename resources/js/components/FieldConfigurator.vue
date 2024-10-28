<template>
    <!-- Element Configurator -->
    <div class="card configurator_height">

        <div class="card-header">
            <h5><i class="fas fa-cog"></i> {{element.label}}</h5>
        </div>

        <div class="card-body">
            <div class="form-group"
                v-if="!_.includes(['heading', 'hr', 'html_text'], element.type)">
                <label>
                    {{trans('messages.field_label')}}
                    <span class="error">*</span>
                </label>
                <input type="text" class="form-control form-control-sm" 
                    v-model="element.label">
            </div>

            <div class="form-group">
                <label>
                    {{trans('messages.field_name')}}
                    <span class="error">*</span>
                </label>
                <input type="text" class="form-control form-control-sm" v-model="element.name" @change="checkDuplicateFieldName(element.name)" onkeypress="return event.charCode != 32">
                <div id="field_name_error"></div>
            </div>

            <div class="form-group" v-if="!_.includes(['signature'], element.type)">
                <label>{{trans('messages.column_width')}}</label>
                <select class="form-control-sm form-control" v-model="element.col">
                    <option v-for="(width, index) in columns_width"
                        :value="width">
                        {{width}}
                    </option>
                </select>
            </div>

            <!-- iframe -->
            <div class="mb-1" v-if="_.includes(['iframe', 'youtube'], element.type)">
                <div class="mb-1">
                    <label>
                        <span v-if="_.includes(['iframe'], element.type)">
                            {{trans('messages.frame_url')}}
                        </span>
                        <span v-if="_.includes(['youtube'], element.type)">
                            {{trans('messages.youtube_video_url')}}
                        </span>
                    </label>
                    <input type="url" class="form-control" 
                    v-model="element.iframe_url">
                    <div class="form-text"
                        v-if="_.includes(['iframe'], element.type)">
                        {{trans('messages.frame_url_help_text')}}
                    </div>
                </div>
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
            <!-- /iframe -->
            <!-- pdf-uploader -->
            <div v-if="_.includes(['pdf'], element.type)">
                <pdf-uploader
                    :element="element">
                </pdf-uploader>
            </div>

            <!-- countdown -->
            <div v-if="_.includes(['countdown'], element.type)">
                <div class="mb-1">
                    <label>
                        {{trans('messages.seconds')}}
                    </label>
                    <input type="number" class="form-control" v-model="element.seconds"
                        @change="reinitializeElement">
                </div>
                <div class="mb-1">
                    <label>
                        {{trans('messages.minutes')}}
                    </label>
                    <input type="number" class="form-control" v-model="element.minutes"
                        @change="reinitializeElement">
                </div>
                <div class="mb-1">
                    <label>
                        {{trans('messages.hours')}}
                    </label>
                    <input type="number" class="form-control" v-model="element.hours"
                        @change="reinitializeElement">
                </div>
            </div>
            <div v-if="_.includes(['countdown'], element.type)">
                <div class="form-check mb-1">
                    <input type="checkbox" class="form-check-input" v-model="element.labels_format"
                        @change="reinitializeElement">
                    <label class="form-check-label">
                        {{trans('messages.display_labels')}}
                    </label>
                </div>
                <div class="mb-1"
                    v-show="!element.labels_format">
                    <label>
                        {{trans('messages.size')}}
                    </label>
                    <select class="form-control" v-model="element.size"
                        @change="reinitializeElement">
                        <option value="xs">
                            {{trans('messages.extra_small')}}
                        </option>
                        <option value="sm">
                            {{trans('messages.small')}}
                        </option>
                        <option value="md">
                            {{trans('messages.medium')}}
                        </option>
                        <option value="lg">
                            {{trans('messages.large')}}
                        </option>
                        <option value="xl">
                            {{trans('messages.extra_large')}}
                        </option>
                    </select>
                </div>
                <div class="mb-1">
                    <label for="countdown_border_color">
                        {{trans('messages.border_color')}}
                    </label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" 
                            id="countdown_border_color" 
                            v-model="element.border_color">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <input type="color" v-model="element.border_color"
                                    @change="reinitializeElement">
                            </span>
                        </div>
                    </div>
                </div>
                <div class="mb-1">
                    <label for="countdown_font_color">
                        {{trans('messages.font_color')}}
                    </label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" 
                            id="countdown_font_color" 
                            v-model="element.font_color">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <input type="color" v-model="element.font_color"
                                    @change="reinitializeElement">
                            </span>
                        </div>
                    </div>
                </div>
                <div class="mb-1">
                    <label for="countdown_bg_color">
                        {{trans('messages.background_color')}}
                    </label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" 
                            id="countdown_bg_color" 
                            v-model="element.bg_color">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <input type="color" v-model="element.bg_color"
                                    @change="reinitializeElement">
                            </span>
                        </div>
                    </div>
                </div>
                <div class="mb-1"
                    v-show="!element.labels_format">
                    <label for="countdown_time_separator">
                        {{trans('messages.time_separator')}}
                    </label>
                    <select id="countdown_time_separator" v-model="element.time_separator"
                        @change="reinitializeElement" class="form-control">
                        <option value=":">:</option>
                        <option value="/">/</option>
                        <option value="-">-</option>
                    </select>
                </div>
                <div class="mb-1">
                    <label for="countdown_display_format">
                        {{trans('messages.display_format')}}
                        <i class="fa fa-info-circle text-success"
                            data-html="true"
                            data-toggle="tooltip"
                            :title="trans('messages.countdown_display_format_tooltip')">
                        </i>
                    </label>
                    <select id="countdown_display_format" v-model="element.display_format"
                        @change="reinitializeElement" class="form-control">
                        <option value="HMS" v>HMS</option>
                        <option value="MS" v>MS</option>
                        <option value="DHMS" v>DHMS</option>
                        <option value="ODHMS" v>ODHMS</option>
                        <option value="YODHMS" v>YODHMS</option>
                    </select>
                </div>
            </div>
            <!-- /countdown -->
            <div class="form-group" v-if="_.includes(['text'], element.type)">
                <label>{{trans('messages.type')}}</label>
                <select class="form-control form-control-sm" v-model="element.subtype">
                    <option value="text">{{trans('messages.text')}}</option>
                    <option value="number">{{trans('messages.number')}}</option>
                    <option value="email">{{trans('messages.email')}}</option>
                    <option value="password">{{trans('messages.password')}}</option>
                    <option value="color">{{trans('messages.color')}}</option>
                </select>
            </div>

            <div class="form-group" v-if="_.includes(['range'], element.type)">
                <label>
                    {{trans('messages.min_value')}}
                </label>
                <input type="number" class="form-control form-control-sm" min="0" v-model="element.min" @change="updateAttribute('range')">

                <label>{{trans('messages.max_value')}}</label>
                <input type="number" class="form-control form-control-sm" min="0" v-model="element.max" @change="updateAttribute('range')">

                <label>{{trans('messages.step')}}</label>
                <input type="number" class="form-control form-control-sm" min="0" v-model="element.step" @change="updateAttribute('range')">

                <label>{{trans('messages.data_orientation')}}</label>
                <select class="form-control form-control-sm" v-model="element.data_orientation"@change="updateAttribute('range')">
                    <option value="horizontal">
                        {{trans('messages.horizontal')}}
                    </option>
                    <option value="vertical">
                        {{trans('messages.vertical')}}
                    </option>
                </select>
            </div>

            <div class="form-group"
                v-if="_.includes(['dropdown'], element.type)">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="multiselect" v-model="element.multiselect">
                    <label class="form-check-label" for="multiselect">
                        {{trans('messages.allow_select_multi_value')}}
                    </label>
                </div>
            </div>
            
            <div class="form-group" 
                v-if="_.includes(['text', 'textarea', 'text_editor'], element.type)">
                <label>{{trans('messages.placeholder')}}</label>
                <input type="text" class="form-control form-control-sm" 
                    v-model="element.placeholder">
            </div>
            
            <div class="form-group"
                v-if="!_.includes(['heading', 'terms_and_condition', 'hr', 'html_text', 'rating', 'youtube', 'iframe', 'pdf', 'countdown'], element.type)">
                <label>{{trans('messages.help_text')}}</label>
                <input type="text" class="form-control form-control-sm" 
                    v-model="element.help_text">
            </div>

            <div class="form-group" v-if="element.type == 'text_editor'">
                <label>{{trans('messages.editior_height')}}</label>
                <input type="number" class="form-control form-control-sm" v-model="element.editor_height" min="1">
                <small class="form-text">
                    {{trans('messages.editior_height_help_text')}}
                </small>
            </div>

            <div v-if="element.type == 'file_upload'">
                <div class="form-group">
                    <label>{{trans('messages.upload_text')}}</label>
                    <input type="text" v-model="element.upload_text" class="form-control form-control-sm">
                </div>

                <div class="form-group">
                    <label>{{trans('messages.no_files_can_be_uploaded')}}</label>
                    <input type="number" v-model="element.no_of_files" class="form-control form-control-sm" min="1">
                    <small class="form-text">
                        {{trans('messages.upload_file_limit_help_text')}}
                    </small>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="send_as_attachment" v-model="element.send_as_email_attachment">
                    <label class="form-check-label" for="send_as_attachment">
                        {{trans('messages.send_as_email_attachment')}}
                    </label>
                </div><br><br>

                <div class="form-group">
                    <label>
                        {{trans('messages.file_size_limit')}}
                    </label>
                    <input type="text" class="form-control form-control-sm" v-model="element.file_size_limit" :placeholder="trans('messages.file_size_limit_help')">
                    <small class="form-text">
                        {{trans('messages.file_size_limit_help')}}
                    </small>
                </div>
                
                <div class="form-group">
                    <label>
                        {{trans('messages.allowed_file')}}
                    </label> <br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="all_types" name="file_type" :value="'all'" v-model="element.file_type" checked>
                        <label class="form-check-label" for="all_types">
                            {{trans('messages.all_types')}}
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" name="file_type"
                            type="radio" id="allowed_types"
                            :value="'Specify_type'" v-model="element.file_type">
                        <label class="form-check-label" for="allowed_types">
                            {{trans('messages.specify_file_type')}}
                        </label>
                    </div>
                </div>

                <div class="form-group" v-show="element.file_type === 'Specify_type'">
                    <label>
                        {{trans('messages.add_file_type')}}
                    </label>
                    <input type="text" class="form-control form-control-sm" v-model="element.allowed_file_type" :placeholder="trans('messages.enter_file_type')">
                    <small class="form-text">
                        {{trans('messages.add_file_type_help_text')}}
                    </small>
                </div>
            </div>

            <div class="form-group" 
                v-if="_.includes(['text', 'calendar', 'dropdown'], element.type)">
                <label>
                    {{trans('messages.sizing')}}
                </label>
                <select class="form-control form-control-sm" v-model="element.size">
                    <option value="">
                        {{trans('messages.default')}}
                    </option>
                    <option value="custom-select-sm">
                        {{trans('messages.small')}}
                    </option>
                    <option value="custom-select-lg">
                        {{trans('messages.large')}}
                    </option>
                </select>
            </div>

            <!-- switch size -->
            <div class="form-group" v-if="element.type == 'switch'">
                <label>
                    {{trans('messages.sizing')}}
                </label>
                <select class="form-control form-control-sm" v-model="element.size">
                    <option value="switch">
                        {{trans('messages.default')}}
                    </option>
                    <option value="switch-sm">
                        {{trans('messages.small')}}
                    </option>
                    <option value="switch-lg">
                        {{trans('messages.large')}}
                    </option>
                </select>
            </div>
            <!-- calendar -->
            <div v-if="_.includes(['calendar'], element.type)">
                <div class="form-group">
                    <label>
                        {{trans('messages.start_date')}}
                    </label>
                    <select class="form-control form-control-sm" v-model="element.start_date">
                        <option value="none">
                            {{trans('messages.none')}}
                        </option>
                        <option value="today">
                            {{trans('messages.today')}}
                        </option>
                        <option value="current_month">
                            {{trans('messages.current_month')}}
                        </option>
                        <option value="current_year">
                            {{trans('messages.current_year')}}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>
                        {{trans('messages.end_date')}}
                    </label>
                    <select class="form-control form-control-sm" v-model="element.end_date">
                        <option value="none">
                            {{trans('messages.none')}}
                        </option>
                        <option value="today">
                            {{trans('messages.today')}}
                        </option>
                        <option value="current_month">
                            {{trans('messages.current_month')}}
                        </option>
                        <option value="current_year">
                            {{trans('messages.current_year')}}
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label>
                        {{trans('messages.date_format')}}
                    </label>
                    <input type="text" v-model="element.format" class="form-control form-control-sm">
                    <small class="form-text">
                        <span v-html="trans('messages.date_format_help_text')">
                        </span>
                    </small>
                </div>

                <div class="form-group">
                    <label>
                        {{trans('messages.disabled_days')}}
                    </label> <br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="sunday" value="0" v-model="element.disabled_days">
                        <label class="form-check-label" for="sunday">
                            {{trans('messages.sunday')}}
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="monday" value="1" v-model="element.disabled_days">
                        <label class="form-check-label" for="monday">
                            {{trans('messages.monday')}}
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="tuesday" value="2" v-model="element.disabled_days">
                        <label class="form-check-label" for="tuesday">
                            {{trans('messages.tuesday')}}
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="wednesday" value="3" v-model="element.disabled_days">
                        <label class="form-check-label" for="wednesday">
                            {{trans('messages.wednesday')}}
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="thursday" value="4" v-model="element.disabled_days">
                        <label class="form-check-label" for="thursday">
                            {{trans('messages.thursday')}}
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="friday" value="5" v-model="element.disabled_days">
                        <label class="form-check-label" for="friday">
                            {{trans('messages.friday')}}
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="saturday" value="6" v-model="element.disabled_days">
                        <label class="form-check-label" for="saturday">
                            {{trans('messages.saturday')}}
                        </label>
                    </div>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="enable_time_picker" v-model="element.enable_time_picker">
                    <label class="form-check-label" for="enable_time_picker">
                        {{trans('messages.enable_time_picker')}}
                    </label>
                </div>

                <div class="form-group" v-if="element.enable_time_picker">
                    <label>{{trans('messages.time_format')}}</label>
                    <select class="form-control form-control-sm" v-model="element.time_format">
                        <option value="12">
                            {{trans('messages.12_hour')}}
                        </option>
                        <option value="24">
                            {{trans('messages.24_hour')}}
                        </option>
                    </select>
                </div>

                <div class="form-check form-check-inline" v-if="element.enable_time_picker">
                    <input class="form-check-input" type="checkbox" id="time_picker_inline" v-model="element.time_picker_inline">
                    <label class="form-check-label" for="time_picker_inline">
                        {{trans('messages.show_calendar_sidebyside')}}
                    </label>
                </div>
            </div>

            <div class="form-group" 
                v-if="!_.includes(['heading', 'hr', 'html_text', 'youtube', 'iframe', 'pdf', 'countdown'], element.type)">
                <div class="form-check">
                    <input class="form-check-input" 
                        type="checkbox" 
                        v-model="element.required" id="requiredCheck">
                    <label class="form-check-label" for="requiredCheck">
                        {{trans('messages.is_required')}}
                    </label>
                </div>
            </div>

            <div class="form-group" v-if="element.required">
                <label>
                    {{trans('messages.error_msg_for_required')}}
                </label>
                <input type="text" class="form-control form-control-sm" 
                v-model="element.required_error_msg">
            </div>

            <div class="form-group"
                v-if="!_.includes(['heading', 'range', 'calendar', 'file_upload', 'radio', 'text_editor', 'terms_and_condition', 'hr', 'html_text', 'rating', 'switch', 'signature', 'youtube', 'iframe', 'pdf', 'countdown'], element.type)">
                <label>
                    {{trans('messages.validation')}}
                </label>
                <select class="form-control form-control-sm"
                    multiple v-model="element.validations"
                    :id="'validations_' + element.name">
                    <option v-for="validation in validations" 
                        :value="{'rule': validation.rule, 'error_msg': ''}" 
                        v-if="_.includes(validation.applies_to, element.type)">
                        {{validation.display}}
                    </option>
                </select>
            </div>

            <div class="form-group" v-for="validation in element.validations">

                <div class="form-group" v-if="validation.rule === 'minlength'">
                    <label>
                        {{trans('messages.min_length_value')}}
                    </label>
                    <input type="number" class="form-control form-control-sm" :placeholder="trans('messages.enter_min_value')" min="0" v-model="validation['value']">
                </div>

                <div class="form-group" v-if="validation.rule === 'maxlength'">
                    <label>
                        {{trans('messages.max_length_value')}}
                    </label>
                    <input type="number" class="form-control form-control-sm" :placeholder="trans('messages.enter_max_value')" min="0" v-model="validation['value']">
                </div>

                <label>
                    {{trans('messages.error_msg_for')}} {{validation.rule}}
                </label>
                <input type="text" class="form-control form-control-sm"  v-model="validation['error_msg']">
            </div>

            <div class="form-group" 
                v-if="element.type == 'textarea'">
                <label>{{trans('messages.no_of_rows')}}</label>
                <input type="number" class="form-control form-control-sm" 
                    v-model="element.rows" min="0">
            </div>
            
            <div class="form-group"
                v-if="element.type == 'textarea'">
                <label>{{trans('messages.no_of_cols')}}</label>
                <input type="number" class="form-control form-control-sm" 
                    v-model="element.columns" min="0">
            </div>

            <div class="form-group" v-if="!_.includes(['radio', 'checkbox', 'heading', 'range', 'file_upload', 'text_editor', 'terms_and_condition', 'hr', 'html_text', 'rating', 'switch', 'signature', 'youtube', 'iframe', 'pdf', 'countdown'], element.type)">
                <label for="icon_prefix">
                    {{trans('messages.icon_prefix')}}
                </label>
                <select id="icon_prefix" class="form-control form-control-sm" v-model="element.prefix_icon">
                    <option value="none">None</option>
                    <option v-for="(icon, index) in getIcons()" :value="icon.c" :key="index">
                        {{icon.l}}
                    </option>
                </select>
            </div>

            <div class="form-group" v-if="!_.includes(['radio', 'checkbox', 'heading', 'range', 'file_upload', 'text_editor', 'terms_and_condition', 'hr', 'html_text', 'rating', 'switch', 'signature', 'youtube', 'iframe', 'pdf', 'countdown'], element.type)">
                <label for="icon_suffix">
                    {{trans('messages.icon_suffix')}}
                </label>
                <select id="icon_suffix" class="form-control form-control-sm" v-model="element.suffix_icon">
                    <option :value="'none'">None</option>
                    <option v-for="(icon, index) in getIcons()" :value="icon.c" :key="index">
                        {{icon.l}}
                    </option>
                </select>
            </div>

            <div class="form-group" 
                v-if="_.includes(['radio', 'checkbox', 'dropdown'], element.type)">
                <label>{{trans('messages.options')}}</label>
                <textarea class="form-control form-control-sm" 
                    v-model="element.options"></textarea>
                <small class="form-text">
                    {{trans('messages.enter_one_option_per_line')}}
                </small>
            </div>

            <div class="row mb-1"
                v-if="_.includes(['radio', 'checkbox'], element.type)">
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="spread_to_col" v-model="element.spread_to_col.enable">
                            <label class="custom-control-label" for="spread_to_col">
                                {{trans('messages.spread_to_col')}}
                            </label>
                        </div>
                      </div>
                </div>
                <div class="col-md-12"
                    v-if="element.spread_to_col.enable">
                    <div class="input-group">
                        <input type="number" name="spread_to_column" class="form-control" min="1" max="4" v-model="element.spread_to_col.column"
                        required>
                        <div class="input-group-append">
                            <span class="input-group-text">
                                {{trans('messages.cols')}}
                            </span>
                        </div>
                    </div>
                </div>
                <small class="form-text text-muted">
                    {{trans('messages.spread_to_col_help_text')}}
                </small>
            </div>
            
            <div class="form-group" 
                v-if="_.includes(['heading'], element.type)">
                <label>{{trans('messages.type')}}</label>
                <select class="form-control form-control-sm" v-model="element.tag">
                    <option value="h1">
                        H1
                    </option>
                    <option value="h2">
                        H2
                    </option>
                    <option value="h3">
                        H3
                    </option>
                    <option value="h4">
                        H4
                    </option>
                    <option value="h5">
                        H5
                    </option>
                    <option value="h6">
                        H6
                    </option>
                    <option value="p">
                        {{trans('messages.paragraphp')}}
                    </option>
                </select>
            </div>

            <div class="form-group" 
                v-if="_.includes(['heading'], element.type)">
                <label>{{trans('messages.content')}}</label>
                <textarea class="form-control form-control-sm" 
                    v-model="element.content"></textarea>
            </div>

            <div class="form-group"
                v-if="_.includes(['heading'], element.type)">
                <label for="heading_text_color">
                    {{trans('messages.text_color')}}
                </label>

                <div class="input-group">
                    <input type="text" class="form-control" 
                        id="heading_text_color" 
                        v-model="element.text_color">
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <input type="color" v-model="element.text_color">
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-group" v-if="_.includes(['terms_and_condition'], element.type)">
                <label>{{trans('messages.link')}}</label>
                <input type="text" class="form-control form-control-sm" v-model="element.link" :placeholder="trans('messages.enter_link_for_label')">
            </div>
            
            <div class="form-group" v-if="!_.includes(['range', 'text_editor', 'hr', 'rating', 'switch'], element.type)">
                <label>{{trans('messages.add_custom_class')}}</label>
                <i class="fa fa-info-circle text-success" data-placement="top" data-toggle="tooltip" data-html="true" :title="trans('messages.custom_class_tooltip')">
                </i>
                <input type="text" class="form-control form-control-sm" 
                v-model="element.custom_class" aria-describedby="customClass">
            </div>
            <!-- allowed input values -->
            <template v-if="_.includes(['text'], element.type) && _.includes(['text', 'email', 'number'], element.subtype)">
                <div class="mb-2">
                    <label class="allowed_values">
                        {{trans('messages.allowed_values')}}
                        <i class="fa fa-info-circle"
                            :title="trans('messages.values_allowed_tooltip')"
                            data-toggle="tooltip"></i>
                    </label>
                    <textarea id="allowed_values" class="form-control" 
                        v-model="element.allowed_input.values">
                    </textarea>
                    <small class="text-muted">{{ trans('messages.enter_allowed_value_per_line') }}</small>
                </div>
                <div class="mb-2">
                    <label>
                        {{trans('messages.error_msg_for_not_allowed_value')}}
                    </label>
                    <input type="text" class="form-control form-control-sm" v-model="element.allowed_input.error_msg">
                </div>
            </template>
            <!-- /allowed input values -->
            <!-- popover help text -->
            <div class="form-group mt-3" 
                v-if="!_.includes(['countdown', 'pdf', 'iframe', 'youtube', 'page_break', 'html_text', 'hr', 'heading'], element.type)">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="enable_popover_help_text" v-model="element.popover_help_text.enable" @change="initializePopoverEditor">
                    <label class="custom-control-label" for="enable_popover_help_text">
                        {{trans('messages.enable_popover_help_text')}}
                    </label>
                </div>
            </div>
            <div class="form-group" v-show="!_.includes(['countdown', 'pdf', 'iframe', 'youtube', 'page_break', 'html_text', 'hr', 'heading'], element.type) && element.popover_help_text.enable">
                <label>{{trans('messages.popover_help_text')}}</label>
                <textarea id="popover_help_text" class="form-control summer_note" v-model="element.popover_help_text.content"></textarea>
            </div>
            <!-- /popover help text -->
            <div class="form-group" v-if="element.type == 'hr'">
                <label>{{trans('messages.padding_top')}}</label>
                <div class="input-group mb-2">
                    <input type="number" class="form-control" v-model="element.padding_top" aria-describedby="padding_top">
                    <div class="input-group-append">
                        <span class="input-group-text" id="padding_top">
                            px
                        </span>
                    </div>
                </div>

                <label>{{trans('messages.padding_bottom')}}</label>
                <div class="input-group mb-2">
                    <input type="number" class="form-control" v-model="element.padding_bottom" aria-describedby="padding_bottom">
                    <div class="input-group-append">
                        <span class="input-group-text" id="padding_bottom">
                            px
                        </span>
                    </div>
                </div>

                <label>{{trans('messages.border_size')}}</label>
                <div class="input-group mb-2">
                    <input type="number" class="form-control" v-model="element.border_size" aria-describedby="border_size">
                    <div class="input-group-append">
                        <span class="input-group-text" id="border_size">
                            px
                        </span>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label>{{trans('messages.border_type')}}</label>
                        <select class="form-control" v-model="element.border_type">
                            <option value="solid">
                                {{trans('messages.solid')}}
                            </option>
                            <option value="dashed">
                                {{trans('messages.dashed')}}
                            </option>
                            <option value="dotted">
                                {{trans('messages.dotted')}}
                            </option>
                            <option value="double">
                                {{trans('messages.double')}}
                            </option>
                        </select>
                    </div>

                    <div class="col">
                        <label for="border_color">
                            {{trans('messages.border_color')}}
                        </label>
                        <input type="color" class="form-control" id="border_color" v-model="element.border_color">
                    </div>
                </div>
                <div class="row mt-1">
                    <div class="col-sm-12">
                        <label>{{trans('messages.horizontal_space')}}</label>
                        <div class="input-group">
                            <input type="number" class="form-control" v-model="element.horizontal_space">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    px
                                </span>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            {{trans('messages.horizontal_space_help_text')}}
                        </small>
                    </div>
                </div>
            </div>

            <div class="form-group" v-if="element.type == 'html_text'">
                <label>{{trans('messages.html_text')}}</label>
                <textarea id="html_text" class="form-control summer_note" v-model="element.html_text">
            </textarea>
            </div>

            <div v-if="element.type == 'rating'">
                <div class="form-group">
                    <label>{{trans('messages.stars')}}</label>
                    <tool-tip :tooltip="trans('messages.max_star_help_text')"></tool-tip>
                    <input type="number" v-model="element.stars_to_display" class="form-control form-control-sm">
                </div>
                <div class="form-group">
                    <label>{{trans('messages.min_value')}}</label>
                    <tool-tip :tooltip="trans('messages.min_rating_value_help_text')"></tool-tip>
                    <input type="number" v-model="element.min_rating" class="form-control form-control-sm">
                </div>
                <div class="form-group">
                    <label>{{trans('messages.max_value')}}</label>
                    <tool-tip :tooltip="trans('messages.max_rating_value_help_text')"></tool-tip>
                    <input type="number" v-model="element.max_rating" class="form-control form-control-sm">
                </div>
                <div class="form-group">
                    <label>{{trans('messages.step')}}</label>
                    <tool-tip :tooltip="trans('messages.step_value_help_text')"></tool-tip>
                    <input type="number" v-model="element.increment" class="form-control form-control-sm">
                </div>

                <div class="form-group">
                    <label>{{trans('messages.display_type')}}</label>
                    <select v-model="element.direction" class="form-control form-control-sm">
                        <option value="rtl">
                            {{trans('messages.rtl')}}
                        </option>
                        <option value="ltr">
                            {{trans('messages.ltr')}}
                        </option>
                    </select>
                </div>
                <div class="form-group">
                    <label>{{trans('messages.size')}}</label>
                    <select v-model="element.size" class="form-control form-control-sm">
                        <option value="xs">
                            {{trans('messages.extra_small')}}
                        </option>
                        <option value="sm">
                            {{trans('messages.small')}}
                        </option>
                        <option value="md">
                            {{trans('messages.medium')}}
                        </option>
                        <option value="lg">
                            {{trans('messages.large')}}
                        </option>
                        <option value="xl">
                            {{trans('messages.extra_large')}}
                        </option>
                    </select>
                </div>
            </div>
            
            <div v-if="!_.includes(['heading', 'hr', 'html_text', 'rating', 'youtube', 'iframe', 'pdf', 'countdown'], element.type)">
                <div class="form-group" v-if="element.custom_attributes.length">
                    <hr>
                    <label>{{trans('messages.custom_attributes')}}</label>
                </div>
                <div class="row mb-10" v-for="(custom_attribute, index) in element.custom_attributes">
                    <div class="col-md-10">
                        <div class="input-group">
                            <input type="text" class="form-control" v-model="custom_attribute.key" :placeholder="trans('messages.key')">
                            <input type="text" class="form-control" v-model="custom_attribute.value" :placeholder="trans('messages.value')">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm" @click="removeAttribute(index)">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-info" style="margin: 28px;" @click="addAttribute()">
                    <i class="fas fa-plus-circle"></i>
                    {{trans('messages.add_custom_attribute')}}
                </button>
            </div>
            <!-- Edit/Delete btn -->
            <div class="row text-center">
                <div class="col-md-12">
                    <button type="button" class="btn btn-primary m-1" 
                        @click="$emit('toggleConfigurator', {index: index, show: false})">
                        {{trans('messages.close')}}
                    </button>
                    <button type="button" class="btn btn-danger m-1" @click="$emit('deleteElement', index)">
                        <span class="fas fa-trash" aria-hidden="true"></span>
                        {{trans('messages.remove')}}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import PdfUploader from "./Shared/PdfUploader";
	export default {
		props: {
			element: Object,
			index: Number,
            selected_elements: Array
		},
        components: {
            PdfUploader
        },
        mounted(){
            const self = this;
            $(function() {
                if (self.element.type == 'html_text') {
                    $('#html_text').summernote({
                        placeholder: self.trans('messages.jot_down_here'),
                        height: 200,
                        dialogsInBody: true,
                        callbacks: {
                            onChange: function(contents, editable) {
                              self.element.html_text = contents;
                            }
                        }
                    });
                }

                if (!_.includes(['countdown', 'pdf', 'iframe', 'youtube', 'page_break', 'html_text', 'hr', 'heading'], self.element.type) && self.element.popover_help_text.enable) {
                    self.initializePopoverEditor();
                }
            });
        },
		data: function () {
	  		return {
                validations:[],
                columns_width:[
                    'col-md-1',
                    'col-md-2',
                    'col-md-3',
                    'col-md-4',
                    'col-md-5',
                    'col-md-6',
                    'col-md-7',
                    'col-md-8',
                    'col-md-9',
                    'col-md-10',
                    'col-md-11',
                    'col-md-12'
                ]
	  		}
		},
        created() {
            this.validations = validationRules;
            //chck if custom attr is undefined for element, if so add it to element
            if (_.isUndefined(this.element.custom_attributes)) {
                Vue.set(this.element, 'custom_attributes', []);
            }
        },
        methods:{
            addAttribute() {
                const self = this;
                self.element.custom_attributes.push({'key':null, 'value':null});
            },
            removeAttribute(index) {
                this.element.custom_attributes.splice(index, 1);
            },
            checkDuplicateFieldName(field_name) {
                const self = this;
                var curent_element_index = self.index;
                for (let index = 0; index < self.selected_elements.length; index++) {
                    if(_.isEmpty(field_name)) {
                        $("#field_name_error").html('<p id="error" class="error">'+self.trans('messages.this_field_is_required')+'</p>');
                        return false;
                    } else if (index == curent_element_index && (/\s/.test(field_name))) {
                        $("#field_name_error").html('<p id="error" class="error">'+self.trans('messages.field_name_should_nt_have_space')+'</p>');
                        return false;
                    } else if (index !== curent_element_index && (self.selected_elements[index].name == field_name)) {
                        $("#field_name_error").html('<p id="error" class="error">'+self.trans('messages.duplicate_field_name_choose_unique')+'</p>');
                        return false;
                    } else {
                        $("#field_name_error").html("");
                    }
                }
            },
            updateAttribute(type) {
                if (type == 'range') {
                    $('input[type="range"]').rangeslider('update', true);
                }
            },
            getIcons() {
                var icons = this.getFontAwesomeIcons();
                return icons;
            },
            reinitializeElement(){
                const self = this;
                self.reInitializePlugins(self.element);  
            },
            initializePopoverEditor() {
                const self = this;
                $('#popover_help_text').summernote({
                    placeholder: self.trans('messages.write_popover_help_text'),
                    height: 200,
                    dialogsInBody: true,
                    callbacks: {
                        onChange: function(contents, editable) {
                            self.element.popover_help_text.content = contents;
                        }
                    }
                });
            }
        }
	}
</script>
<style scoped>
    label {
        margin-bottom: 0px !important;
    }
    .configurator_height {
        overflow-y: auto;
        max-height: 58vh;
    }
</style>