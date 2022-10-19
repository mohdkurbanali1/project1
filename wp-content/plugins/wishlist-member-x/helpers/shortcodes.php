<?php

$shortcodes = array(
	'short_code' => array(
		'label'      => 'Descriptive Name',
		'attributes' => array(
			'attr_name' => array(
				'label'      => 'Attribute Label',
				'type'       => 'text|select|radio|checkbox',
				'options'    => array(
					'value1' => array(
						'label'      => 'Descriptive Name for Value',
						'dependency' => 'jQuery selector',
					),
				),
				'default'    => 'value1',
				'dependency' => 'jQuery selector',
			),
		),
	),
);

$shortcodes = array(
	'simple'                   => array(
		'label'      => 'Simple',
		'attributes' => array(),
	),
	'text'                     => array(
		'label'      => 'Text',
		'attributes' => array(
			'textme' => array(
				'label'   => 'Text Me',
				'default' => 'Type something',
			),
		),
	),
	'checkbox'                 => array(
		'label'      => 'Checkbox',
		'attributes' => array(
			'attr1' => array(
				'label'   => 'Attr 1',
				'type'    => 'checkbox',
				'options' => array(
					'0' => array(
						'label' => 'Zero',
					),
					'1' => array(
						'label' => 'One',
					),
					'2' => array(
						'label' => 'Two',
					),
				),
			),
			'attr2' => array(
				'label'   => 'Attr 2',
				'type'    => 'checkbox',
				'options' => array(
					'0' => array(
						'label' => 'Zero',
					),
				),
				'default' => '0',
			),
		),
	),
	'complex'                  => array(
		'label'      => 'Complex',
		'attributes' => array(
			'format' => array(
				'label'   => 'Date Format',
				'type'    => 'select-multiple',
				'options' => array(
					'm/d/Y' => array(
						'label' => 'month/day/year',
					),
					'Y-m-d' => array(
						'label' => 'year-month-day',
					),
				),
				'default' => 'm/d/Y',
			),
		),
	),
	'attr_dependency_sample1'  => array(
		'label'      => 'Attribute Dependency Sample',
		'attributes' => array(
			'attr1' => array(
				'label'   => 'Attr 1',
				'type'    => 'radio',
				'options' => array(
					'0' => array(
						'label' => 'Zero',
					),
					'1' => array(
						'label' => 'One',
					),
				),
			),
			'attr2' => array(
				'label'      => 'Attr 2',
				'type'       => 'radio',
				'options'    => array(
					'0' => array(
						'label' => 'Zero',
					),
					'1' => array(
						'label' => 'One',
					),
				),
				'default'    => '0',
				'dependency' => '[name="attr1"][value="1"]:checked',
			),
		),
	),
	'option_dependency_sample' => array(
		'label'      => 'Option Dependency Sample',
		'attributes' => array(
			'attr1' => array(
				'label'   => 'Attr 1',
				'type'    => 'radio',
				'options' => array(
					'0' => array(
						'label' => 'Zero',
					),
					'1' => array(
						'label' => 'One',
					),
					'2' => array(
						'label' => 'Two',
					),
				),
			),
			'attr2' => array(
				'label'   => 'Attr 2',
				'type'    => 'radio',
				'options' => array(
					'0' => array(
						'label'      => 'Zero',
						'dependency' => '[name="attr1"][value="1"]:checked',
					),
					'1' => array(
						'label'      => 'One',
						'dependency' => '[name="attr1"][value="0"]:checked,[name="attr1"][value="1"]:checked',
					),
				),
				'default' => '0',
			),
		),
	),
);

foreach ( $shortcodes as $shortcode => $options ) {
	$complex = (int) ! empty( $options['attributes'] );
	printf(
		'<button class="shortcode" value="%s" data-complex="%s">%s</button>',
		esc_attr( $shortcode ),
		esc_attr( $complex ),
		esc_html( $options['label'] )
	);
	if ( $complex && is_array( $options['attributes'] ) ) {
		echo '<form data-shortcode="' . esc_attr( $shortcode ) . '" id="' . esc_attr( $shortcode ) . '" class="attributes">';
		foreach ( $options['attributes'] as $attr_name => $attr_options ) {
			$dependency = trim( (string) $attr_options['dependency'] );
			printf(
				'<div %s="%s" class="attribute col-%d">',
				esc_attr( $dependency ? 'data-dependency' : '' ),
				esc_attr( $dependency ),
				(int) ( wlm_arrval( $attr_options, 'columns' ) ? wlm_arrval( 'lastresult' ) : '12' )
			);
			echo '<label>' . esc_html( wlm_arrval( $attr_options, 'label' ) ) . '</label>';
			switch ( $attr_options['type'] ) {
				case 'select-multiple':
					$multiple = ' multiple';
					// proceed to select.
				case 'select':
					echo '<select name="' . esc_attr( $attr_name ) . '"' . esc_attr( $multiple ) . '>';
					foreach ( $attr_options['options'] as $value => $value_options ) {
						$selected   = ( isset( $attr_options['default'] ) && $value == $attr_options['default'] ) ? ' selected' : '';
						$dependency = trim( (string) $value_options['dependency'] );
						printf(
							'<option %s="%s" value="%s"%s>%s</option>',
							esc_attr( $dependency ? 'data-dependency' : '' ),
							esc_attr( $dependency ),
							esc_attr( $value ),
							esc_attr( $selected ),
							esc_html( $value_options['label'] )
						);
					}
					echo '</select>';
					break;
				case 'radio':
				case 'checkbox':
					foreach ( $attr_options['options'] as $value => $value_options ) {
						$checked    = ( isset( $attr_options['default'] ) && $value == $attr_options['default'] ) ? ' checked' : '';
						$dependency = trim( (string) $value_options['dependency'] );
						printf(
							'<label %s"%s"><input type="%s" name="%s" value="%s"%s> %s</label>',
							esc_attr( $dependency ? 'data-dependency' : '' ),
							esc_attr( $dependency ),
							esc_attr( $attr_options['type'] ),
							esc_attr( $attr_name ),
							esc_attr( $value ),
							esc_attr( $checked ),
							esc_html( $value_options['label'] )
						);
					}
					break;
				case 'text':
				default:
					printf(
						'<input type="text" name="%s" value="%s" placeholder="%s">',
						esc_attr( $attr_name ),
						esc_attr( $attr_options['default'] ),
						esc_attr( $attr_options['placeholder'] )
					);
			}
			echo '</div>';
		}
		echo '</form>';
	}
}
?>
<style>
.attributes{
  display: none;
}
</style>

