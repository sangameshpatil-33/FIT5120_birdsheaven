(function( $ ) {
	
	/**
	 * JavaScript class for working with third party services.
	 *
	 * @since 1.4.0
	 */

	var SendfoxService = {

		parentObj : '',
		
		/**
		 * Initializes the services logic.
		 *
		 * @return void
		 * @since 1.4.0
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
		 * @since 1.4.0
		 */

		_validateOptions: function( parentObj ) {

			var list 	= $( 'select[name=sendfox_list]' ),
				err 	= false;

			if( list.val() == -1 ) {
				err = cp_services.valid_list;
			}
			SendfoxService.parentObj._setisValidated( err );
		},

	};

	$ ( function() {

		ConvertPlugServicesTrigger.addHook( 'cp-service-getlist-click', function( argument, obj ) {
			SendfoxService.init( obj );
		});

		ConvertPlugServicesTrigger.addHook( 'cp-service-list-validate', function( argument, obj ) {
			SendfoxService._validateOptions();
		});
	});

})( jQuery );
