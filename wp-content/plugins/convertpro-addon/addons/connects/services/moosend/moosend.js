(function( $ ) {
	
	/**
	 * JavaScript class for working with third party services.
	 *
	 * @since x.x.x
	 */

	var MoosendService = {

		parentObj : '',
		
		/**
		 * Initializes the services logic.
		 *
		 * @return void
		 * @since x.x.x
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
		 * @since x.x.x
		 */

		_validateOptions: function( parentObj ) {

			var list 	= $( 'select[name=moosend_list]' ),
				err 	= false;

			if( list.val() == -1 ) {
				err = cp_services.valid_list;
			}
			console.log("error");
			MoosendService.parentObj._setisValidated( err );
		},

	};

	$ ( function() {

		ConvertPlugServicesTrigger.addHook( 'cp-service-getlist-click', function( argument, obj ) {
			MoosendService.init( obj );
		});

		ConvertPlugServicesTrigger.addHook( 'cp-service-list-validate', function( argument, obj ) {
			MoosendService._validateOptions();
		});
	});

})( jQuery );
