(function( $ ) {
	
	/**
	 * JavaScript class for working with third party services.
	 *
	 * @since 1.0.0
	 */

	var ElasticEmailService = {

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
			
			// Elastic Email Events
			parentObj.listAccountFrm.off( 'change', 'select[name=elasticemail_list]' );
			parentObj.listAccountFrm.on( 'change', 'select[name=elasticemail_list]', this._elasticEMailListChange );
		},

		/**
		 * Validates selected lists/groups.
		 *
		 * @return void
		 * @since 1.0.0
		 */

		_validateOptions: function( parentObj ) {

			var list 	= $( 'select[name=elasticemail_list]' ),
				err 	= false;

			if( list.val() == -1 ) {
				err = cp_services.valid_list;
			}

			ElasticEmailService.parentObj._setisValidated( err );
		},
		
		/**
		 * Fires when the Elastic Email list select is changed.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		_elasticEMailListChange: function()
		{
			var account     = ElasticEmailService.parentObj.listAccountFrm.find( 'input[name=cp-integration-account-slug]' ),
				list        = $( this );
			
			ElasticEmailService.parentObj.ajaxCall( {
				action  : 'cppro_render_service_fields',
				account : account.val(),
				list_id : list.val(),
				addon_security: cpAddonModules.ajax_nonce,
			}, ElasticEmailService._elasticEMailListChangeComplete );
		},
		
		/**
		 * AJAX callback for when the Elastic Email list select is changed.
		 *
		 * @param {String} response The JSON response.
		 * @return void
		 * @since 1.0.0
		 */
		_elasticEMailListChangeComplete: function( response )
		{
			var data    = JSON.parse( response ),
				selectWrap  = ElasticEmailService.parentObj.listAccountFrm.find( '.cp-new-account-fields' );
			
			if( !data.error ) {
				ElasticEmailService.parentObj._updateErrorMsg( '' );
				selectWrap.append( data.html );
			} else {
				ElasticEmailService.parentObj._updateErrorMsg( data.error );
			}

			ElasticEmailService.parentObj._finishSettingsLoading();
		},

	};

	$ ( function() {

		ConvertPlugServicesTrigger.addHook( 'cp-service-getlist-click', function( argument, obj ) {
			ElasticEmailService.init( obj );
		});

		ConvertPlugServicesTrigger.addHook( 'cp-service-list-validate', function( argument, obj ) {
			ElasticEmailService._validateOptions();
		});
	});

})( jQuery );