(function( $ ) {
	
	/**
	 * JavaScript class for working with third party services.
	 *
	 * @since 1.2.1
	 */

	var SendGridService = {

		parentObj : '',
		
		/**
		 * Initializes the services logic.
		 *
		 * @return void
		 * @since 1.2.1
		 */
		init: function( parentObj )
		{
			this.parentObj = parentObj;
			parentObj.listAccountFrm 	= $( '.cp-account-list-form' );
		},

		/**
		 * Validates selected lists/groups.
		 *
		 * @return void
		 * @since 1.2.1
		 */

		_validateOptions: function( parentObj ) {

			var list 	= $( 'select[name=SendGrid_list]' ),
				err 	= false;

			if( list.val() == -1 ) {
				err = cp_services.valid_list;
			}

			SendGridService.parentObj._setisValidated( err );
		},

	};

	$ ( function() {

		ConvertPlugServicesTrigger.addHook( 'cp-service-getlist-click', function( argument, obj ) {
			SendGridService.init( obj );
		});

		ConvertPlugServicesTrigger.addHook( 'cp-service-list-validate', function( argument, obj ) {
			SendGridService._validateOptions();
		});
	});

})( jQuery );
