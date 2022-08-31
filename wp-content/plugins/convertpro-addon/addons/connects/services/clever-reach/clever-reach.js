(function( $ ) {
	
	/**
	 * JavaScript class for working with third party services.
	 *
	 * @since 1.0.0
	 */

	var CleverReachService = {

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
			parentObj.listAccountFrm.on( 'click', 'input[name=cleverreach_double_optin]', this._cleverReachDoubleOptinChange );
			parentObj.listAccountFrm.on( 'change', 'select[name=cleverreach_lists]', this._cleverReachListChange );
		},

		/**
		 * Fires when the Clever Reach list select is changed.
		 *
		 * @return void
		 * @since 1.6.0
		 */
		_cleverReachListChange: function()
		{

			var account     = CleverReachService.parentObj.listAccountFrm.find( 'input[name=cp-integration-account-slug]' ),
				list        = $( this );

			CleverReachService.parentObj._startSettingsLoading();	
			if ( '' === list.val() ) {
				return;
			}			

			if ( $( '#cleverreach_double_optin' ).is( ":checked" ) ) {
				$( '.cp-cleverreach_double_forms-wrap' ).remove();
				CleverReachService.parentObj.ajaxCall( {
					action  : 'cppro_render_service_fields',
					account : account.val(),
					double_optin : 'yes',
					list_id : $('#cleverreach_lists').val(),
					addon_security: cpAddonModules.ajax_nonce,
				}, CleverReachService._cleverReachListChangeComplete );	
			} else {
				CleverReachService.parentObj.ajaxCall( {
					action  : 'cppro_render_service_fields',
					account : account.val(),
					double_optin : 'no_list',
					list_id : $('#cleverreach_lists').val(),
					addon_security: cpAddonModules.ajax_nonce,
				}, CleverReachService._cleverReachListChangeComplete );
			}
		},

		/**
		 * Fires when Clever Reach double optin option is changed.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		_cleverReachDoubleOptinChange: function()
		{
			var account     = CleverReachService.parentObj.listAccountFrm.find( 'input[name=cp-integration-account-slug]' ),
				list        = $( this );

			CleverReachService.parentObj._startSettingsLoading();	
			if ( '' === list.val() ) {
				return;
			}

			if ( $( list ).is( ":checked" ) ) {
				CleverReachService.parentObj.ajaxCall( {
					action  : 'cppro_render_service_fields',
					account : account.val(),
					double_optin : 'yes',
					list_id : $('#cleverreach_lists').val(),
					addon_security: cpAddonModules.ajax_nonce,
				}, CleverReachService._cleverReachDoubleOptinChangeComplete );
			} else {
				$( '.cp-cleverreach_double_forms-wrap' ).remove();	
					CleverReachService.parentObj.ajaxCall( {
					action  : 'cppro_render_service_fields',
					account : account.val(),
					double_optin : 'no',
					list_id : $('#cleverreach_lists').val(),
					addon_security: cpAddonModules.ajax_nonce,
				}, CleverReachService._cleverReachDoubleOptinChangeUncheckedComplete );
			}
		},

		/**
		 * AJAX callback for when the Clever Reach optin is checked.
		 *
		 * @param {String} response The JSON response.
		 * @return void
		 * @since 1.6.0
		 */
		_cleverReachDoubleOptinChangeComplete: function( response )
		{
			console.log(response);
			var data    = JSON.parse( response ),
				selectWrap  = CleverReachService.parentObj.listAccountFrm.find( '.cp-new-account-fields' );
			
			$( '.cp-cleverreach_double_forms-wrap' ).remove();

			if( !data.error ) {
				CleverReachService.parentObj._updateErrorMsg( '' );

				selectWrap.append( data.html );
			} else {
				CleverReachService.parentObj._updateErrorMsg( data.error );
			}
			CleverReachService.parentObj._finishSettingsLoading();
		},

		/**
		 * AJAX callback for when the Clever Reach optin is unchecked.
		 *
		 * @param {String} response The JSON response.
		 * @return void
		 * @since 1.6.0
		 */
		_cleverReachDoubleOptinChangeUncheckedComplete: function( response )
		{
			console.log(response);
			var data    = JSON.parse( response ),
				selectWrap  = CleverReachService.parentObj.listAccountFrm.find( '.cp-new-account-fields' );
			
			$( '.cp-cleverreach_double_forms-wrap' ).remove();

			if( !data.error ) {
				CleverReachService.parentObj._updateErrorMsg( '' );

				selectWrap.html( data.html );
			} else {
				CleverReachService.parentObj._updateErrorMsg( data.error );
			}
			CleverReachService.parentObj._finishSettingsLoading();
		},

		/**
		 * AJAX callback for when the Active Campaign list select is changed.
		 *
		 * @param {String} response The JSON response.
		 * @return void
		 * @since 1.6.0
		 */
		_cleverReachListChangeComplete: function( response )
		{
			var data    = JSON.parse( response ),
				selectWrap  = CleverReachService.parentObj.listAccountFrm.find( '.cp-new-account-fields' );

			$( '.cp-cleverreach_double_forms-wrap' ).remove();

			if( !data.error ) {
				CleverReachService.parentObj._updateErrorMsg( '' );

				selectWrap.append( data.html );
			} else {
				CleverReachService.parentObj._updateErrorMsg( data.error );
			}
			CleverReachService.parentObj._finishSettingsLoading();
		},

		/**
		 * Validates selected lists/groups.
		 *
		 * @return void
		 * @since 1.0.0
		 */

		_validateOptions: function( parentObj ) {

			var list 	= $( 'select[name=cleverreach_lists]' ),
				err 	= false;

			if( list.val() == -1 ) {
				err = cp_services.valid_list;
			}

			CleverReachService.parentObj._setisValidated( err );
		},
		
	};

	$ ( function() {

		ConvertPlugServicesTrigger.addHook( 'cp-service-getlist-click', function( argument, obj ) {
			CleverReachService.init( obj );
		});

		ConvertPlugServicesTrigger.addHook( 'cp-service-list-validate', function( argument, obj ) {
			CleverReachService._validateOptions();
		});
	});

})( jQuery );