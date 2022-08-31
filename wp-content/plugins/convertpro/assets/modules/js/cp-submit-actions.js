(function ( $, window, undefined ) {

	var is_form_validated = true;
	var btn_data = '';
	$(window).ready(function(){

		var styleId = $('.cpro-form input[name="style_id"]').val();
		styleIdSelctor = 'cp_popup_style_'+styleId;

		/* Field Action on Click */
		$(document).on('click', '.cp-target.cp-button-field', function() {
			var self = $(this),
			 action  = self.closest(".cp-field-html-data").data('action');
			$('.cp-current-clicked-btn').removeClass('cp-current-clicked-btn');
			self.closest(".cp-field-html-data").addClass('cp-current-clicked-btn');
			btn_data = self.text();
		});

		/* Field Action on Click */
		$(document).on('click', '.cp-field-html-data.cp-shapes-wrap', function() {
			var self = $(this),
			 action  = self.closest(".cp-field-html-data").data('action');

			if ( action == 'submit' || action ==  'submit_n_close' || action ==  'submit_n_goto_step' || action == 'submit_n_goto_url' ) {
			 	if( $('.cp_shape_submit_hidden').length == 0) {
			 		self.find('.cp_shape_submit_label').append('<input type="submit" class="cp_shape_submit_hidden">');
			 	}
			}

			$('.cp-current-clicked-shape').removeClass('cp-current-clicked-shape');
			self.closest(".cp-field-html-data").addClass('cp-current-clicked-shape');
		});

		//handled form submission
		jQuery( document ).on( "submit", "form.cpro-form" , function(e) {
			e.preventDefault();
			var is_success = false;
			var invalid_email = false;
			var thisForm = jQuery( this );
			jQuery(document).trigger( "cp_before_form_submit", [jQuery(this)] );
			
			var checkboxFlag = true;

			jQuery( this ).find( '.cpro-checkbox-required' ).each( function( index, elem ) {
				var checkThis = jQuery(this);
				setTimeout(function() { checkThis.find("input[type=checkbox]").prop( 'required', false ); }, 2000);
				var checked = jQuery(this).find("input[type=checkbox]:checked").length;
				if( checked == 0 ) {
					jQuery(this).find("input[type=checkbox]:first").attr( 'required', 'required' );
					thisForm[0].reportValidity();
					checkboxFlag = false;
					return false;
				}
			} );
			
			if( checkboxFlag ) {
				is_form_validated = true;
			} else {
				is_form_validated = false;
			}

			if( is_form_validated ) {

				var form         = jQuery(this),
					style_id     = form.closest( ".cp-popup-container" ).data("style").replace( "cp_style_", "" ),
					style_slug   = form.closest( ".cp-popup-container" ).data("styleslug"),
					btn_old_text      = '';

				var css_val = form.find( '.cp-form-input-field' ).css( 'text-transform' );
				var form_field, attr_name, radio_attr_name, checkbox_attr_name, field_name, field_name_value, field_name_class, select_dropdown, selected_radio, selectedCheck;

				if( 'uppercase' == css_val || 'lowercase' == css_val || 'capitalize' == css_val )
				{
					form_field = form.closest( ".cp-popup-container" ).find( '.cp-form-field' ).each(function() {
					attr_name = jQuery(this).attr('name');

					field_name = form.find( '[name="'+attr_name+'"]' );
					field_name_class = field_name.attr( 'class' );

					// Dropdown field section start.
					if( undefined != field_name_class){
						var isFound = field_name_class.indexOf( "cp-dropdown-field" ) !=-1? true: false;
						if( isFound )
						{
							if( 'uppercase' == css_val ) {
								select_dropdown = $( 'select[name="'+attr_name+'"] option:selected' ).val().toUpperCase();
							} else if( 'lowercase' == css_val )	{
								select_dropdown = $( 'select[name="'+attr_name+'"] option:selected' ).val().toLowerCase();
							} else if ( 'capitalize' == css_val ) {
								select_dropdown = $( 'select[name="'+attr_name+'"] option:selected' ).val().toLowerCase().replace(/\b[a-z]/g, function(letter) {
									return letter.toUpperCase();
								});;
							}

							$( 'select[name="'+attr_name+'"] option:selected' ).attr( 'value', select_dropdown );
						}
					}
					// Dropdown field section end.
					
					// Radio and Checkbox field section start.
					if( undefined == field_name_class )
					{
						var radio_length = $( ".cp-radio-field" ).length;
						var checkbox_length = $( ".cp-checkbox-field" ).length;

						if( radio_length > 0 ){
							var each_radio = jQuery(this).find( 'input:radio' ).each(function() {
							radio_attr_name = jQuery(this).attr( 'name' );

							if( 'uppercase' == css_val ) {
								selected_radio = $( 'input[name="'+radio_attr_name+'"]:checked' ).val().toUpperCase();
							} else if( 'lowercase' == css_val ) {
								selected_radio = $( 'input[name="'+radio_attr_name+'"]:checked' ).val().toLowerCase();
							} else if ( 'capitalize' == css_val ) {
								selected_radio = $( 'input[name="'+radio_attr_name+'"]:checked' ).val().toLowerCase().replace(/\b[a-z]/g, function(letter) {
									return letter.toUpperCase();
								});;
							}
								$( 'input[name="'+radio_attr_name+'"]:checked' ).attr( 'value', selected_radio );

							});
							;
						}

						if( checkbox_length > 0 ){
							var each_checkbox = jQuery(this).find( 'input:checkbox' ).each(function() {
							checkbox_attr_name = jQuery(this).attr( 'name' );
								
							var check = $( 'input[name="'+checkbox_attr_name+'"]' ).is( ":checked" );
								
							if( true == check ){
								if( 'uppercase' == css_val ) {
									selectedCheck = $( 'input[name="'+checkbox_attr_name+'"]:checked' ).val().toUpperCase();
								} else if( 'lowercase' == css_val ) {
									selectedCheck = $( 'input[name="'+checkbox_attr_name+'"]:checked' ).val().toLowerCase();
								} else if ( 'capitalize' == css_val ) {
									selectedCheck = $( 'input[name="'+checkbox_attr_name+'"]:checked' ).val().toLowerCase().replace(/\b[a-z]/g, function(letter) {
										return letter.toUpperCase();
									});;
								}
									$( 'input[name="'+checkbox_attr_name+'"]:checked' ).attr( 'value', selectedCheck );
								}
							});
						}
					}
					// Radio checkbox field section end.

					if( undefined != field_name_class)
					{
						if( 'param[email]' == attr_name )
			            {
			              field_name_value   = field_name.val().toLowerCase();
			            }else{
							if( 'uppercase' == css_val ) {
								field_name_value   = field_name.val().toUpperCase();
							} else if( 'lowercase' == css_val ) {
								field_name_value   = field_name.val().toLowerCase();
							} else if ( 'capitalize' == css_val ) {
								field_name_value   = field_name.val().toLowerCase().replace(/\b[a-z]/g, function(letter) {
									return letter.toUpperCase();
								});;
							}
						}
						jQuery(this).attr( 'value', field_name_value );
					}
				});
				}
				
				var	data 				= form.serialize(),
					form_container  	= form.closest(".cpro-form-container");
					redirectdata 		= form.data("redirect-lead-data");
					currentBtn 			= form.find('.cp-current-clicked-btn'),
					currentShape		= form.find('.cp-current-clicked-shape'),
					currentBtnAction	= currentBtn.closest('.cp-field-html-data').data('action'),
					success_message		= currentBtn.find('.cp-button-field').data('success-message'),
					currentShpAction	= currentShape.closest('.cp-field-html-data').data('action'),
					success_message_shp	= currentShape.data('success-message'),
					loader_style        = 'loader_1',
					currObj 			= '',
					btn_old_text        = currentBtn.find('.cp-button-field').html(),
					shape_old_text      = currentShape.html();

				var button_field = currentBtn.find('.cp-button-field');

				var google_recaptcha = thisForm.find('.g-recaptcha');

				if( google_recaptcha.length > 0 ) {


					var client_side_response = thisForm.find( '[name="g-recaptcha-response"]' ).val();

					if ( client_side_response.length === 0 ) {
						thisForm.find( '.recaptcha-msg-error' ).text( "reCAPTCHA is Mandatory" );
						if( !google_recaptcha.hasClass( "error" ) ){
							google_recaptcha.addClass( "error" );
						}
						return;
					} else {
						thisForm.find('.recaptcha-msg-error' ).text('');
						google_recaptcha.removeClass( "error" );
					}
				}

				if( currentBtn.attr( 'data-type' ) == 'cp_button' || currentBtn.attr( 'data-type' ) == 'cp_gradient_button' ) {

					currObj = currentBtn;
					
					button_field.removeClass('cp-tooltip cp-tooltip-top cp-tooltip-bottom cp-loader');
					button_field.find('.cp_loader_container').removeClass('cp_success_loader_container');

					// Loader Button
					if( !currentBtn.hasClass( "cp-state-loading" ) && !currentBtn.hasClass( "cp-state-success" ) && !currentBtn.hasClass( "cp-state-error" ) ) {

						currentBtn.addClass("cp-state-loading");
						button_field.addClass('cp-loader cp-button-loading');
						currentBtn.find('.cp-loader').css('text-align', 'center');

						button_field.html("<div class='cp_loader_container'><i class='cp-button-loader-style draw " + loader_style + "'></i></div>");

						if( currentBtnAction == 'submit_n_close' || currentBtnAction == 'submit' || currentBtnAction == 'submit_n_goto_url') {
							if( '' != success_message.trim() && null != success_message.trim() )
							{
								button_field.addClass('cp-button-tooltip');
								setTimeout(function(){
								    button_field.removeClass('cp-button-tooltip');
								},5000);
							}
						}
					}
				}

				if( currentShape.attr( 'data-type' ) == 'cp_shape' ) {

					currObj = currentShape;
					
					currentShape.find('.cp-shape-container').removeClass('cp-tooltip-top cp-tooltip-bottom');
					currentShape.removeClass('cp-shape-submit-loading cp-state-success cp-error-tooltip');
					if( !currentShape.hasClass( "cp-state-loading" ) && !currentShape.hasClass( "cp-state-success" ) && !currentShape.hasClass( "cp-state-error" ) ) {
						
						currentShape.addClass('cp-shape-submit-loading');

						if( currentShpAction == 'submit_n_close' || currentShpAction == 'submit' || currentShpAction == 'submit_n_goto_url' ) {
							currentShape.find('.cp-shape-submit-loading').addClass('cp-shapes-tooltip');
						}
					}
				}

				if( typeof cp_ajax.ajax_nonce !== 'undefined' ) {
					data += '&_nonce='+  cp_ajax.ajax_nonce;
				}

				if( typeof cp_ajax.cpro_mx_valid !== 'undefined' ) {
					data += '&mx_valid='+  cp_ajax.cpro_mx_valid;
				}

				close_span = '<span class="cp-tooltip-close">&times;</span>';

				var email_field = thisForm.find( '[name="param[email]"]' );
				var email_val   = email_field.val();

				jQuery.ajax({
					url: cp_ajax.url,
					data: data,
					type: 'POST',
					dataType: 'json',
					success: function( response ) {
						var id      = currentBtn.closest(".cp-popup-wrapper").find('input[name=style_id]').val();
						var modal   = $( '.cpro-onload[data-class-id=' + id + ']' );
						var button_field = currentBtn.find('.cp-button-field');

						button_field.find('.cp_loader_container').addClass('cp_success_loader_container');
						button_field.find('.cp-button-loader-style').removeClass(loader_style).addClass('success-loader-style');
						result = response.data;
						error_msg = cp_ajax.not_connected_to_mailer;

						if ( currentBtn.find('.cp-target').hasClass('cp-button-field') ) {

							var curPos = currentBtn.find('.cp-button-field').offset().top - $( window ).scrollTop();
							if( curPos > 90 ) {
							currentBtn.find('.cp-button-field').addClass('cp-tooltip-top');
							} else {
								currentBtn.find('.cp-button-field').addClass('cp-tooltip-bottom');
							} 
							currentBtn.removeClass('cp-state-loading').addClass('cp-state-success').attr( 'style', 'z-index: 999 !important' );

						} else if( currentShape.hasClass('cp-shapes-wrap') ) {

							var curPos = currentShape.offset().top - $( window ).scrollTop();
							if( curPos > 90 ) {
								currentShape.find('.cp-shape-container').addClass('cp-tooltip-top');
							} else {
								currentShape.find('.cp-shape-container').addClass('cp-tooltip-bottom');
							}
							currentShape.removeClass('cp-state-loading').addClass('cp-state-success').attr( 'style', 'z-index: 999 !important' );
						}

						if( response === 0 ) {

							is_success = false;

							if( currentBtn.find('.cp-target').hasClass('cp-button') ) {
								currentBtn.removeClass('cp-current-clicked-btn').addClass('cp-error-tooltip');
								currentBtn.find( '.cp-btn-tooltip' ).html('<div class="cp-error-tip-content">' + error_msg + close_span + '</div>' );
							} else if ( currentShape.hasClass('cp-shapes-wrap') ) {	
								currentShape.removeClass('cp-current-clicked-shape').addClass('cp-error-tooltip');
								currentShape.find('.cp-shape-tooltip').html('<div class="cp-error-tip-content">' + error_msg + close_span + '</div>' );
							}

						} else {

							console.log( response );

							if( false != result.error ) {

								if( 'Invalid email address.' == result.error ) {
									error_msg = cp_ajax.invalid_email_id;
									invalid_email = true;
								}

								if( true == result.error ) {
									error_msg = 'Google Recaptcha Secret Key Not Valid!!!! Please contact web administrator.';
								}

								is_success = false;
								if( currentBtn.find('.cp-target').hasClass('cp-button') ) {

									currentBtn.removeClass('cp-current-clicked-btn').removeClass( 'cp-state-success' ).addClass('cp-error-tooltip');

									currentBtn.find( '.cp-button-field' ).find( '.cp_success_loader_container' ).remove(); 

									jQuery('<div/>', {
									    addClass: 'cp_loader_container cp_error_loader_container',
									}).appendTo( currentBtn.find( '.cp-button-field' ) );

									currentBtn.find( '.cp_error_loader_container' ).append( "<i class='dashicons-no-alt dashicons'></i>" );

									jQuery('<div/>', {
									    class: 'cp-error-tip-content',
									}).appendTo( currentBtn.find( '.cp-btn-tooltip' ) );

									currentBtn.find( '.cp-error-tip-content' ).append( error_msg + close_span );


								} else if ( currentShape.hasClass('cp-shapes-wrap') ) {

									currentShape.removeClass('cp-current-clicked-shape').removeClass( 'cp-state-success' ).addClass('cp-error-tooltip');
									currentShape.find('.cp-shape-tooltip').html('<div class="cp-error-tip-content">' + error_msg + close_span + '</div>' );
									currentShape.find('.cp-button-field').html("<div class='cp_loader_container cp_error_loader_container'><i class='dashicons-no-alt dashicons'></i></div>");
								}

							} else {

								is_success = true;
								if( result.error == false ) {
									if( currentBtn.find('.cp-target').hasClass('cp-button') ) {
										currentBtn.removeClass('cp-error-tooltip').addClass('cp-state-success');
										currentBtn.find('.cp-button-field').attr('data-content', success_message );
										currentBtn.find('.cp-button-field').attr('disabled', true);
										
										switch( currentBtnAction ) {

											case "submit_n_close": 
		        								setTimeout(function() {
													jQuery(document).trigger('closePopup',[modal,id]);
												}, 1200 );
											break;

											case "submit_n_goto_step":

												var step_number  = currentBtn.closest('.cp-field-html-data').data("step");
												var current_step = currentBtn.closest('.cp-popup-content').data("step");
												if( current_step != step_number ) {

													setTimeout(function() {
														cp_move_to_next_step( currentBtn, current_step, step_number );
													}, 1200 );
												}
											break;

											case "submit_n_goto_url":
												var redirect_url  = currentBtn.closest('.cp-field-html-data').data("redirect");
												var redirect_target = currentBtn.closest('.cp-field-html-data').data("redirect-target");
												var get_param = currentBtn.find( '.cp-target' ).data( "get-param" );
												var param = currentBtn.closest(".cpro-form").serializeArray();

												redirect_url = cpro_generate_url( param, redirect_url, get_param );

												setTimeout(function() {

													if( typeof redirect_target == 'undefined' || redirect_target == ''){
														redirect_target ='_self';
													}
													if( redirect_url !== '' ) {

														if( '_self' !== redirect_target ) {
															window.open( redirect_url, redirect_target );
														} else {

															if( redirect_url.indexOf( '.pdf' ) > 0 ) {
																cpro_download_pdf( redirect_url );
															} else {
																window.location = redirect_url;
															}
														}
														
														/*
														 * Close popup once submitted.
														 * Reset the form data i.e. empty the form data fields.
														 */
														jQuery(document).trigger( 'closePopup', [modal,id] );
														jQuery( '#cp_popup_id_' + id + ' .cpro-form' ).trigger("reset");
														currentBtn.find('.cp-button-field').removeAttr('style data-content disabled');
														currentBtn.find('.cp-target .cp_loader_container').remove();
														currentBtn.find('.cp-button-field').html( btn_data ).removeClass('cp-button-loading cp-tooltip-top cp-tooltip-bottom');
														currentBtn.removeClass( 'cp-state-success' );
													}
												}, 1200 );
												
											break;
										}
									} else if ( currentShape.hasClass('cp-shapes-wrap') ) {

										currentShape.removeClass('cp-error-tooltip').removeClass('cp-shape-submit-loading').addClass('cp-state-success');
										currentShape.attr( 'style', 'z-index: 35 !important' );
										currentShape.find('.cp-shape-container').attr('data-content', success_message_shp );

										switch( currentShpAction ) {

											case "submit_n_close": 

												var id      = currentShape.closest(".cp-popup-wrapper").find('input[name=style_id]').val();
		        								var modal   = $( '.cpro-onload[data-class-id=' + id + ']' );

		        								setTimeout(function() {
													jQuery(document).trigger('closePopup',[modal,id]);
												}, 1200 );
											break;

											case "submit_n_goto_step":

												currentShape.find('.cp-shape-container').removeClass('cp-tooltip-top').removeClass('cp-tooltip-bottom');
												var step_number  = currentShape.closest('.cp-field-html-data').data("step");
												var current_step = currentShape.closest('.cp-popup-content').data("step");
												if( current_step != step_number ) {

													setTimeout(function() {
														cp_move_to_next_step( currentShape, current_step, step_number );
													}, 1200 );
												}
											break;

											case 'submit_n_goto_url':
												var redirect_url  = currentShape.closest('.cp-field-html-data').data("redirect");
												var redirect_target = currentShape.closest('.cp-field-html-data').data("redirect-target");

												var get_param = currentShape.closest( '.cp-field-html-data' ).data( "get-param" );
												var param = currentShape.closest('.cpro-form').serializeArray();

												redirect_url = cpro_generate_url( param, redirect_url, get_param );

												setTimeout(function() {
													if( typeof redirect_target == 'undefined' || redirect_target == ''){
														redirect_target ='_self';
													}
													if( redirect_url !== '' ) {

														if( '_self' !== redirect_target ) {
															window.open( redirect_url, redirect_target );
														} else {
																
															if( redirect_url.indexOf( '.pdf' ) > 0 ) {
																cpro_download_pdf( redirect_url );
															} else {
																window.location = redirect_url;
															}
														}

														// close popup once submitted.
														jQuery(document).trigger( 'closePopup', [modal,id] );
													}
												}, 1200 );
											break;
										}
									} else {
										if( currentBtn.find('.cp-target').hasClass('cp-button') ) {
											currentBtn.addClass('cp-error-tooltip');
											currentBtn.find('.cp-button-field').attr('data-content', result.error );	
										} else if ( currentShape.hasClass('cp-shapes-wrap') ) {
											currentShape.removeClass('cp-current-clicked-shape').addClass('cp-error-tooltip');
											currentShape.find('.cp-shape-container').attr('data-content', result.error );
										}						
									}
								}

								var convertPopupObj = new ConvertProPopup;
								convertPopupObj._setCookie( currObj );
							}
						}

						setTimeout(function() {
							if( invalid_email ) {

								if( currentBtn.length > 0 ) {
									currentBtn.find('.cp-button-field').html( btn_old_text );
									currentBtn.find('.cp-button-field').removeClass('cp-tooltip-top cp-tooltip-bottom');
									currentBtn.closest('.cp-field-html-data').find( '.cp-error-tip-content' ).remove();
								}

								if( currentShape.length > 0 ) {
									currentShape.find('.cp-shape-container').html( shape_old_text );
									currentShape.find('.cp-shape-container').removeClass('cp-tooltip-top cp-tooltip-bottom');

									currentShape.closest('.cp-field-html-data').find( '.cp-error-tip-content' ).remove();
								}
							}

						}, 1500 );

						setTimeout(function() {

							if ( currentBtn.find('.cp-target .cp_loader_container').hasClass('cp_success_loader_container') ) {
								currentBtn.find('.cp-button-field').removeClass('cp-tooltip-top cp-tooltip-bottom');
							} else if( currentShape.hasClass('cp-shapes-wrap') && !currentShape.hasClass( 'cp-error-tooltip' ) ){
								currentShape.find('.cp-shape-container').removeClass('cp-tooltip-top cp-tooltip-bottom');
							}
							
						}, 5000 );

						currentBtn.removeClass('cp-current-clicked-btn');
						currentShape.removeClass('cp-current-clicked-shape');

						if( ! invalid_email ) {
							jQuery(document).trigger( "cp_after_form_submit", [jQuery(this), response, style_slug, email_val] );
						}

					},
					error: function(data){
						is_success = false;
						currentBtn.find('.cp-button-field').attr('data-content', data );
			        }
				});
			}

			if( ! invalid_email ) {
				jQuery(document).trigger( "cp_after_submit_action", [jQuery(this), style_id, is_success] );
			}

			e.preventDefault();

		});

		/**
		 * Dynamic tags support START.
		 * As per user selection set and send respective tags.
		 * On the basis of Checkbox, Radio, and Dropdown elements.
		*/
		$( '.cp-checkbox-field input[type="checkbox"], .cp-radio-field input[type="radio"]' ).on( 'click', function( event ) {
			var inputType         = $( this ).attr( 'type' );
			var inputTypeField    = $( this ).closest( '.cp-' + inputType + '-field' );
			var dynamicApiTagsValue = inputTypeField.find( '.cp-dynamic-api-tags' ).data( 'dynamic-api-tags' );
			if ( 'undefined' !== typeof dynamicApiTagsValue ) {
				var getApiTags          = $( this ).data( 'api-tags' );
				var updatedValue        = [];
				var tagsValue           = '';
				if ( $( this ).is( ":checked" ) ) {
					if ( '' === dynamicApiTagsValue ) {
						updatedValue.push( getApiTags );
					} else {
						updatedValue = JSON.parse( dynamicApiTagsValue );
						if ( ! updatedValue.includes( getApiTags ) ) {
							updatedValue.push( getApiTags );
						}
					}

					if ( 'radio' === inputType ) {
						tagsValue = getApiTags;
					} else {
						tagsValue = updatedValue.join( '||' );
					}
					inputTypeField.find( '.cp-dynamic-api-tags' ).data( 'dynamic-api-tags', JSON.stringify( updatedValue ) ).val( tagsValue );
				} else if ( $( this ).is( ":not(:checked)" ) && 'radio' !== inputType ) {
					updatedValue   = JSON.parse( dynamicApiTagsValue );
					var removeTags = updatedValue.indexOf( getApiTags );
					if ( -1 !== removeTags ) {
						updatedValue.splice( removeTags, 1 );
					}
					inputTypeField.find( '.cp-dynamic-api-tags' ).data( 'dynamic-api-tags', JSON.stringify( updatedValue ) ).val( updatedValue.join( '||' ) );
				}
			}
		});

		$( 'select.cp-dropdown-field' ).on( 'change', function( event ) {
			var selectedValue = $( 'option:selected', this ).data( 'api-tags' );
			$( this ).closest( '.cp-field-html-data' ).find( '.cp-dynamic-api-tags' ).val( selectedValue );
		});
		// Dynamic tags support END.

		$( document ).on( 'click', '.cp-tooltip-close', function( event ) {
				var $this       = $( this );
				var form 		= $this.closest( 'form.cpro-form' ),
				currentBtn 		= form.find('.cp-current-clicked-btn'),
				currentShape 	= form.find('.cp-current-clicked-shape');

				$this.closest( '.cp-error-tip-content' ).remove();
				form.find('.cp-button-field').removeClass('cp-button-tooltip');
				form.find('.cp-shape-container').removeClass('cp-tooltip-top')
		} );

		/* Button Scripts */
		$(document).on( 'click', '.cp-field-html-data', function() {

			var self = $(this),
				dataAction = self.data( 'action' ),
				formValidate = true;
			
			fieldActions( self, dataAction );

			if( jQuery(this).data("type") !== 'cp_button' && jQuery(this).data("type") !== 'cp_gradient_button' ) {
				self.removeClass('cp-current-clicked-btn');
			}

			if( jQuery(this).data("type") !== 'cp_shape' ) {
				self.removeClass('cp-current-clicked-shape');
			}
		});

		function cpro_generate_url( param, redirect_url, get_param ) {

			for ( param_index = 0; param_index < param.length; ++param_index ) {

				// Remove parameters with blank value
				if( '' == param[param_index].value || 
					'param[date]' == param[param_index].name || 
					'action' == param[param_index].name ||
					'style_id' == param[param_index].name ) {
					delete param[param_index];
				} else {

					var new_name = param[param_index].name.replace( 'param[', '' );
					new_name = new_name.substring( 0, new_name.length - 1 ); 
					param[param_index].name = new_name;
				}
			}

			// Remove empty paramters 
			var param = param.filter(function(v){ return v !== '' } );
			param = jQuery.param( param );

			if( true == get_param ) {
				var arr = redirect_url.split('?');
				if( arr.length == 1 ) {
				  	redirect_url = redirect_url + '?' + param
				} else {
					redirect_url = redirect_url + '&' + param
				}
			}

			return redirect_url;
		}

		/**
		 * Button Actions
		 */
		function fieldActions( btn, dataAction ) {

			var current_step     = btn.closest('.cp-popup-content').data("step");
			var id               = btn.closest(".cp-popup-wrapper").find('input[name=style_id]').val();
            var modal            = $( '.cpro-onload[data-class-id=' + id + ']' );
            var count_conversion = btn.hasClass('cpro_count_conversion');

            $(document).trigger( 'cpro_before_field_actions', [btn, id] );

            // count button click as conversion
            if( count_conversion ) {
            	// category name as per the plugin name is set.
				var category = cp_ga_object.ga_category_name;
			    var action   = 'conversion';
			    var label    =  btn.closest( ".cp-popup-container" ).data("styleslug");

			    if( 'function' === typeof cpCreateGoogleAnalyticEvent ) {
			    	cpCreateGoogleAnalyticEvent( category, action, label );
			    }

			    var convertPopupObj = new ConvertProPopup;

			    // Set conversion cookie
				convertPopupObj._setCookie( btn );
			}

			/* Button Actions without Submit */
			switch( dataAction ) {
				case 'close':
					$(document).trigger( 'closePopup', [modal,id] );
					break;

				case 'close_tab':
					window.top.close();
					break;

				case 'goto_step':

					var step_number  = btn.closest('.cp-field-html-data').data("step");
					if( current_step != step_number ) {
						var all_inputs = btn.closest('.cpro-form-container').find('input, select, textarea');

						btn.closest('.cpro-form-container').find( '.cpro-checkbox-required' ).each( function( index, elem ) {

							var checkThis = jQuery(this),
								checked = jQuery(this).find("input[type=checkbox]:checked").length;

							setTimeout( function( event ) {
								checkThis.find("input[type=checkbox]").prop( 'required', false );
								}, 2000 );

							if( checked == 0 ) {
								var firstcheckbox = jQuery(this).find("input[type=checkbox]:first");

								if( 'undefined' != typeof firstcheckbox ) {
									firstcheckbox.attr( 'required', 'required' );
									firstcheckbox[0].reportValidity();
									return false;
								}
							}
						} );

						// Google Recaptcha validate starts here.
						var google_recaptcha = btn.closest('.cpro-form-container').find('.g-recaptcha');

						if( google_recaptcha.length > 0) {

							var client_side_response = btn.closest('.cpro-form-container').find('[name="g-recaptcha-response"]').val();
							
							if (client_side_response.length === 0) {
							
								btn.closest('.cpro-form-container').find('.recaptcha-msg-error').text( "reCAPTCHA is Mandatory" );
							
								if( !google_recaptcha.hasClass( "error" ) ){
									google_recaptcha.addClass( "error" );
								}
								return false;
							} else {
								btn.closest('.cpro-form-container').find('.recaptcha-msg-error' ).text('');
								google_recaptcha.removeClass( "error" );
							}
						}// Google Recaptcha validate ends here.

						if( all_inputs.length > 0 ) {
							var proceed_to_next_step = true;
							if( current_step < step_number ) {
								$.each( all_inputs, function( index, value ) {
									if( ! value.checkValidity() ) {
										proceed_to_next_step = false;
										value.reportValidity();
										return false;
									}
								} );
							}
							if( proceed_to_next_step ) {
								cp_move_to_next_step( btn, current_step, step_number );
							}
						} else {
							cp_move_to_next_step( btn, current_step, step_number );
						}
					}
					
				break;

				case "goto_url":
					var redirect_url = btn.data("redirect"),
						target 		 = btn.data("redirect-target");
					
					if( typeof target == 'undefined' || target == '' ) {
						target ='_self';
					}

					if( '' !== redirect_url ) {

						if( '_self' !== target ) {
							window.open( redirect_url, target );
						} else {
								
							if( redirect_url.indexOf( '.pdf' ) > 0 ) {
								cpro_download_pdf( redirect_url );
							} else {
								window.location = redirect_url;
							}
						}
					}
				break;
				case "close_n_goto_url":
					var redirect_url = btn.data("redirect"),
						target 		 = btn.data("redirect-target");
					
					if( typeof target == 'undefined' || target == '' ) {
						target ='_self';
					}

					if( '' !== redirect_url ) {

						if( '_self' !== target ) {
							window.open( redirect_url, target );
						} else {
								
							if( redirect_url.indexOf( '.pdf' ) > 0 ) {
								cpro_download_pdf( redirect_url );
							} else {
								window.location = redirect_url;
							}
						}
						$(document).trigger( 'closePopup', [modal,id] );
					}
				break;
			}
		}

		/**
		 * Move to Next Step Funtion
		 */
		function cp_move_to_next_step( obj, step_id, step_number ) {
			if( obj.closest( '.cp-popup' ).find( '.cp-popup-content.cp-panel-'+step_number ).length > 0 ) {
				obj.closest( '.cp-popup' ).find( '.cp-popup-content.cp-panel-'+step_id ).removeClass('cpro-active-step');
				obj.closest( '.cp-popup' ).find( '.cp-popup-content.cp-panel-'+step_number ).addClass('cpro-active-step');
			}
		}

		function cpro_download_pdf( redirect_url ) {

            var index = redirect_url.lastIndexOf("/") + 1;
			var filename = redirect_url.substr(index);

			var link = $("<a>");
                link.attr( "href", redirect_url );
                link.attr( "download", filename );
                link.text( "cpro_anchor_link" );
                link.addClass( "cpro_dummy_anchor" );

            $('body').append(link);

            $(".cpro_dummy_anchor")[0].click();

            setTimeout(function() {
				$(".cpro_dummy_anchor").remove();												                	
            }, 500 );
		}

	});

}(jQuery, window));
