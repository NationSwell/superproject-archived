<?php
/**
* Plugin Name: GP Limit Choices
* Description: Limit how many times a choice may be selected for Radio Button, Drop Down and Checkbox fields.
* Plugin URI: http://gravitywiz.com/
* Version: 1.6.4
* Author: David Smith
* Author URI: http://gravitywiz.com/
* License: GPL2
* Perk: True
*/

/**
* TODO
* + add option to hide when limit reached or disable (code is in place, UI to follow)
*       add UI option that only appears when "enable limits" is checked
*/

/**
* Saftey net for individual perks that are active when core Gravity Perks plugin is inactive.
*/
$gw_perk_file = __FILE__;
if(!require_once(dirname($gw_perk_file) . '/safetynet.php'))
    return;



class GWLimitChoices extends GWPerk {

    protected $min_gravity_perks_version = '1.2.5';
    protected $min_gravity_forms_version = '1.9.3';

    public $version = '1.6.4';
    public $choiceless;

    public static $version_info;
    public static $allowed_field_types = array( 'radio', 'select', 'checkbox', 'multiselect' );
    public static $disabled_choices = array(); // array( ['form_id'] => array( ['field_id'] => array( choice id, choice id ) ) )
    public static $current_form = null;

    private static $instance = null;

    public static function get_instance( $perk_file ) {
        if( null == self::$instance )
            self::$instance = new self( $perk_file );
        return self::$instance;
    }

    function init() {

        // # Register Scripts

        $this->register_scripts();

        // # Form Rendering

        add_action( 'gform_enqueue_scripts',       array( $this, 'enqueue_form_scripts' ) );
        add_action( 'gform_register_init_scripts', array( $this, 'register_init_script' ) );
        add_filter( 'gform_pre_render',            array( $this, 'pre_render' ) );
        add_filter( 'gform_pre_render',            array( $this, 'add_conditional_logic_support_rules' ) );
        add_filter( 'gform_field_input',           array( $this, 'display_choiceless_message' ), 10, 5 );

        // # Form Validation & Submission

        add_filter( 'gform_pre_validation',        array( $this, 'set_current_form' ) );
        add_filter( 'gform_validation',            array( $this, 'validate' ) );
        add_filter( 'gform_is_value_match',        array( $this, 'is_value_match' ), 10, 6 );
        add_filter( 'gform_pre_submission_filter', array( $this, 'add_conditional_logic_support_rules' ) );
        add_action( 'gform_entry_created',         array( $this, 'flush_choice_count_cache_post_entry_creation' ), 10, 2 );
        add_filter( 'gform_after_submission',      array( $this, 'unset_current_form' ), 20 );

        // # Admin

        if( is_admin() ) {

            $this->enqueue_field_settings();

            add_filter( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

        }

    }

    private function register_scripts() {

        $this->register_script( $this->key( 'admin' ), $this->get_base_url() . '/js/admin.js', array( 'jquery', 'gform_form_admin', 'gform_gravityforms' ), $this->version, false );
        $this->register_script( $this->key( 'frontend' ), $this->get_base_url() . '/js/frontend.js', array( 'jquery', 'gform_gravityforms', 'gform_conditional_logic' ), $this->version, false );

    }

    public function enqueue_admin_scripts() {

        $is_applicable_page = in_array(
            GFForms::get_page(),
            array(
                'form_editor',
                'form_settings',
                'confirmation',
                'notification_edit',
                'export_entry'
            )
        );

        if( $is_applicable_page ) {
            wp_enqueue_script( $this->key( 'admin' ) );
        }

    }

    public function enqueue_form_scripts( $form ) {

        foreach( $form['fields'] as $field ) {
            if( $this->is_applicable_field( $field ) ) {
                wp_enqueue_script( $this->key( 'frontend' ) );
                break;
            }
        }

    }

    public function register_init_script( $form ) {

        $data = array();

        foreach( $form['fields'] as $field ) {

            if( ! $this->is_applicable_field( $field ) ) {
                continue;
            }

	        $choices = $field['choices'];

            foreach( $choices as &$choice ) {
                $choice['count'] = self::get_choice_count( $choice['value'], $field, $form['id'] );
            }

	        $field['choices'] = $choices;

            $data[$field['id']] = array(
	            'choices'     => $field['choices'],
	            'isExhausted' => in_array( $field['id'], (array) rgar( $this->choiceless, $form['id'] ) )
            );

        }

        if( empty( $data ) ) {
            return;
        }

        $args = array(
            'formId' => $form['id'],
            'data'   => $data
        );

        $script = 'new GPLimitChoices( ' . json_encode( $args ) . ' );';

        GFFormDisplay::add_init_script( $form['id'], $this->slug, GFFormDisplay::ON_PAGE_RENDER, $script );

    }

    public function field_settings_js() {
        ?>

        <style type="text/css">

            .gw-limit-choice { }
                .gw-limit-choice label.gfield_choice_header_limit { display: none !important; }
                .gw-limit-choice input.field-choice-limit,
                .field-choice-limit{ display: none; }

            .gw-limit-choice.limits-enabled { }
                .gw-limit-choice.limits-enabled label.gfield_choice_header_limit { display: inline !important; }
                .gw-limit-choice.limits-enabled input.field-choice-limit { display: inline; }

            /* Headers */
            .gw-limit-choice .gfield_choice_header_label { display: inline !important; }
                .gw-limit-choice .gfield_choice_header_limit { display: inline !important; padding-left: 236px; }
                .gw-limit-choice .choice_with_value .gfield_choice_header_limit { padding-left: 86px; }
                .gw-limit-choice .choice_with_price .gfield_choice_header_limit { padding-left: 88px; }
                .gw-limit-choice .choice_with_value_and_price .gfield_choice_header_price { padding-left: 74px; }
                .gw-limit-choice .choice_with_value_and_price .gfield_choice_header_limit { padding-left: 35px; }

            /* Label Input */
            .gw-limit-choice.limits-enabled #field_choices li input.field-choice-text { width: 270px; }

            /* Value Input */
            .gw-limit-choice.limits-enabled .choice_with_value li input.field-choice-value { width: 113px !important; }

            /* Price Input */
            .gw-limit-choice.limits-enabled .choice_with_price li input.field-choice-price { width: 113px !important; }
            .gw-limit-choice.limits-enabled .choice_with_value_and_price li input.field-choice-price { width: 60px !important; }

        </style>

        <script type="text/javascript">

            gperk.limitChoicesSetup = false;

            gperk.addLimitChoiceInputs = function() {

                jQuery('ul#field_choices li').each(function(i){

                    var limitValue = typeof field.choices[i]['limit'] != 'undefined' ? field.choices[i]['limit'] : '';

                    // skip this row if already has a limit input
                    if( jQuery(this).find( 'input.field-choice-limit' ).length > 0 )
                        return;

                    // add limit input
                    jQuery(this).find('input.field-choice-input:last').after('<input type="text" class="field-choice-input field-choice-limit" value="' + limitValue + '" style="width:40px;" onkeyup="gperk.setChoiceLimit(' + i + ', this.value)" />');

                    // replace onclick options
                    jQuery(this).find('img.add_field_choice').attr('onclick', jQuery(this).find('img.add_field_choice').attr('onclick') + ' gperk.addLimitChoiceInputs();');
                    jQuery(this).find('img.delete_field_choice').attr('onclick', jQuery(this).find('img.delete_field_choice').attr('onclick') + ' gperk.addLimitChoiceInputs();');

                });

            };

            gperk.setChoiceLimit = function(index, value) {
                field.choices[index]['limit'] = value;
            }

            gperk.addEnableLimitsCheckbox = function() {

                // add checkbox if it has not been added
                if(jQuery('#field_choice_limits_enabled').length < 1) {
                    var checkbox = '<div style="float:right;margin-right:10px;"> \
                        <input type="checkbox" onclick="SetFieldProperty(\'<?php echo $this->key('enableLimits'); ?>\', this.checked); gperk.toggleEnableLimits();" id="field_choice_limits_enabled"> \
                        <label class="inline gfield_value_label" for="field_choice_limits_enabled"><?php _e('enable limits', 'gravityperks'); ?></label> \
                    </div>';
                    jQuery('li.gw-limit-choice.field_setting').children('div:first-child').after(checkbox);
                }

                // check or uncheck
                jQuery('#field_choice_limits_enabled').prop('checked', field['<?php echo $this->key('enableLimits'); ?>'] == true);

                gperk.toggleEnableLimits();

            }

            gperk.removeEnableLimitsCheckbox = function() {
                jQuery('#field_choice_limits_enabled').parent('div').remove();
            }

            gperk.toggleEnableLimits = function() {
                var isChecked = jQuery('#field_choice_limits_enabled').prop('checked');
                if(isChecked) {
                    jQuery('li.gw-limit-choice.field_setting').addClass('limits-enabled');
                } else {
                    jQuery('li.gw-limit-choice.field_setting').removeClass('limits-enabled');
                }
            }

            jQuery(function($){

            /**
            * Handle field settings load
            */
            $(document).bind('gform_load_field_settings', function(event, field) {

                var allowedFieldTypes = <?php echo json_encode(self::$allowed_field_types); ?>;

                if($.inArray(field.type, allowedFieldTypes) == -1 && $.inArray(field.inputType, allowedFieldTypes) == -1) {

                    $('li.choices_setting').removeClass('gw-limit-choice');
                    $('.gfield_choice_header_limit').hide();
                    gperk.removeEnableLimitsCheckbox();
                    return;

                } else {

                    // add limit class to choice setting
                    $('li.choices_setting').addClass('gw-limit-choice');

                    // add limit header if does not exists
                    if(!$('.gfield_choice_header_limit').length)
                        $('.gfield_choice_header_price').after('<label class="gfield_choice_header_limit">Limit</label>');

                    $('.gfield_choice_header_limit').show();

                    // init enable limits checkbox
                    gperk.addEnableLimitsCheckbox(field);

                    // init choice inputs
                    gperk.addLimitChoiceInputs();

                    // only bind once for sorting action
                    if(!gperk.limitChoicesSetup) {

                        $('#field_choices').bind('sortupdate', function(){
                            // was firing before GF's update function
                            setTimeout('gperk.addLimitChoiceInputs()', 1);
                        });

                        $(document).on( 'gform_load_field_choices', function() {

                            gperk.addLimitChoiceInputs();

                        });

                    }

                    gperk.limitChoicesSetup = true;

                }

            });

        });

        </script>

        <?php
    }

    public function pre_render( $form ) {

        $has_disabled_choice = false;

        foreach( $form['fields'] as &$field ) {

            if( ! $this->is_applicable_field( $field ) )
                continue;

            $choice_counts = self::get_choice_counts( $form['id'], $field );
            $choices = array();

            // allows to prevent the removal of choices, validation still occurs
            $remove_choices = apply_filters( "gwlc_remove_choices_{$form['id']}", apply_filters( 'gwlc_remove_choices', true, $form['id'], $field['id'] ), $form['id'], $field['id'] );
	        $remove_choices = gf_apply_filters( 'gplc_remove_choices', array( $form['id'], $field->id ), $remove_choices, $form['id'], $field['id'] );

            // if choices are not removed, disable by default but allow override
            $disable_choices = gf_apply_filters( 'gplc_disable_choices', array( $form['id'], $field->id ), ! $remove_choices, $form['id'], $field['id'] );

            foreach( $field['choices'] as $choice ) {

                $limit = rgar( $choice, 'limit' );
                $no_limit = rgblank( $limit );

                if( $no_limit ) {
                    $choices[] = $choice;
                    continue;
                }

                // if choice count is greater than or equal to choice limit, limit has been exceeded
                $choice_count = intval( rgar( $choice_counts, $choice['value'] ) );
                $exceeded_limit = $choice_count >= $limit;

                // add $choice to $disabled_choices, will be used to disable choice via JS
                if( $exceeded_limit && $disable_choices ) {
                    $has_disabled_choice = true;
                    $choice['is_disabled'] = true;
                    $choice['isSelected'] = false;
                }

                // provide custom oppurtunity to modify choices (includes whether the choice has excdeed limit)
                $choice = apply_filters( 'gwlc_pre_render_choice', $choice, $exceeded_limit, $field, $form );
                $choice = apply_filters( 'gplc_pre_render_choice', $choice, $exceeded_limit, $field, $form, $choice_count );

                if( ! $exceeded_limit || ! $remove_choices ) {
                    $choices[] = $choice;
                }

            }

            if( empty( $choices ) ) {
                $this->choiceless[ $form['id'] ][] = $field['id'];
            }

            $field['choices'] = $choices;

        }

        if( $has_disabled_choice ) {
            add_filter( 'gform_field_content', array( $this, 'disable_choice' ), 10, 2 );
        }

        return $form;
    }

    /**
     * We need to make sure that when fields are changed that impact our custom conditional logic the conditional logic is triggered.
     * To this end, we add "fake" rules which will always return true or false (depending on the logic type) to ensure that when
     * GF creates the 'conditionalLogicFields' property for a trigger field, it will pick up our custom dependency.
     *
     * @param $form
     *
     * @return $form
     */
    public function add_conditional_logic_support_rules( $form ) {

        foreach( $form['fields'] as &$field ) {

            if( ! is_array( rgar( $field, 'conditionalLogic' ) ) ) {
                continue;
            }

            foreach( $field['conditionalLogic']['rules'] as $rule ) {

                if( strpos( $rule['fieldId'], 'gplc_count_remaining_' ) === false ) {
                    continue;
                }

	            $field_id_bits = explode( '_', $rule['fieldId'] );
                $field_id = array_pop( $field_id_bits );

                // if ALL rules must match, create rule that will always be true, if ANY, create rule that will always be false
                $value = $field['conditionalLogic']['logicType'] == 'all' ? '__return_true' : '__return_false';
                
	            $conditional_logic = $field['conditionalLogic'];

	            $conditional_logic['rules'][] = array(
                    'fieldId'  => $field_id,
                    'operator' => 'is',
                    'value'    => $value
                );

	            $field['conditionalLogic'] = $conditional_logic;

            }

        }

        return $form;
    }

    public function is_value_match( $is_match, $field_value, $target_value, $operation, $source_field, $rule ) {

        if( $target_value == '__return_true' ) {
            return true;
        } else if( $target_value == '__return_false' ) {
            return false;
        }

        // $current_form is set on pre validation and nulled on after submission
        if( ! self::$current_form ) {
            return $is_match;
        }

        $has_our_tag = strpos( $rule['fieldId'], 'gplc_count_remaining' ) !== false;
        if( ! $has_our_tag ) {
            return $is_match;
        }

        $target_field_id = call_user_func( 'array_pop', explode( '_', $rule['fieldId'] ) );
        $target_field    = GFFormsModel::get_field( self::$current_form, $target_field_id );

        $choice    = call_user_func( 'array_pop', $this->get_selected_choices( $target_field ) );

	    // account for drop down placeholder option (no choice selected); only applies when comparing to "0" for checking if field is exhausted
	    // otherwise we'll assume they're checking for the selected option, which would be "0" anyways since no option is selected
		if( ! $choice && GFFormsModel::get_input_type( $target_field ) == 'select' && $target_value === '0' ) {

			$remaining = $this->is_field_exhausted( $target_field ) ? 0 : 1;

		} else {

			$limit     = intval( rgar( $choice, 'limit' ) );
			$count     = self::get_choice_count( $choice['value'], $target_field, $target_field['formId'] );
			$remaining = max( $limit - $count, 0 );

		}

	    $is_match = GFFormsModel::matches_operation( $remaining, $target_value, $operation );

        return $is_match;
    }

    public function set_current_form( $form ) {
        self::$current_form = $form;
        return $form;
    }

    public function unset_current_form( $return ) {
        self::$current_form = null;
        return $return;
    }

    /**
     * Prevent synchronous submisssion which would exceed limit.
     *
     * @param mixed $validation_result
     */
    public function validate($validation_result) {

        $form = $validation_result['form'];
        $has_validation_error = false;

        foreach($form['fields'] as &$field) {

            if( ! $this->should_validate_field( $field ) )
                continue;

            $choices = $this->get_selected_choices( $field );
            if( empty( $choices ) )
                continue;

            // confirm whether choices are removed and/or disabled for valdiation purposes
            $remove_choices = apply_filters( "gplc_remove_choices_{$form['id']}", apply_filters( 'gplc_remove_choices', true, $form['id'], $field['id'] ), $form['id'], $field['id'] );
            $disable_choices = apply_filters( "gplc_disable_choices_{$form['id']}", apply_filters( 'gplc_disable_choices', ! $remove_choices, $form['id'], $field['id'] ), $form['id'], $field['id'] );

            // if choices are not disabled, bypass validation
            if( ! $remove_choices && ! $disable_choices ) {
                continue;
            }

            $validation_messages = array();

            foreach( $choices as $choice ) {

                $limit = rgar( $choice, 'limit' );
                if( rgblank( $limit ) ) {
                    continue;
                }

	            $limit            = intval( $limit );
                $count            = self::get_choice_count( $choice['value'], $field, $form['id'] );
	            $requested_count  = $this->get_requested_count( $field );
	            $out_of_stock     = $limit <= $count;
	            $not_enough_stock = $limit < $count + $requested_count;
	            $remaining_count  = $limit - $count;

                if( ! ( $out_of_stock && $requested_count > 0 ) && ! $not_enough_stock && $limit != 0 ) {
                    continue;
                }

	            // passed to the label hooks
	            $inventory_data = array(
		            'limit' => $limit,
		            'count' => $count,
		            'requested' => $requested_count,
		            'remaining' => $remaining_count
	            );

	            if( $out_of_stock ) {

		            $out_of_stock_message = __( 'The choice, "%s", which you have selected is no longer available.', 'gravityperks' );
		            /**
		             * Filter validation message when the item is out of stock.
		             *
		             * @since 1.6
		             *
		             * @param string $out_of_stock_message Validation message.
		             * @param array  $form                 Form Object
		             * @param array  $field                Field Object
		             * @param array  $inventory_data       Includes the limit, count, requested count and remaining count.
		             *
		             * @example url https://gist.github.com/spivurno/3dbf8bf204b46031f7ec
		             */
		            $out_of_stock_message = gf_apply_filters( 'gplc_out_of_stock_message', array( $form['id'], $field->id ), $out_of_stock_message, $form, $field, $inventory_data );
		            $message              = sprintf( $out_of_stock_message, $choice['text'] );

	            } else if( $not_enough_stock ) {

		            if( $field->type == 'option' ) {
			            $not_enough_stock_message = _n( 'You selected this option for %1$d items but only %2$d of this option is available.', 'You selected this option for %1$d items but only %2$d this option are available.', $remaining_count, 'gravityperks' );
		            } else {
			            $not_enough_stock_message = _n( 'You selected %1$d of this item but only %2$d is available.', 'You selected %1$d of this item but only %2$d are available.', $remaining_count, 'gravityperks' );
		            }
		            /**
		             * Filter validation message when the item has stock available but not as many as the requested amount.
		             *
		             * @since 1.6
		             *
		             * @param string $not_enough_stock_message Validation message.
		             * @param array  $form                     Form Object
		             * @param array  $field                    Field Object
		             * @param array  $inventory_data           Includes the limit, count, requested count and remaining count.
		             *
		             * @example url https://gist.github.com/spivurno/a0b3bc833a1b7ced93eb
		             */
		            $not_enough_stock_message = gf_apply_filters( 'gplc_not_enough_stock_message', array( $form['id'], $field->id ), $not_enough_stock_message, $form, $field, $inventory_data );
		            $message                  = sprintf( $not_enough_stock_message, $requested_count, $remaining_count );

	            }

                $validation_messages[] = $message;

            }

            if( ! empty( $validation_messages ) ) {
                $has_validation_error        = true;
                $field['failed_validation']  = true;
                $field['validation_message'] = implode( '<br />', $validation_messages );
            }

        }

        $validation_result['form'] = $form;
        $validation_result['is_valid'] = $validation_result['is_valid'] && ! $has_validation_error;

        return $validation_result;
    }

    public function disable_choice( $content, $field ) {

        $field_type = GFFormsModel::get_input_type( $field );
        if( !in_array( $field_type, self::$allowed_field_types ) )
            return $content;

        foreach( $field['choices'] as $choice_id => $choice ) {

            if( !rgar( $choice, 'is_disabled' ) )
                continue;

            if( is_array( $field['inputs'] ) ) {
                $input = false;
                foreach( $field['inputs'] as $input_index => $input ) {
                    if( $input_index == $choice_id ) {
                        $pieces = explode( '.', $input['id'] );
                        $choice_id = $pieces[1];
                        break;
                    }
                }
            }

            switch( $field_type ) {
            case 'multiselect':
            case 'select':
				if( in_array( $field['type'], array( 'product', 'option' ) ) ) {
					$price = GFCommon::to_number( $choice['price'] ) === false ? 0 : GFCommon::to_number( $choice['price'] );
					$value = sprintf( '%s|%s', $choice['value'], $price );
				} else {
					$value = $choice['value'];
				}
                $value = esc_attr( $value );
                $search = "<option value='{$value}'";
                break;
            default:
                if( version_compare( GFCommon::$version, '1.8.20.7', '>=' ) ) {
                    $choice_html_id = "choice_{$field['formId']}_{$field['id']}_{$choice_id}";
                } else {
                    $choice_html_id = "choice_{$field['id']}_{$choice_id}";
                }
                $search = "id='{$choice_html_id}'";
                break;
            }

            $replace = "$search disabled='disabled' class='gwlc-disabled'";
            $content = str_replace( $search, $replace, $content );

        }

        return $content;
    }

    public function is_applicable_field( $field ) {

        $is_allowed_field_type = in_array( GFFormsModel::get_input_type( $field ), self::$allowed_field_types );
        $are_limits_enabled = rgar( $field, $this->key( 'enableLimits' ) );

        return $is_allowed_field_type && $are_limits_enabled;
    }

    public function should_validate_field( $field ) {
        return $this->is_applicable_field( $field ) && GFFormDisplay::get_source_page( $field->formId ) == $field->pageNumber;
    }

    public function display_choiceless_message($input, $field, $value, $lead_id, $form_id) {

        if(is_admin() || !isset($this->choiceless[$form_id]) || !in_array($field['id'], $this->choiceless[$form_id]))
            return $input;

        $message = '<p class="choiceless">There are no options available for this field.<p>';

        return apply_filters( 'gplc_choiceless_message', $message, $field, $form_id );
    }

    public function get_selected_choices( $field, $values = false ) {

        if( ! $values ) {
            $values = $this->get_selected_values( $field );
        } else if( ! is_array( $values ) ) {
            $values = array( $values );
        }

        $choices = array();

        foreach( $field['choices'] as $choice ) {
            if( in_array( $choice['value'], $values ) )
                $choices[] = $choice;
        }

        return $choices;
    }

    public function get_selected_values( $field ) {

        $values = GFFormsModel::get_field_value( $field );
        if( ! is_array ( $values ) ) {
	        $values = array( $values );
        }

        $values = array_filter( $values, array( $this, 'not_blank' ) );

        if( $this->is_pricing_field( $field ) ) {
            foreach( $values as &$value ) {
                $value = rgar( explode( '|', $value ), 0 );
            }
        }

        return $values;
    }

    public function not_blank( $value ) {
        return ! rgblank( $value );
    }

    public function is_pricing_field( $field ) {
        return GFCommon::is_pricing_field( $field['type'] );
    }

	public function is_field_exhausted( $field ) {
		return isset( $this->choiceless[ $field['formId'] ] ) && in_array( $field['id'], $this->choiceless[ $field['formId'] ] );
	}

	public function get_requested_count( $field ) {

		if( ! gp_limit_choices()->is_pricing_field( $field ) ) {
			return 1;
		}

		$quantity_input_id = $this->get_product_quantity_input_id( $field );
		$requested_count   = $quantity_input_id ? rgpost( sprintf( 'input_%s', str_replace( '.', '_', $quantity_input_id ) ) ) : 1;

		return intval( $requested_count );
	}

	/**
	 * Get the Quantity field or Product field where quantity ordered will be provided.
	 *
	 * @param $product_field
	 *
	 * @return bool
	 */
	public function get_product_quantity_field( $product_field ) {

		$form            = GFAPI::get_form( $product_field->formId );
		$product_field   = $product_field->type == 'product' ? $product_field : GFFormsModel::get_field( $form, $product_field->productField );
		$quantity_fields = GFCommon::get_product_fields_by_type( $form, array( 'quantity' ), $product_field->id );

		if( isset( $quantity_fields[0] ) ) {
			$quantity_field = $quantity_fields[0];
		} else {
			// if no quantity field is found, the product field will have the quantity inline
			$quantity_field = $product_field;
		}

		return $quantity_field;
	}

	public function get_product_quantity_input_id( $product_field ) {

		$quantity_field = $this->get_product_quantity_field( $product_field );

		if( $quantity_field->type == 'quantity' ) {
			$quantity_input_id = $quantity_field->id;
		} else if( in_array( GFFormsModel::get_input_type( $quantity_field ), array( 'singleproduct', 'calculation' ) ) ) {
			$quantity_input_id = "{$quantity_field->id}.3";
		} else {
			$quantity_input_id = false;
		}

		return $quantity_input_id;
	}

    public static function get_choice_count( $value, $field, $form_id ) {

        $counts = self::get_choice_counts( $form_id, $field );

        if( gp_limit_choices()->is_pricing_field( $field ) ) {
            $value = rgar( explode( '|', $value ), 0 );
        }

        return intval( rgar( $counts, $value ) );
    }

    public static function get_choice_counts( $form_id, $field ) {
        global $wpdb;

        if( is_integer( $field ) ) {
            $form = GFFormsModel::get_form_meta( $form_id );
            $field = GFFormsModel::get_field( $form, $field );
        }

        $cache_key = sprintf( 'gplc_choice_counts_%d_%d', $form_id, $field['id'] );
        $result = GFCache::get( $cache_key );
        if( $result !== false ) {
            return $result;
        }

	    $is_pricing_field = gp_limit_choices()->is_pricing_field( $field );
	    $counts           = array();

        $query = array(
            'select' => 'SELECT ld.lead_id, ld.field_number, ld.value',
            'from'   => "FROM {$wpdb->prefix}rg_lead l INNER JOIN {$wpdb->prefix}rg_lead_detail ld ON ld.lead_id = l.id",
            'join'   => '',
            'where'  => $wpdb->prepare( "
                WHERE l.status = 'active'
                AND ld.form_id = %d
                AND floor( ld.field_number ) = %d",
	            $form_id, $field['id']
            )
        );

	    if( $is_pricing_field ) {
		    $quantity_input_id = gp_limit_choices()->get_product_quantity_input_id( $field );
		    if( $quantity_input_id ) {
			    $query['select'] .= ', ld2.value as quantity';
			    $query['join']   .= "INNER JOIN {$wpdb->prefix}rg_lead_detail ld2 ON ld2.lead_id = l.id";
			    $query['where']  .= $wpdb->prepare( "\nAND CAST( ld2.field_number as CHAR )  = %s", $quantity_input_id );
		    }
	    }

        $approved_payments_only = apply_filters( "gwlc_approved_payments_only_{$form_id}", apply_filters( 'gwlc_approved_payments_only', false ) );
        $approved_payments_only = apply_filters( "gplc_completed_payments_only_{$form_id}", apply_filters( 'gplc_completed_payments_only', $approved_payments_only ) );

        if( $approved_payments_only )  {
            $query['where'] .= " AND ( l.payment_status = 'Approved' OR l.payment_status = 'Paid' OR l.payment_status is null )";
        }

        $query   = apply_filters( "gwlc_choice_counts_query_{$form_id}", apply_filters( 'gwlc_choice_counts_query', $query, $field ), $field );
        $sql     = implode( ' ', $query);
        $results = $wpdb->get_results( $sql, ARRAY_A );

        foreach( $results as $choice ) {

            if( strlen( $choice['value'] ) >= GFORMS_MAX_FIELD_LENGTH - 10 ) {
                $entry = array( 'id' => $choice['lead_id'] );
                $long_value = GFFormsModel::get_field_value_long( $entry, $choice['field_number'], array(), false );
                $choice['value'] = ! empty( $long_value ) ? $long_value : $choice['value'];
            }

            if( $is_pricing_field ) {
                $value            = wp_kses( rgar( explode( '|', $choice['value'] ), 0 ), wp_kses_allowed_html( 'post' ) );
	            $quantity         = $quantity_input_id ? $choice['quantity'] : 1;
                $counts[ $value ] = isset( $counts[ $value ] ) ? $counts[ $value ] + $quantity : $quantity;
            } else if( GFFormsModel::get_input_type( $field ) == 'multiselect' ) {
                $values = explode( ',', $choice['value'] );
                foreach( $values as $value ) {
	                $value = wp_kses( $value, wp_kses_allowed_html( 'post' ) );
                    $counts[ $value ] = isset( $counts[ $value ] ) ? $counts[ $value ] + 1 : 1;
                }
            } else {
	            $value = wp_kses( $choice['value'], wp_kses_allowed_html( 'post' ) );
                $counts[ $value ] = isset( $counts[ $value ] ) ? $counts[ $value ] + 1 : 1;
            }
        }

        GFCache::set( $cache_key, $counts );

        return $counts;
    }

    public static function flush_choice_count_cache_post_entry_creation( $entry, $form ) {
        self::flush_choice_count_cache( $form );
    }

    public static function flush_choice_count_cache( $form ) {
        foreach( $form['fields'] as $field ) {
            $cache_key = sprintf( 'gplc_choice_counts_%d_%d', $form['id'], $field['id'] );
            GFCache::delete( $cache_key );
        }
    }

    public static function get_disabled_choices( $form_id = false, $field_id = false ) {

        if( ! $form_id && ! $field_id )
            return self::$disabled_choices;

        if( $form_id && $field_id )
            return isset( self::$disabled_choices[$form_id][$field_id] ) ? self::$disabled_choices[$form_id][$field_id] : array();

        if( $form_id )
            return isset( self::$disabled_choices[$form_id] ) ? self::$disabled_choices[$form_id] : array();

        return array();
    }

    public static function set_disabled_choice( $choice, $form_id, $field_id ) {

        $choices = self::get_disabled_choices( $form_id, $field_id );
        $choices[] = $choice;

        if( !isset( self::$disabled_choices[$form_id] ) )
            self::$disabled_choices[$form_id] = array();

        if( !isset( self::$disabled_choices[$form_id][$field_id] ) )
            self::$disabled_choices[$form_id][$field_id] = array();

        self::$disabled_choices[$form_id][$field_id] = $choices;

    }

    /**
     * Get the number of entries left. Not used internally.
     *
     * @param $form_id
     * @param $field_id
     * @param $value     the value of the desired choice.
     *
     * @return int|string
     */
    public static function get_entries_left( $form_id, $field_id, $value ) {

        $form   = GFFormsModel::get_form_meta( $form_id );
        $field  = GFFormsModel::get_field( $form, $field_id );
        $choice = reset( gp_limit_choices()->get_selected_choices( $field, $value ) );
        $limit  = rgar( (array) $choice, 'limit' );

        if( ! $choice || ! $limit )
            return __( 'unlimited', 'gravityperks' );

        $count = self::get_choice_count( $value, $field, $form_id );

        if( $limit > $count )
            return $limit - $count;

        return 0;
    }

    public function documentation() {
    	return array(
            'type' => 'url',
            'value' => 'http://gravitywiz.com/documentation/gp-limit-choices/'
        );
    }





    /**
     * Get the selected choice by field or field and value.
     *
     * @deprecated 1.3.1
     * @deprecated Use get_selected_choices()
     *
     * @param       $field
     * @param mixed $value
     *
     * @return bool
     */
    public static function get_selected_choice( $field, $value = false ) {

        _deprecated_function( __FUNCTION__, '1.3.1', 'get_selected_choices()' );

        if( ! $value ) {
            $value = GFFormsModel::get_field_value($field);
            $value = gp_limit_choices()->is_pricing_field( $field ) ? rgar( explode( '|', $value ), 0 ) : $value;
        }

        foreach( $field['choices'] as $choice ) {
            if( $choice['value'] == $value )
                return $choice;
        }

        return false;
    }

}

function gp_limit_choices() {
    return GWLimitChoices::get_instance( null );
}