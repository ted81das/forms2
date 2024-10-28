<template>

    <div class="form-group" v-if="element.type == 'text'" :class="[element.extras.showConfigurator ? 'active_element' : '',  element.conditional_class]" @mouseover="onMouseHover()" @mouseleave="onMouseLeave(element)" >
        
        <label :for="element.name">
            <i class="fas fa-sort handle pointer font_icon_size float-left mr-3" :class="[display_handler]" :title="trans('messages.drag_element_using_icon')"></i>
            <span :style="{'color': settings.color.label}">
                {{element.label}}
            </span>
            <span :style="{'color': settings.color.required_asterisk_color}" v-if="element.required">*</span>
            <i class="fas fa-info-circle cursor-pointer modal_trigger"
                v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable"
                
                :data-target="`#${element.name}_modal`"></i>
        </label>
        <div class="input-group">
            <div class="input-group-prepend" v-if="element.prefix_icon && element.prefix_icon !== 'none'">
                <span class="input-group-text">
                    <i :class="'fas ' + element.prefix_icon"></i>
                </span>
            </div>
                <input :type="element.subtype" class="form-control" 
                    :name="element.name" 
                    :placeholder="element.placeholder" 
                    :class="[element.size, element.custom_class, element.conditional_class]" 
                    :required="element.required && applyValidations" 
                    v-bind="getDynamicallyGeneratedAttributeObj(element.validations, element.custom_attributes)" 
                    :id="element.name"
                    :value="_.get(submitted_data, element.name, '')"
                    :data-msg-required="element.required_error_msg"
                    @change="$emit('apply_conditions')">
            <div class="input-group-append" v-if="element.suffix_icon && element.suffix_icon !== 'none'">
                <span class="input-group-text">
                    <i :class="'fas ' + element.suffix_icon"></i>
                </span>
            </div>
        </div>
        <small class="form-text text-muted" v-if="element.help_text">
            {{element.help_text}}
        </small>
        <popover-help-text-modal
            v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable && element.popover_help_text.content"
            :element="element"
            ></popover-help-text-modal>
    </div>

    <div v-else-if="element.type == 'range'" :class="[element.extras.showConfigurator ? 'active_element' : '',  element.conditional_class, 'mb-4 mt-3']" @mouseover="onMouseHover()" @mouseleave="onMouseLeave(element)" >
        <label :for="element.name">
            <i class="fas fa-sort handle pointer font_icon_size float-left mr-3" :class="[display_handler]" :title="trans('messages.drag_element_using_icon')"></i>
            <span :style="{'color': settings.color.label}">
                {{element.label}}
            </span>
            <span :style="{'color': settings.color.required_asterisk_color}" v-if="element.required">*</span>
            <i class="fas fa-info-circle cursor-pointer modal_trigger"
                v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable"
                
                :data-target="`#${element.name}_modal`"></i>
        </label>
        <div class="row">
            <div class="col-sm-1">{{element.min}}</div>
                <div class="col-sm-10">
                    <input type="range" :name="element.name" :required="element.required && applyValidations" :id="element.name" :min="element.min" :max="element.max" :step="element.step" :data-orientation="element.data_orientation"
                    :value="_.get(submitted_data, element.name, '')"
                    :class="[element.conditional_class]"
                    v-bind="getCustomAttributes(element.custom_attributes)"
                    :data-msg-required="element.required_error_msg"
                    >
                </div>
            <div class="col-sm-1">{{element.max}}</div>
        </div>
        <b>
            <output :class="element.name" style="display: block;text-align:center;" :for="element.name">
                {{_.get(submitted_data, element.name, '')}}
            </output>
        </b>
        <small class="form-text text-muted" v-if="element.help_text">
            {{element.help_text}}
        </small>
        <popover-help-text-modal
            v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable && element.popover_help_text.content"
            :element="element"
            ></popover-help-text-modal>
    </div>

    <div class="form-group" v-else-if="element.type == 'calendar'" :class="[element.extras.showConfigurator ? 'active_element' : '',  element.conditional_class]" @mouseover="onMouseHover()" @mouseleave="onMouseLeave(element)" >
        <label :for="element.name">
            <i class="fas fa-sort handle pointer font_icon_size float-left mr-3" :class="[display_handler]" :title="trans('messages.drag_element_using_icon')"></i>
            <span :style="{'color': settings.color.label}">
                {{element.label}}
            </span>
            <span :style="{'color': settings.color.required_asterisk_color}" v-if="element.required">*</span>
            <i class="fas fa-info-circle cursor-pointer modal_trigger"
                v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable"
                
                :data-target="`#${element.name}_modal`"></i>
        </label>
        <div class="input-group date" :id="element.name" data-target-input="nearest">
            <div class="input-group-prepend"
                :data-target="'#' + element.name"
                data-toggle="datetimepicker"
                v-if="element.prefix_icon && element.prefix_icon !== 'none'">
                <span class="input-group-text">
                    <i :class="'fas ' + element.prefix_icon"></i>
                </span>
            </div>
                <input type="text" 
                    class="form-control datetimepicker-input" 
                    :data-target="'#' + element.name"
                    data-toggle="datetimepicker"
                    :name="element.name"
                    :id="element.name"
                    readonly
                    :class="[element.size, element.custom_class, element.conditional_class]"
                    :required="element.required && applyValidations"
                    :data-defaultDate="_.get(submitted_data, element.name, '')"
                    v-bind="getCustomAttributes(element.custom_attributes)"
                    :data-msg-required="element.required_error_msg"
                    />
                    
            <div class="input-group-append" 
                :data-target="'#' + element.name" 
                data-toggle="datetimepicker" 
                v-if="element.suffix_icon && element.suffix_icon !== 'none'">
            
                <div class="input-group-text">
                    <i :class="'fas ' + element.suffix_icon"></i>
                </div>
            </div>
        </div>
        <small class="form-text text-muted" v-if="element.help_text">
            {{element.help_text}}
        </small>
        <popover-help-text-modal
            v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable && element.popover_help_text.content"
            :element="element"
            ></popover-help-text-modal>
    </div>

    <div class="form-group" v-else-if="element.type == 'textarea'" :class="[element.extras.showConfigurator ? 'active_element' : '',  element.conditional_class]" @mouseover="onMouseHover()" @mouseleave="onMouseLeave(element)" >
        <label :for="element.name">
            <i class="fas fa-sort handle pointer font_icon_size float-left mr-3" :class="[display_handler]" :title="trans('messages.drag_element_using_icon')"></i>
            <span :style="{'color': settings.color.label}">
                {{element.label}}
            </span>
            <span :style="{'color': settings.color.required_asterisk_color}" v-if="element.required">*</span>
            <i class="fas fa-info-circle cursor-pointer modal_trigger"
                v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable"
                
                :data-target="`#${element.name}_modal`"></i>
        </label>
        <div class="input-group">
            <div class="input-group-prepend" v-if="element.prefix_icon && element.prefix_icon !== 'none'">
                <span class="input-group-text">
                    <i :class="'fas ' + element.prefix_icon"></i>
                </span>
            </div>
            <textarea class="form-control"
                :value="_.get(submitted_data, element.name, '')"
                :rows="element.rows"
                :name="element.name"
                :id="element.name"
                :cols="element.columns"
                :placeholder="element.placeholder"
                :class="[element.custom_class, element.conditional_class]"
                :required="element.required && applyValidations"
                v-bind="getDynamicallyGeneratedAttributeObj(element.validations, element.custom_attributes)"
                @change="$emit('apply_conditions')"
                :data-msg-required="element.required_error_msg"
                ></textarea>
            <div class="input-group-append" v-if="element.suffix_icon && element.suffix_icon !== 'none'">
                <span class="input-group-text">
                    <i :class="'fas ' + element.suffix_icon"></i>
                </span>
            </div>
        </div>
        <small class="form-text text-muted" v-if="element.help_text">
            {{element.help_text}}
        </small>
        <popover-help-text-modal
            v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable && element.popover_help_text.content"
            :element="element"
            ></popover-help-text-modal>
    </div>

    <div class="form-group" 
        v-else-if="element.type == 'radio' || element.type == 'checkbox'" :class="[element.extras.showConfigurator ? 'active_element' : '',  element.conditional_class]" @mouseover="onMouseHover()" @mouseleave="onMouseLeave(element)" >
        <label :for="element.name">
            <i class="fas fa-sort handle pointer font_icon_size float-left mr-3" :class="[display_handler]" :title="trans('messages.drag_element_using_icon')"></i>
            <span :style="{'color': settings.color.label}">{{element.label}}</span>
            <span :style="{'color': settings.color.required_asterisk_color}" v-if="element.required">*</span>
            <i class="fas fa-info-circle cursor-pointer modal_trigger"
                v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable"
                
                :data-target="`#${element.name}_modal`"></i>
        </label>
        <div class="row">
            <div :class="[spreadColumnForElement(element)]"
                v-for="(option, index) in element.options.split('\n')">
                <div class="custom-control" :class="[element.type == 'radio' ? 'custom-radio' : 'custom-checkbox']">
                    <input class="custom-control-input" 
                        :type="element.type" 
                        :value="option"
                        v-bind="getDynamicallyGeneratedAttributeObj(element.validations, element.custom_attributes)"
                        :required="element.required && applyValidations"
                        :name="(element.type == 'checkbox' ? element.name + '[]' : element.name)"
                        :id="element.name +'_'+ index"
                        @change="$emit('apply_conditions')"
                        :checked="_.includes(_.get(submitted_data, element.name, ''), option)"
                        :class="[element.conditional_class]"
                        :data-msg-required="element.required_error_msg"
                        >
                    <label class="custom-control-label" :for="element.name +'_'+ index">
                        {{option}}
                    </label>
                </div>
            </div>
        </div>
        <small class="form-text text-muted" v-if="element.help_text">
            {{element.help_text}}
        </small>
        <popover-help-text-modal
            v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable && element.popover_help_text.content"
            :element="element"
            ></popover-help-text-modal>
    </div>

    <div class="form-group" v-else-if="element.type == 'dropdown'" :class="[element.extras.showConfigurator ? 'active_element' : '',  element.conditional_class]" @mouseover="onMouseHover()" @mouseleave="onMouseLeave(element)" >
        <label :for="element.name">
            <i class="fas fa-sort handle pointer font_icon_size float-left mr-3" :class="[display_handler]" :title="trans('messages.drag_element_using_icon')"></i>
            <span :style="{'color': settings.color.label}">{{element.label}}</span>
            <span :style="{'color': settings.color.required_asterisk_color}" v-if="element.required">*</span>
            <i class="fas fa-info-circle cursor-pointer modal_trigger"
                v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable"
                
                :data-target="`#${element.name}_modal`"></i>
        </label>

        <div class="input-group">
            <div class="input-group-prepend" v-if="element.prefix_icon && element.prefix_icon !== 'none'">
                <span class="input-group-text">
                    <i :class="'fas ' + element.prefix_icon"></i>
                </span>
            </div>
            <select class="custom-select" :class="[element.size, element.custom_class, element.conditional_class]" :required="element.required && applyValidations" v-bind="getDynamicallyGeneratedAttributeObj(element.validations, element.custom_attributes)"
            :id="element.name" :multiple="element.multiselect"
            :name="(element.multiselect == true ? element.name + '[]' : element.name)"
            @change="$emit('apply_conditions')"
            :data-msg-required="element.required_error_msg"
            >
                <option v-for="option in element.options.split('\n')"
                :selected="_.includes(_.get(submitted_data, element.name, ''), option)" 
                >
                    {{option}}
                </option>
            </select>
            <div class="input-group-append" v-if="element.suffix_icon && element.suffix_icon !== 'none'">
                <span class="input-group-text">
                    <i :class="'fas ' + element.suffix_icon"></i>
                </span>
            </div>
        </div>
        <small class="form-text text-muted" v-if="element.help_text">
            {{element.help_text}}
        </small>
        <popover-help-text-modal
            v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable && element.popover_help_text.content"
            :element="element"
            ></popover-help-text-modal>
    </div>

    <div v-else-if="element.type == 'heading'" :class="[element.extras.showConfigurator ? 'active_element' : '',  element.conditional_class]" @mouseover="onMouseHover()" @mouseleave="onMouseLeave(element)" >
        <i class="fas fa-sort handle pointer font_icon_size float-left mr-3" :class="[display_handler]" :title="trans('messages.drag_element_using_icon')"></i>
        <div v-html="'<' + element.tag + ' style=color:' + element.text_color + '>' + element.content + '</' + element.tag + '>'" :class="[element.custom_class]">
        </div>
    </div>

    <div v-else-if="element.type == 'file_upload'" :class="[element.extras.showConfigurator ? 'active_element' : '',  element.conditional_class]" @mouseover="onMouseHover()" @mouseleave="onMouseLeave(element)"  class="mb-3">
        <label :for="element.name">
            <i class="fas fa-sort handle pointer font_icon_size float-left mr-3" :class="[display_handler]" :title="trans('messages.drag_element_using_icon')"></i>
            <span :style="{'color': settings.color.label}">{{element.label}}</span>
            <span :style="{'color': settings.color.required_asterisk_color}" v-if="element.required">*</span>
            <i class="fas fa-info-circle cursor-pointer modal_trigger"
                v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable"
                
                :data-target="`#${element.name}_modal`"></i>
        </label>
        <div class="dropzone" :class="[element.custom_class]" :id="element.name" v-bind="getCustomAttributes(element.custom_attributes)">
        </div>
        <input type="hidden" :name="element.name + '[]'" :id="element.name" :value="_.get(submitted_data, element.name, '')" :required="element.required && applyValidations" :class="element.conditional_class" :data-msg-required="element.required_error_msg">
        <small class="form-text text-muted" v-if="element.help_text">
            {{element.help_text}}
        </small>
        <popover-help-text-modal
            v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable && element.popover_help_text.content"
            :element="element"
            ></popover-help-text-modal>
    </div>

    <div v-else-if="element.type == 'text_editor'" :class="[element.extras.showConfigurator ? 'active_element' : '',  element.conditional_class]" @mouseover="onMouseHover()" @mouseleave="onMouseLeave(element)" >
        <label :for="element.name">
            <i class="fas fa-sort handle pointer font_icon_size float-left mr-3" :class="[display_handler]" :title="trans('messages.drag_element_using_icon')"></i>
            <span :style="{'color': settings.color.label}">{{element.label}}</span>
            <span :style="{'color': settings.color.required_asterisk_color}" v-if="element.required">*</span>
            <i class="fas fa-info-circle cursor-pointer modal_trigger"
            v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable"
            
            :data-target="`#${element.name}_modal`"></i>
        </label>
        <textarea :id="element.name" :name="element.name"
            :required="element.required && applyValidations" class="form-control summer_note"
            :class="[element.conditional_class]"
            :value="_.get(submitted_data, element.name, '')"
            v-bind="getCustomAttributes(element.custom_attributes)"
            :data-msg-required="element.required_error_msg"
            >
        </textarea>
        <small class="form-text text-muted" v-if="element.help_text">
            {{element.help_text}}
        </small>
        <popover-help-text-modal
            v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable && element.popover_help_text.content"
            :element="element"
            ></popover-help-text-modal>
    </div>

    <div v-else-if="element.type == 'terms_and_condition'" class="pt-3 mb-3" :class="[element.extras.showConfigurator ? 'active_element' : '',  element.conditional_class]" @mouseover="onMouseHover()" @mouseleave="onMouseLeave(element)" >
        <i class="fas fa-sort handle pointer font_icon_size" :class="[display_handler]" :title="trans('messages.drag_element_using_icon')"></i>
        <div class="custom-control custom-checkbox">
          <input class="custom-control-input" type="checkbox" :name="element.name" id="terms_and_condition" :required="element.required && applyValidations" :class="[element.custom_class, element.conditional_class]" @change="$emit('apply_conditions')"
          :checked="_.includes(['on'], _.get(submitted_data, element.name, ''))"
          v-bind="getCustomAttributes(element.custom_attributes)"
          :data-msg-required="element.required_error_msg"
          >
          <label class="custom-control-label" for="terms_and_condition">
                <a :href="element.link" target="_blank" v-if="element.link">
                    {{element.label}}
                </a>
                <span v-else>{{element.label}}</span>
                <span :style="{'color': settings.color.required_asterisk_color}" v-if="element.required">*</span>
                <i class="fas fa-info-circle cursor-pointer modal_trigger"
                v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable"
                
                :data-target="`#${element.name}_modal`"></i>
          </label>
        </div>
        <popover-help-text-modal
            v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable && element.popover_help_text.content"
            :element="element"
            ></popover-help-text-modal>
    </div>

    <div v-else-if="element.type == 'hr'" :class="[element.extras.showConfigurator ? 'active_element' : '']" @mouseover="onMouseHover()" @mouseleave="onMouseLeave(element)" >
        <i class="fas fa-sort handle pointer font_icon_size" :class="[display_handler]" :title="trans('messages.drag_element_using_icon')"></i>
        <hr :style="{'margin-top' : element.padding_top + 'px', 'margin-bottom' : element.padding_bottom + 'px', 'border-top' : element.border_size + 'px ' + element.border_type +  element.border_color, 'margin-left' : element.horizontal_space + 'px', 'margin-right' : element.horizontal_space + 'px'}">
    </div>

    <div v-else-if="element.type == 'html_text'" :class="[element.extras.showConfigurator ? 'active_element' : '',  element.conditional_class]" @mouseover="onMouseHover()" @mouseleave="onMouseLeave(element)" >
        <i class="fas fa-sort handle pointer font_icon_size float-left mr-3" :class="[display_handler]" :title="trans('messages.drag_element_using_icon')"></i>
        <span v-html="element.html_text" :class="element.custom_class"></span>
    </div>

    <div class="form-group" v-else-if="element.type == 'rating'" :class="[element.extras.showConfigurator ? 'active_element' : '',  element.conditional_class]" @mouseover="onMouseHover()" @mouseleave="onMouseLeave(element)" >
        <label :for="element.name">
            <i class="fas fa-sort handle pointer font_icon_size float-left mr-3" :class="[display_handler]" :title="trans('messages.drag_element_using_icon')"></i>
            <span :style="{'color': settings.color.label}">{{element.label}}</span>
            <span :style="{'color': settings.color.required_asterisk_color}" v-if="element.required">*</span>
            <i class="fas fa-info-circle cursor-pointer modal_trigger"
            v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable"
            
            :data-target="`#${element.name}_modal`"></i>
        </label>
        <input :id="element.name" :name="element.name"
            class="star_rating" :data-stars="element.stars_to_display"
            :data-min="element.min_rating" :data-max="element.max_rating"
            :data-step="element.increment" :dir="element.direction"
            :data-size="element.size"
            type="hidden" 
            :required="element.required && applyValidations"
            :data-msg-required="element.required_error_msg"
            :class="element.conditional_class"
            :value="_.get(submitted_data, element.name, '')"
            >
        <popover-help-text-modal
            v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable && element.popover_help_text.content"
            :element="element"
            ></popover-help-text-modal>
    </div>
    <div v-else-if="element.type == 'switch'" :class="[element.extras.showConfigurator ? 'active_element' : '',  element.conditional_class]" @mouseover="onMouseHover()" @mouseleave="onMouseLeave(element)" >
        <i class="fas fa-sort handle pointer font_icon_size float-left mr-3" :class="[display_handler]" :title="trans('messages.drag_element_using_icon')"></i>
        <div class="form-group">
            <span class="switch" :class="element.size">
                <input type="checkbox" class="switch" :name="element.name" :id="element.name" :required="element.required && applyValidations" @change="$emit('apply_conditions')" :class="element.conditional_class"
                :checked="_.includes(['on'], _.get(submitted_data, element.name, ''))"
                v-bind="getCustomAttributes(element.custom_attributes)"
                :data-msg-required="element.required_error_msg"
                >
                <label :for="element.name">
                    <span :style="{'color': settings.color.label}" class="ml-2">
                        {{element.label}}
                    </span>
                    <span :style="{'color': settings.color.required_asterisk_color}" v-if="element.required">*</span>
                    <i class="fas fa-info-circle cursor-pointer modal_trigger"
                    v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable"
                    
                    :data-target="`#${element.name}_modal`"></i>
                </label>
            </span>
            <small class="form-text text-muted" v-if="element.help_text">
                {{element.help_text}}
            </small>
        </div>
        <popover-help-text-modal
            v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable && element.popover_help_text.content"
            :element="element"
            ></popover-help-text-modal>
    </div>
    <div v-else-if="element.type == 'signature'" :class="[element.extras.showConfigurator ? 'active_element' : '',  element.conditional_class]"  @mouseover="onMouseHover" @mouseleave="onMouseLeave(element)">
        <i class="fas fa-sort handle pointer font_icon_size float-left mr-3" :class="[display_handler]" :title="trans('messages.drag_element_using_icon')"></i>
        <label :for="element.name">
            <span :style="{'color': settings.color.label}" class="ml-2">
                {{element.label}}
            </span>
            <span :style="{'color': settings.color.required_asterisk_color}" v-if="element.required">*</span>
            <i class="fas fa-info-circle cursor-pointer modal_trigger"
            v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable"
            
            :data-target="`#${element.name}_modal`"></i>
        </label>
        <div class="form-group">
            <canvas :id="element.name" width="300" height="200" class="signature-pad" v-bind="getCustomAttributes(element.custom_attributes)">
            </canvas>
            <input type="hidden" :name="element.name" :id="'output_'+element.name" :value="_.get(submitted_data, element.name, '')" :required="element.required && applyValidations" :class="[element.conditional_class, 'signature']"
            :data-msg-required="element.required_error_msg">
        </div>
        <div class="form-text">
            <span :id="'clear_' + element.name" class="pointer mr-4" :title="trans('messages.clear_signature_pad')" :data-name="element.name">
                <i class="far fa-times-circle"></i>
                {{trans('messages.clear')}}
            </span>
            <span :id="'undo_' + element.name" class="pointer" :title="trans('messages.undo')" :data-name="element.name">
                <i class="fas fa-undo"></i>
                {{trans('messages.undo')}}
            </span>
            <small class="form-text text-muted" v-if="element.help_text">
                {{element.help_text}}
            </small>
        </div>
        <popover-help-text-modal
            v-if="!_.isUndefined(element.popover_help_text) && element.popover_help_text.enable && element.popover_help_text.content"
            :element="element"
            ></popover-help-text-modal>
    </div>
    <!-- page break -->
    <div  v-else-if="_.includes(['page_break'], element.type) && !applyValidations"
        :class="[element.extras.showConfigurator ? 'active_element' : '']"
        @mouseover="onMouseHover" @mouseleave="onMouseLeave(element)">
        <i class="fas fa-sort handle pointer font_icon_size float-left mr-3" :class="[display_handler]" :title="trans('messages.drag_element_using_icon')"></i>
        <div class="divider divider-secondary divider-dashed">
            <div class="divider-text">
                {{trans('messages.page_break')}}
            </div>
        </div>
    </div>
    <!-- /page break -->
    <!-- youtube -->
    <div  v-else-if="element.type == 'youtube'"
        class="mt-25 mb-25" 
        :class="[element.extras.showConfigurator ? 'active_element' : '',  element.conditional_class]"
        @mouseover="onMouseHover" @mouseleave="onMouseLeave(element)">
        <i class="fas fa-sort handle pointer font_icon_size float-left mr-3" :class="[display_handler]" :title="trans('messages.drag_element_using_icon')"></i>
        <label :for="element.name">
            <span :style="{'color': settings.color.label}" class="ml-2">
                {{element.label}}
            </span>
            <span :style="{'color': settings.color.required_asterisk_color}" v-if="element.required">*</span>
        </label>
        <div class="col-md-12">
            <iframe :src="getYtEmbedUrl(element.iframe_url)"
                :id="element.name"
                :name="element.label" :title="element.label"
                :class="[element.custom_class, element.conditional_class]" 
                :width="`${element.width}%`"
                :height="`${element.height}`"
                frameborder="0"
                style="max-width: 100%;"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen>
            </iframe>
        </div>
    </div>
    <!-- /youtube -->
    <!-- iframe -->
    <div  v-else-if="element.type == 'iframe'"
        class="mt-25 mb-25" 
        :class="[element.extras.showConfigurator ? 'active_element' : '',  element.conditional_class]"
        @mouseover="onMouseHover" @mouseleave="onMouseLeave(element)">
        <i class="fas fa-sort handle pointer font_icon_size float-left mr-3" :class="[display_handler]" :title="trans('messages.drag_element_using_icon')"></i>
        <label :for="element.name">
            <span :style="{'color': settings.color.label}" class="ml-2">
                {{element.label}}
            </span>
            <span :style="{'color': settings.color.required_asterisk_color}" v-if="element.required">*</span>
        </label>
        <div :class="['col-md-12']">
            <iframe :src="element.iframe_url"
                :id="element.name"
                :name="element.label" :title="element.label"
                :class="[element.custom_class, element.conditional_class]" 
                :width="`${element.width}%`"
                :height="`${element.height}`"
                frameborder="0"
                style="max-width: 100%;">
            </iframe>
        </div>
    </div>
    <!-- /iframe -->
    <!-- pdf -->
    <div  v-else-if="element.type == 'pdf'"
        :class="[element.extras.showConfigurator ? 'active_element' : '',  element.conditional_class]"
        class="col-md-12" @mouseover="onMouseHover" @mouseleave="onMouseLeave(element)">
        <i class="fas fa-sort handle pointer font_icon_size float-left mr-3" :class="[display_handler]" :title="trans('messages.drag_element_using_icon')"></i>
        <label :for="element.name">
            <span :style="{'color': settings.color.label}" class="ml-2">
                {{element.label}}
            </span>
            <span :style="{'color': settings.color.required_asterisk_color}" v-if="element.required">*</span>
        </label>
        <div class="text-center col-md-12">
            <iframe
                v-if="!_.isEmpty(element.pdf)"
                :src="`${MEDIA_URL}/${element.pdf}`"
                :id="element.name"
                :name="element.label" :title="element.label"
                :class="[element.custom_class, element.conditional_class]" 
                :width="`${element.width}%`"
                :height="`${element.height}`"
                frameborder="0"
                style="max-width: 100%;"
                >
            </iframe>
            <img v-if="_.isEmpty(element.pdf)" class="img-fluid" :src="PDF_PLACEHOLDER">
        </div>
    </div>
    <!-- /pdf -->
    <!-- countdown -->
    <div  v-else-if="element.type == 'countdown'"
        :class="[element.extras.showConfigurator ? 'active_element' : '',  element.conditional_class, 'row']"
        @mouseover="onMouseHover" @mouseleave="onMouseLeave(element)">
        <i class="fas fa-sort handle pointer font_icon_size float-left mr-3" :class="[display_handler]" :title="trans('messages.drag_element_using_icon')"></i>
        <label :for="element.name">
            <span :style="{'color': settings.color.label}" class="ml-2">
                {{element.label}}
            </span>
            <span :style="{'color': settings.color.required_asterisk_color}" v-if="element.required">*</span>
        </label>
        <div class="col-md-12">
            <div :class="[element.custom_class, element.conditional_class]" >
                <span :id="element.name" class="max-width-100">
                </span>
            </div>
        </div>
    </div>
    <!-- /countdown -->
</template>

<script>
    import PopoverHelpTextModal from './Shared/PopoverHelpText.vue';
	export default {
		props: {
			element: Object,
            settings: Object,
            applyValidations: {
                type: Boolean,
                default: true //If passed false then validation will not be applied to field. Used while creating forms
            },
            submitted_data: Object
		},
        components:{
            PopoverHelpTextModal
        },
        data(){
            return{
                display_handler:'hide',
                PDF_PLACEHOLDER: APP.PDF_PLACEHOLDER,
                MEDIA_URL: APP.APP_MEDIA_URL
            }
        },
        mounted() {
            this.$eventBus.$emit('callApplyConditions');
        },
        created() {
            const self = this;

            var field = self.element;
            var notification_position = self.settings.notification.position;

            //if spread to col option is undefined for element set to default
            if (
                _.includes(['radio', 'checkbox'], self.element.type) &&
                _.isUndefined(self.element.spread_to_col)
            ) {
                self.element.spread_to_col = {
                    enable: (!_.isUndefined(self.element.inline) && self.element.inline) ? true : false,
                    column: 2
                };
            }

            $(function() {

                $(".modal_trigger").on("click", function() {
                    let modal_id = $(this).attr('data-target');
                    $(modal_id).modal('toggle');
                });

                //input range
                if (field.type == 'range') {
                    initialize_rangeslider(field.name);

                    //on change of range silder call applyConditions through event
                    $('#'+field.name).on('change', function() {
                        self.$eventBus.$emit('callApplyConditions');
                    });
                }
                
                //datetime picker
                if (field.type == 'calendar') {

                    initialize_datetimepicker(field.name, field.start_date, field.end_date, field.format, field.time_format, field.disabled_days, field.enable_time_picker, field.time_picker_inline);

                    //on change of dateTimePicker call applyConditions through event
                    $('#'+field.name).on('change.datetimepicker', function() {
                        self.$eventBus.$emit('callApplyConditions');
                    });
                }

                if (field.type == 'file_upload') {
                    initialize_dropzone(field.name, field.upload_text, field.no_of_files, field.file_size_limit, field.allowed_file_type);
                }

                if (field.type == 'text_editor') {
                    initialize_text_editor(field.name, field.placeholder, field.editor_height);
                }

                if (field.type == 'rating') {
                    initialize_star_rating(field.name);

                    //on change of star rating call applyConditions through event
                    $('#'+field.name).on('rating:change', function(event, value){
                        self.$eventBus.$emit('callApplyConditions');
                    });
                }

                if (field.type == 'signature') {
                    initialize_signature_pad(field.name);
                }

                if (field.type == 'countdown') {
                    initialize_countdowntimer(field);
                }
                
                initializeToastrSettingsForForm(notification_position);
            });
        },
        methods:{
            getDynamicallyGeneratedAttributeObj(validations, attributes) {

                var rules = [];
                if(this.applyValidations){
                    _.forEach(validations, function(validation) {
                        var rule = 'data-rule-' + validation.rule;
                        var error_msg = 'data-msg-' + validation.rule;
                        var is_rule_required = validation.value ? validation.value : true;

                        rules.push({[rule]:is_rule_required, [error_msg]:validation.error_msg});
                    });
                }
                
                let custom_attr = this.getCustomAttributes(attributes);
                let validation_rules = Object.assign({}, ...rules);
                return {...validation_rules, ...custom_attr};
            },
            onMouseHover() {
                if(!this.applyValidations){
                    this.display_handler = '';
                }
            },
            onMouseLeave(element) {
                if(!this.applyValidations){
                    this.display_handler = 'hide';
                }
            },
            /**
             * get col size for 
             * checkbox & radio
             **/
            spreadColumnForElement(element) {
                let total_columns = 12;
                let spread = (parseInt(element.spread_to_col.column) <= 4 ? parseInt(element.spread_to_col.column) : 4) || 2;
                let default_column = 12;
                if (element.spread_to_col.enable) {
                    default_column = _.floor(total_columns / spread);
                }
                return `col-md-${default_column}`;
            },
            getYtEmbedUrl(url) {
                if(url.search("watch") != -1) {
                    let YT_ID = url.replace("https://www.youtube.com/watch?v=", "");
                    return `https://www.youtube.com/embed/${YT_ID}`;
                } else if (url.search("youtu.be") != -1) {
                    let YT_ID = url.replace("https://youtu.be/", "");
                    return `https://www.youtube.com/embed/${YT_ID}`;
                } else if (url.search("embed") != -1) {
                    let YT_ID = url.replace("https://www.youtube.com/embed/", "");
                    return `https://www.youtube.com/embed/${YT_ID}`;
                }
            },
        }
	}
</script>
<style scoped>
    .active_element {
        padding:15px;
        cursor: pointer;
        -webkit-box-shadow: 0px 0px 0px 2px #0293e2;
        box-shadow: 0px 0px 0px 2px #0293e2;
        border-radius: 10px;
    }
    .hide {
        display: none;
    }
    .show {
        display: block;
    }
</style>