//-------------------------------------\\
//----- Social Activity Shortcode -----\\
//-------------------------------------\\
wp.blocks.registerBlockType( 'um-block/um-user-profile-wall', {
	title: wp.i18n.__( 'Social Activity Feed', 'um-activity' ),
	description: wp.i18n.__( 'Used on the user profile page', 'um-activity' ),
	icon: 'businessman',
	category: 'um-blocks',
	attributes: { // Necessary for saving block content.
		content: {
			source: 'html',
			selector: 'p'
		},
		user_id: {
			type: 'select'
		},
		hashtag: {
			type: 'string'
		},
		wall_post: {
			type: 'number'
		},
		user_wall: {
			type: 'boolean'
		}
	},

	edit: function( props ) {
		var user_id      = props.attributes.user_id,
			hashtag      = props.attributes.hashtag,
			wall_post    = props.attributes.wall_post,
			user_wall    = props.attributes.user_wall,
			attributes   = props.attributes,
			content      = props.attributes.content;

		function onChangeContent( newContent ) {
			props.setAttributes( { content: newContent } );
		}

		function umShortcode() {

			var shortcode = '';

			if ( attributes.user_id !== undefined ) {

				shortcode = '[ultimatemember_wall user_id="' + attributes.user_id + '"';

				if( attributes.hashtag !== undefined ) {
					shortcode = shortcode + ' hashtag="' + attributes.hashtag + '"';
				}

				if( attributes.wall_post !== undefined ) {
					shortcode = shortcode + ' wall_post="' + attributes.wall_post + '"';
				}

				if( attributes.user_wall !== undefined ) {
					shortcode = shortcode + ' user_wall="' + attributes.user_wall + '"';
				}

				shortcode = shortcode + ']';

				props.setAttributes( { content: shortcode } );

			} else {
				props.setAttributes( { content: '[ultimatemember_activity]' } );
			}
		}

		return [
			wp.element.createElement(
				"div",
				{
					className: "um-social-activity-wrapper"
				},
				wp.element.createElement(
					wp.components.SelectControl,
					{
						label: wp.i18n.__( 'Select User', 'um-activity' ),
						className: "um_select_users",
						type: 'number',
						value: props.attributes.user_id,
						options: function() {

							var options   = [],
								user_list = '';

							wp.apiFetch( { path : '/wp/v2/users/' } ).then(
								function ( answer ) {
									user_list = answer;

									user_list.map( function( user ) {
										options.push(
											{
												label: user.name,
												value: user.id
											}
										);
									});

								}
							);

							return options;
						},
						onChange: function onChange( value ) {
							props.setAttributes({ user_id: value });
							attributes['user_id'] = value;
							umShortcode();
						}
					}
				),
				wp.element.createElement(
					wp.components.TextControl,
					{
						className: "um_hashtag",
						label: wp.i18n.__( 'Hashtag', 'um-activity' ),
						value: props.attributes.hashtag,
						onChange: function onChange( value ) {
							props.setAttributes({ hashtag: value });
							attributes['hashtag'] = value;
							umShortcode();
						}
					}
				)

			),
			wp.element.createElement(
				wp.editor.InspectorControls,
				{},
				wp.element.createElement(
					wp.components.PanelBody,
					{
						title: wp.i18n.__( 'Shortcode Attribute', 'um-activity' )
					},
					wp.element.createElement(
						wp.components.RangeControl,
						{
							label: wp.i18n.__( 'Show the form on the wall?', 'um-activity' ),
							value: props.attributes.wall_post,
							min: 2,
							max: 20,
							onChange: function onChange( value ) {
								props.setAttributes({ wall_post: value });
								attributes['wall_post'] = value;
								umShortcode();
							}
						}
					),
					wp.element.createElement(
						wp.components.ToggleControl,
						{
							label: wp.i18n.__( 'Show the form on the wall?', 'um-activity' ),
							checked: props.attributes.user_wall,
							onChange: function onChange( value ) {
								props.setAttributes({ user_wall: value });
								attributes['user_wall'] = value;
								umShortcode();
							}
						}
					)
				)
			)
		]
	},

	save: function( props ) {

		return wp.element.createElement(
			wp.editor.RichText.Content,
			{
				tagName: 'p',
				className: props.className,
				value: props.attributes.content
			}
		);
	}
});