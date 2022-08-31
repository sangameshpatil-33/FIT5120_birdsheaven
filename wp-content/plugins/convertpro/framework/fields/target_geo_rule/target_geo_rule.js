;(function ( $, window, undefined ) {

	var init_target_geo_rule_select2  = function( selector ) {

		var cp_all_countries = [
		    {
			    id: "AF",
			    text: "Afghanistan"
			},
			{
			    id: "AX",
			    text: "Åland Islands"
			},
			{
			    id: "AL",
			    text: "Albania"
			},
			{
			    id: "DZ",
			    text: "Algeria"
			},
			{
			    id: "AS",
			    text: "American Samoa"
			},
			{
			    id: "AD",
			    text: "Andorra"
			},
			{
			    id: "AO",
			    text: "Angola"
			},
			{
			    id: "AI",
			    text: "Anguilla"
			},
			{
			    id: "AQ",
			    text: "Antarctica"
			},
			{
			    id: "AG",
			    text: "Antigua and Barbuda"
			},
			{
			    id: "AR",
			    text: "Argentina"
			},
			{
			    id: "AM",
			    text: "Armenia"
			},
			{
			    id: "AW",
			    text: "Aruba"
			},
			{
			    id: "AU",
			    text: "Australia"
			},
			{
			    id: "AT",
			    text: "Austria"
			},
			{
			    id: "AZ",
			    text: "Azerbaijan"
			},
			{
			    id: "BS",
			    text: "Bahamas"
			},
			{
			    id: "BH",
			    text: "Bahrain"
			},
			{
			    id: "BD",
			    text: "Bangladesh"
			},
			{
			    id: "BB",
			    text: "Barbados"
			},
			{
			    id: "BY",
			    text: "Belarus"
			},
			{
			    id: "BE",
			    text: "Belgium"
			},
			{
			    id: "PW",
			    text: "Belau"
			},
			{
			    id: "BZ",
			    text: "Belize"
			},
			{
			    id: "BJ",
			    text: "Benin"
			},
			{
			    id: "BM",
			    text: "Bermuda"
			},
			{
			    id: "BT",
			    text: "Bhutan"
			},
			{
			    id: "BO",
			    text: "Bolivia"
			},
			{
			    id: "BQ",
			    text: "Bonaire, Saint Eustatius and Saba"
			},
			{
			    id: "BA",
			    text: "Bosnia and Herzegovina"
			},
			{
			    id: "BW",
			    text: "Botswana"
			},
			{
			    id: "BV",
			    text: "Bouvet Island"
			},
			{
			    id: "BR",
			    text: "Brazil"
			},
			{
			    id: "IO",
			    text: "British Indian Ocean Territory"
			},
			{
			    id: "VG",
			    text: "British Virgin Islands"
			},
			{
			    id: "BN",
			    text: "Brunei"
			},
			{
			    id: "BG",
			    text: "Bulgaria"
			},
			{
			    id: "BF",
			    text: "Burkina Faso"
			},
			{
			    id: "BI",
			    text: "Burundi"
			},
			{
			    id: "KH",
			    text: "Cambodia"
			},
			{
			    id: "CM",
			    text: "Cameroon"
			},
			{
			    id: "CA",
			    text: "Canada"
			},
			{
			    id: "CV",
			    text: "Cape Verde"
			},
			{
			    id: "KY",
			    text: "Cayman Islands"
			},
			{
			    id: "CF",
			    text: "Central African Republic"
			},	
			{
			    id: "TD",
			    text: "Chad"
			},
			{
			    id: "CL",
			    text: "Chile"
			},
			{
			    id: "CN",
			    text: "China"
			},
			{
			    id: "CX",
			    text: "Christmas Island"
			},
			{
			    id: "CC",
			    text: "Cocos (Keeling) Islands"
			},
			{
			    id: "CO",
			    text: "Colombia"
			},
			{
			    id: "KM",
			    text: "Comoros"
			},
			{
			    id: "CG",
			    text: "Congo (Brazzaville)"
			},
			{
			    id: "CD",
			    text: "Congo (Kinshasa)"
			},
			{
			    id: "CK",
			    text: "Cook Islands"
			},
			{
			    id: "CR",
			    text: "Costa Rica"
			},
			{
			    id: "HR",
			    text: "Croatia"
			},
			{
			    id: "CU",
			    text: "Cuba"
			},
			{
			    id: "CU",
			    text: "Cuba"
			},
			{
			    id: "CW",
			    text: "Curaçao"
			},
			{
			    id: "CY",
			    text: "Cyprus"
			},
			{
			    id: "CZ",
			    text: "Czech Republic"
			},
			{
			    id: "DK",
			    text: "Denmark"
			},
			{
			    id: "DJ",
			    text: "Djibouti"
			},
			{
			    id: "DM",
			    text: "Dominica"
			},
			{
			    id: "DO",
			    text: "Dominican Republic"
			},
			{
			    id: "EC",
			    text: "Ecuador"
			},
			{
			    id: "EG",
			    text: "Egypt"
			},
			{
			    id: "SV",
			    text: "El Salvador"
			},
			{
			    id: "GQ",
			    text: "Equatorial Guinea"
			},
			{
			    id: "ER",
			    text: "Eritrea"
			},
			{
			    id: "EE",
			    text: "Estonia"
			},
			{
			    id: "ET",
			    text: "Ethiopia"
			},
			{
			    id: "FK",
			    text: "Falkland Islands"
			},
			{
			    id: "FO",
			    text: "Faroe Islands"
			},
			{
			    id: "FJ",
			    text: "Fiji"
			},
			{
			    id: "FI",
			    text: "Finland"
			},
			{
			    id: "FR",
			    text: "France"
			},
			{
			    id: "GF",
			    text: "French Guiana"
			},
			{
			    id: "PF",
			    text: "French Polynesia"
			},
			{
			    id: "TF",
			    text: "French Southern Territories"
			},
			{
			    id: "GA",
			    text: "Gabon"
			},
			{
			    id: "GM",
			    text: "Gambia"
			},
			{
			    id: "GE",
			    text: "Georgia"
			},
			{
			    id: "DE",
			    text: "Germany"
			},
			{
			    id: "GH",
			    text: "Ghana"
			},
			{
			    id: "GI",
			    text: "Gibraltar"
			},
			{
			    id: "GR",
			    text: "Greece"
			},
			{
			    id: "GL",
			    text: "Greenland"
			},
			{
			    id: "GD",
			    text: "Grenada"
			},
			{
			    id: "GP",
			    text: "Guadeloupe"
			},
			{
			    id: "GU",
			    text: "Guam"
			},
			{
			    id: "GT",
			    text: "Guatemala"
			},
			{
			    id: "GG",
			    text: "Guernsey"
			},
			{
			    id: "GN",
			    text: "Guinea"
			},
			{
			    id: "GW",
			    text: "Guinea-Bissau"
			},
			{
			    id: "GY",
			    text: "Guyana"
			},
			{
			    id: "HT",
			    text: "Haiti"
			},
			{
			    id: "HM",
			    text: "Heard Island and McDonald Islands"
			},
			{
			    id: "HN",
			    text: "Honduras"
			},
			{
			    id: "HK",
			    text: "Hong Kong"
			},
			{
			    id: "HU",
			    text: "Hungary"
			},
			{
			    id: "IS",
			    text: "Iceland"
			},
			{
			    id: "IN",
			    text: "India"
			},
			{
			    id: "ID",
			    text: "Indonesia"
			},
			{
			    id: "IR",
			    text: "Iran"
			},
			{
			    id: "IQ",
			    text: "Iraq"
			},
			{
			    id: "IE",
			    text: "Ireland"
			},
			{
			    id: "IM",
			    text: "Isle of Man"
			},
			{
			    id: "IL",
			    text: "Israel"
			},
			{
			    id: "IT",
			    text: "Italy"
			},
			{
			    id: "CI",
			    text: "Ivory Coast"
			},
			{
			    id: "JM",
			    text: "Jamaica"
			},
			{
			    id: "JP",
			    text: "Japan"
			},
			{
			    id: "JE",
			    text: "Jersey"
			},
			{
			    id: "JO",
			    text: "Jordan"
			},
			{
			    id: "KZ",
			    text: "Kazakhstan"
			},
			{
			    id: "KE",
			    text: "Kenya"
			},
			{
			    id: "KI",
			    text: "Kiribati"
			},
			{
			    id: "KW",
			    text: "Kuwait"
			},
			{
			    id: "KG",
			    text: "Kyrgyzstan"
			},
			{
			    id: "LA",
			    text: "Laos"
			},
			{
			    id: "LV",
			    text: "Latvia"
			},
			{
			    id: "LB",
			    text: "Lebanon"
			},
			{
			    id: "LS",
			    text: "Lesotho"
			},
			{
			    id: "LR",
			    text: "Liberia"
			},
			{
			    id: "LY",
			    text: "Libya"
			},
			{
			    id: "LI",
			    text: "Liechtenstein"
			},
			{
			    id: "LT",
			    text: "Lithuania"
			},
			{
			    id: "LU",
			    text: "Luxembourg"
			},
			{
			    id: "MO",
			    text: "Macao S.A.R., China"
			},
			{
			    id: "MK",
			    text: "Macedonia"
			},
			{
			    id: "MG",
			    text: "Madagascar"
			},
			{
			    id: "MW",
			    text: "Malawi"
			},
			{
			    id: "MY",
			    text: "Malaysia"
			},
			{
			    id: "MV",
			    text: "Maldives"
			},
			{
			    id: "ML",
			    text: "Mali"
			},
			{
			    id: "MT",
			    text: "Malta"
			},
			{
			    id: "MH",
			    text: "Marshall Islands"
			},
			{
			    id: "MQ",
			    text: "Martinique"
			},
			{
			    id: "MR",
			    text: "Mauritania"
			},
			{
			    id: "MU",
			    text: "Mauritius"
			},
			{
			    id: "YT",
			    text: "Mayotte"
			},
			{
			    id: "MX",
			    text: "Mexico"
			},
			{
			    id: "FM",
			    text: "Micronesia"
			},
			{
			    id: "MD",
			    text: "Moldova"
			},
			{
			    id: "MC",
			    text: "Monaco"
			},
			{
			    id: "MN",
			    text: "Mongolia"
			},
			{
			    id: "ME",
			    text: "Montenegro"
			},
			{
			    id: "MS",
			    text: "Montserrat"
			},
			{
			    id: "MA",
			    text: "Morocco"
			},
			{
			    id: "MZ",
			    text: "Mozambique"
			},
			{
			    id: "MM",
			    text: "Myanmar"
			},
			{
			    id: "NA",
			    text: "Namibia"
			},
			{
			    id: "NR",
			    text: "Nauru"
			},
			{
			    id: "NP",
			    text: "Nepal"
			},
			{
			    id: "NL",
			    text: "Netherlands"
			},
			{
			    id: "NC",
			    text: "New Caledonia"
			},
			{
			    id: "NZ",
			    text: "New Zealand"
			},
			{
			    id: "NI",
			    text: "Nicaragua"
			},
			{
			    id: "NE",
			    text: "Niger"
			},
			{
			    id: "NG",
			    text: "Nigeria"
			},
			{
			    id: "NU",
			    text: "Niue"
			},
			{
			    id: "NF",
			    text: "Norfolk Island"
			},
			{
			    id: "MP",
			    text: "Northern Mariana Islands"
			},
			{
			    id: "KP",
			    text: "North Korea"
			},
			{
			    id: "NO",
			    text: "Norway"
			},
			{
			    id: "OM",
			    text: "Oman"
			},
			{
			    id: "PK",
			    text: "Pakistan"
			},
			{
			    id: "PS",
			    text: "Palestinian Territory"
			},
			{
			    id: "PA",
			    text: "Panama"
			},
			{
			    id: "PG",
			    text: "Papua New Guinea"
			},
			{
			    id: "PY",
			    text: "Paraguay"
			},
			{
			    id: "PE",
			    text: "Peru"
			},
			{
			    id: "PH",
			    text: "Philippines"
			},
			{
			    id: "PN",
			    text: "Pitcairn"
			},
			{
			    id: "PL",
			    text: "Poland"
			},
			{
			    id: "PT",
			    text: "Portugal"
			},
			{
			    id: "PR",
			    text: "Puerto Rico"
			},
			{
			    id: "QA",
			    text: "Qatar"
			},
			{
			    id: "RE",
			    text: "Reunion"
			},
			{
			    id: "RO",
			    text: "Romania"
			},
			{
			    id: "RU",
			    text: "Russia"
			},
			{
			    id: "RW",
			    text: "Rwanda"
			},
			{
			    id: "BL",
			    text: "Saint Barthélemy"
			},
			{
			    id: "SH",
			    text: "Saint Helena"
			},
			{
			    id: "KN",
			    text: "Saint Kitts and Nevis"
			},
			{
			    id: "LC",
			    text: "Saint Lucia"
			},
			{
			    id: "MF",
			    text: "Saint Martin (French part)"
			},
			{
			    id: "SX",
			    text: "Saint Martin (Dutch part)"
			},
			{
			    id: "PM",
			    text: "Saint Pierre and Miquelon"
			},
			{
			    id: "VC",
			    text: "Saint Vincent and the Grenadines"
			},
			{
			    id: "SM",
			    text: "San Marino"
			},
			{
			    id: "ST",
			    text: "São Tomé and Príncipe"
			},
			{
			    id: "SA",
			    text: "Saudi Arabia"
			},
			{
			    id: "SN",
			    text: "Senegal"
			},
			{
			    id: "RS",
			    text: "Serbia"
			},
			{
			    id: "SC",
			    text: "Seychelles"
			},
			{
			    id: "SL",
			    text: "Sierra Leone"
			},
			{
			    id: "SG",
			    text: "Singapore"
			},
			{
			    id: "SK",
			    text: "Slovakia"
			},
			{
			    id: "SI",
			    text: "Slovenia"
			},
			{
			    id: "SB",
			    text: "Solomon Islands"
			},
			{
			    id: "SO",
			    text: "Somalia"
			},
			{
			    id: "ZA",
			    text: "South Africa"
			},
			{
			    id: "GS",
			    text: "South Georgia\/Sandwich Islands"
			},
			{
			    id: "KR",
			    text: "South Korea"
			},
			{
			    id: "SS",
			    text: "South Sudan"
			},
			{
			    id: "ES",
			    text: "Spain"
			},
			{
			    id: "LK",
			    text: "Sri Lanka"
			},
			{
			    id: "SD",
			    text: "Sudan"
			},
			{
			    id: "SR",
			    text: "Suriname"
			},
			{
			    id: "SJ",
			    text: "Svalbard and Jan Mayen"
			},
			{
			    id: "SZ",
			    text: "Swaziland"
			},
			{
			    id: "SE",
			    text: "Sweden"
			},
			{
			    id: "CH",
			    text: "Switzerland"
			},
			{
			    id: "SY",
			    text: "Syria"
			},
			{
			    id: "TW",
			    text: "Taiwan"
			},
			{
			    id: "TJ",
			    text: "Tajikistan"
			},
			{
			    id: "TZ",
			    text: "Tanzania"
			},
			{
			    id: "TH",
			    text: "Thailand"
			},
			{
			    id: "TL",
			    text: "Timor-Leste"
			},
			{
			    id: "TG",
			    text: "Togo"
			},
			{
			    id: "TK",
			    text: "Tokelau"
			},
			{
			    id: "TO",
			    text: "Tonga"
			},
			{
			    id: "TT",
			    text: "Trinidad and Tobago"
			},
			{
			    id: "TN",
			    text: "Tunisia"
			},
			{
			    id: "TR",
			    text: "Turkey"
			},
			{
			    id: "TM",
			    text: "Turkmenistan"
			},
			{
			    id: "TC",
			    text: "Turks and Caicos Islands"
			},
			{
			    id: "TV",
			    text: "Tuvalu"
			},
			{
			    id: "UG",
			    text: "Uganda"
			},
			{
			    id: "UA",
			    text: "Ukraine"
			},
			{
			    id: "AE",
			    text: "United Arab Emirates"
			},
			{
			    id: "GB",
			    text: "United Kingdom (UK)"
			},
			{
			    id: "US",
			    text: "United States (US)"
			},
			{
			    id: "UM",
			    text: "United States (US) Minor Outlying Islands"
			},
			{
			    id: "VI",
			    text: "United States (US) Virgin Islands"
			},
			{
			    id: "UY",
			    text: "Uruguay"
			},
			{
			    id: "UZ",
			    text: "Uzbekistan"
			},
			{
			    id: "VU",
			    text: "Vanuatu"
			},
			{
			    id: "VA",
			    text: "Vatican"
			},
			{
			    id: "VE",
			    text: "Venezuela"
			},
			{
			    id: "VN",
			    text: "Vietnam"
			},
			{
			    id: "WF",
			    text: "Wallis and Futuna"
			},
			{
			    id: "EH",
			    text: "Western Sahara"
			},
			{
			    id: "WS",
			    text: "Samoa"
			},
			{
			    id: "YE",
			    text: "Yemen"
			},
			{
			    id: "ZM",
			    text: "Zambia"
			},
			{
			    id: "ZW",
			    text: "Zimbabwe"
			},
		];
		
		$(selector).cpselect2({

			placeholder: cp_pro.country_filters,
            data: cp_all_countries,
			minimumInputLength: 2,
		});
	};

	var update_target_geo_rule_input = function(wrapper) {

		var rule_input 		= wrapper.find('.cp-target_geo_rule-input');

		var old_value = rule_input.val();
		var new_value = [];
		
		wrapper.find('.cp-target-geo-rule-condition').each(function(i) {
			
			var $this 			= $(this);
			var temp_obj 		= {};
			var rule_condition 	= $this.find('select.target_geo_rule-condition');
			var specific_page 	= $this.find('select.target_geo_rule-specific-countries-page');

			var rule_condition_val 	= rule_condition.val();
			var specific_page_val 	= specific_page.val();
			
			if ( '' != rule_condition_val ) {

				temp_obj = {
					type 	: rule_condition_val,
					specific: specific_page_val
				} 
				
				new_value.push( temp_obj );
			};
		})

		var rules_string = JSON.stringify( new_value );
		rule_input.val( rules_string );
	};

	var update_close_button = function(wrapper) {
		
		type 		= wrapper.closest('.cp-target-geo-rule-wrapper').attr('data-type');
		rules 		= wrapper.find('.cp-target-geo-rule-condition');
		show_close	= false;

		if ( 'display' == type ) {
			if ( rules.length > 1 ) {
				show_close = true;
			}
		}else{
			show_close = true;
		}

		rules.each(function() {
			
			if ( show_close ) {
				jQuery(this).find('.target_geo_rule-condition-delete').removeClass('cp-hidden');
			}else{
				jQuery(this).find('.target_geo_rule-condition-delete').addClass('cp-hidden');
			}
		});
	};

	var update_exclusion_button = function( force_show, force_hide ) {
		
		var display_on = $('.cp-target-geo-rule-display-on-wrap');
		var exclude_on = $('.cp-target-geo-rule-exclude-on-wrap');
		
		var exclude_field_wrap = exclude_on.closest('.cp-element-container');
		var add_exclude_block  = $('.target_geo_rule-add-exclusion-rule');
		var exclude_val = exclude_on.find( '.cp-target_geo_rule-input' ).val();

		if ( true == force_hide ) {
			exclude_field_wrap.addClass( 'cp-hidden' );
			add_exclude_block.removeClass( 'cp-hidden' );
		}else if( true == force_show ){
			exclude_field_wrap.removeClass( 'cp-hidden' );
			add_exclude_block.addClass( 'cp-hidden' );
		}else{
			
			if ( '' == exclude_val || '[]' == exclude_val ) {
				exclude_field_wrap.addClass( 'cp-hidden' );
				add_exclude_block.removeClass( 'cp-hidden' );
			}else{
				exclude_field_wrap.removeClass( 'cp-hidden' );
				add_exclude_block.addClass( 'cp-hidden' );
			}
		}
	};
	
	$(document).ready(function($) {

		jQuery( '.cp-target-geo-rule-condition' ).each( function() {


			var $this 			= $( this ),
				condition 		= $this.find('select.target_geo_rule-condition'),
				condition_val 	= condition.val(),
				specific_page 	= $this.find('.target_geo_rule-specific-countries-page-wrap');

			if( 'specifics-geo' == condition_val ) {
				specific_page.slideDown( 300 );
			} else {
				specific_page.slideUp( 300 );
			}
		} );

		
		jQuery('select.target-geo-rule-select2').each(function(index, el) {
			
			init_target_geo_rule_select2( el );
		});

		jQuery('.cp-target-geo-rule-selector-wrapper').each(function() {
			update_close_button( jQuery(this) );
		})

		/* Show hide exclusion button */
		update_exclusion_button();

		jQuery( document ).on( 'change', '.cp-target-geo-rule-condition select.target_geo_rule-condition' , function( e ) {
			
			var $this 		= jQuery(this),
				this_val 	= $this.val(),
				field_wrap 	= $this.closest('.cp-target-geo-rule-wrapper');

			if( 'specifics-geo' == this_val ) {
				$this.closest( '.cp-target-geo-rule-condition' ).find( '.target_geo_rule-specific-countries-page-wrap' ).slideDown( 300 );
			} else {
				$this.closest( '.cp-target-geo-rule-condition' ).find( '.target_geo_rule-specific-countries-page-wrap' ).slideUp( 300 );
				
				// var sl2 = jQuery( this ).closest( '.cp-element-container' ).find( '.cp-select2-wrap select.select2-group_filters-dropdown' );
				// sl2.val( null );
				// sl2.trigger( 'change' );
			}

			update_target_geo_rule_input( field_wrap );
		} );

		jQuery( '.cp-target-geo-rule-selector-wrapper' ).on( 'change', '.target-geo-rule-select2', function(e) {
			var $this 		= jQuery( this ),
				field_wrap 	= $this.closest('.cp-target-geo-rule-wrapper');

			update_target_geo_rule_input( field_wrap );
		});
		
		jQuery( '.cp-target-geo-rule-selector-wrapper' ).on( 'click', '.target_geo_rule-add-rule-wrap a', function(e) {
			e.preventDefault();
			e.stopPropagation();
			var $this 	= jQuery( this ),
				id 		= $this.attr( 'data-rule-id' ),
				new_id 	= parseInt(id) + 1,
				type 	= $this.attr( 'data-rule-type' ),
				rule_wrap = $this.closest('.cp-target-geo-rule-selector-wrapper').find('.target_geo_rule-builder-wrap'),
				template  = wp.template( 'cp-target-geo-rule-condition' ),
				field_wrap 		= $this.closest('.cp-target-geo-rule-wrapper');

			rule_wrap.append( template( { id : new_id, type : type } ) );
			
			init_target_geo_rule_select2( '.cp-target-geo-rule-'+type+'-on .cp-target-geo-rule-'+new_id + ' .target-geo-rule-select2' );
			
			$this.attr( 'data-rule-id', new_id );

			update_close_button( field_wrap );
		});

		jQuery( '.cp-target-geo-rule-selector-wrapper' ).on( 'click', '.target_geo_rule-condition-delete', function(e) {
			var $this 			= jQuery( this ),
				rule_condition 	= $this.closest('.cp-target-geo-rule-condition'),
				field_wrap 		= $this.closest('.cp-target-geo-rule-wrapper');
				cnt 			= 0,
				data_type 		= field_wrap.attr( 'data-type' );

			if ( 'exclude' == data_type && field_wrap.find('.cp-target-geo-rule-condition').length == 1 && '0' == rule_condition.attr('data-rule') ) {
				
				field_wrap.find('.target_geo_rule-condition').val('');
				field_wrap.find('.target_geo_rule-specific-countries-page').val('');
				field_wrap.find('.target_geo_rule-condition').trigger('change');
				update_exclusion_button( false, true );

			}else{
				rule_condition.remove();
			}

			field_wrap.find('.cp-target-geo-rule-condition').each(function(i) {
				var condition 	= jQuery( this )
					old_rule_id = condition.attr('data-rule');
					
				condition.attr( 'data-rule', i );

				condition.removeClass('cp-target-geo-rule-'+old_rule_id).addClass('cp-target-geo-rule-'+i);

				cnt = i;
			});

			field_wrap.find('.target_geo_rule-add-rule-wrap a').attr( 'data-rule-id', cnt )

			update_close_button( field_wrap );
			update_target_geo_rule_input( field_wrap );
		});
		
		jQuery( '.cp-target-geo-rule-selector-wrapper' ).on( 'click', '.target_geo_rule-add-exclusion-rule a', function(e) {
			e.preventDefault();
			e.stopPropagation();
			update_exclusion_button( true );
		});
		
	});

}(jQuery, window));