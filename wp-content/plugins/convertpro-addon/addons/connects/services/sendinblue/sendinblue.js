(function( $ ) {
	
	/**
	 * JavaScript class for working with third party services.
	 *
	 * @since 1.0.0
	 */

	var SendInBlueService = {

		parentObj : '',
		
		/**
		 * Initializes the services logic.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		init: function( parentObj )
		{
			this.parentObj = parentObj;
			parentObj.listAccountFrm 	= $( '.cp-account-list-form' );
			parentObj.listAccountFrm.on( 'click', 'input[name=sendinblue_double_optin]', this._sendinblueDoubleOptinChange );
		},

		/**
		 * Validates selected lists/groups.
		 *
		 * @return void
		 * @since 1.0.0
		 */

		_validateOptions: function( parentObj ) {

			var list 	= $( 'select[name=sendinblue_lists]' ),
				err 	= false;

			if( list.val() == -1 ) {
				err = cp_services.valid_list;
			}

			SendInBlueService.parentObj._setisValidated( err );
		},

		/**
		 * Fires when SendinBlue double optin option is changed.
		 *
		 * @return void
		 * @since x.x.x
		 */
		_sendinblueDoubleOptinChange: function()
		{
			var account     = SendInBlueService.parentObj.listAccountFrm.find( 'input[name=cp-integration-account-slug]' ),
				list        = $( this );

			SendInBlueService.parentObj._startSettingsLoading();	

			if ( '' === list.val() ) {
				return;
			}

			if ( $( list ).is( ":checked" ) ) {
				SendInBlueService.parentObj.ajaxCall( {
					action  : 'cppro_render_service_fields',
					account : account.val(),
					double_optin : 'yes',
					addon_security: cpAddonModules.ajax_nonce,
				}, SendInBlueService._sendinblueDoubleOptinChangeComplete );
			} else {
				$( '.cp-sendinblue_template_ids-wrap' ).remove();
				$( '.cp-sendinblue_redirection_url-wrap' ).remove();
				SendInBlueService.parentObj.ajaxCall( {
					action  : 'cppro_render_service_fields',
					account : account.val(),
					double_optin : 'no',
					addon_security: cpAddonModules.ajax_nonce,
				}, SendInBlueService._sendinblueDoubleOptinChangeUncheckedComplete );
			}
		},

		/**
		 * AJAX callback for when the SendinBlue optin is checked.
		 *
		 * @param {String} response The JSON response.
		 * @return void
		 * @since x.x.x
		 */
		_sendinblueDoubleOptinChangeComplete: function( response )
		{
			var data        = JSON.parse( response ),
				selectWrap  = SendInBlueService.parentObj.listAccountFrm.find( '.cp-new-account-fields' );
			
			$( '.cp-sendinblue_template_ids-wrap' ).remove();
			$( '.cp-sendinblue_redirection_url-wrap' ).remove();

			if( !data.error ) {
				SendInBlueService.parentObj._updateErrorMsg( '' );

				selectWrap.append( data.html );
			} else {
				SendInBlueService.parentObj._updateErrorMsg( data.error );
			}
			SendInBlueService.parentObj._finishSettingsLoading();
		},

		/**
		 * AJAX callback for when the SendinBlue optin is unchecked.
		 *
		 * @param {String} response The JSON response.
		 * @return void
		 * @since x.x.x
		 */
		_sendinblueDoubleOptinChangeUncheckedComplete: function( response )
		{
			var data       = JSON.parse( response ),
				selectWrap = SendInBlueService.parentObj.listAccountFrm.find( '.cp-new-account-fields' );
			
			$( '.cp-sendinblue_template_ids-wrap' ).remove();
			$( '.cp-sendinblue_redirection_url-wrap' ).remove();

			if( !data.error ) {
				SendInBlueService.parentObj._updateErrorMsg( '' );

				selectWrap.html( data.html );
			} else {
				SendInBlueService.parentObj._updateErrorMsg( data.error );
			}
			SendInBlueService.parentObj._finishSettingsLoading();
		},
	};

	$ ( function() {

		ConvertPlugServicesTrigger.addHook( 'cp-service-getlist-click', function( argument, obj ) {
			SendInBlueService.init( obj );
		});

		ConvertPlugServicesTrigger.addHook( 'cp-service-list-validate', function( argument, obj ) {
			SendInBlueService._validateOptions();
		});
	});

})( jQuery );