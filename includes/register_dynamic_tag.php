<?php

namespace RepRelCon;

if ( ! \defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

\add_action( 'elementor/dynamic_tags/register', function( $dynamic_tags_manager ) {
    $dynamic_tags_manager->register_group( 'RepRelCon_tags', [ 'title' => 'ACF Connector' ] );

    class Acf_Repeater_Sub_Field_Tag extends \Elementor\Core\DynamicTags\Data_Tag {

        public function get_name() {
            return 'acf-repeater-sub-field';
        }

        public function get_title() {
            return \__( 'ACF Repeater Sub Field', 'repeaters-relationships-connector-acf-elementor' );
        }

        public function get_group() {
            return 'RepRelCon_tags';
        }

        public function get_categories() {
            return [
                \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
                \Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
                \Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
            ];
        }

        protected function _register_controls() {
            $this->add_control(
                'sub_field',
                [
                    'label'   => \__( 'Sub Field', 'repeaters-relationships-connector-acf-elementor' ),
                    'type'    => \Elementor\Controls_Manager::SELECT,
                    'options' => $this->get_repeater_sub_field_options(),
                ]
            );
        }

        public function get_value( array $options = [] ) {
            global $post;
            if ( ! isset( $post->acf_repeater_data ) ) {
                return 'No ACF repeater data found';
            }

            $combined_field = $this->get_settings( 'sub_field' );
            if ( empty( $combined_field ) ) {
                return '';
            }

            $parts = \explode( ':', $combined_field );
            if ( \count( $parts ) !== 2 ) {
                return 'Invalid sub-field format';
            }
            $sub_field_name = $parts[1];

            $row = $post->acf_repeater_data;

            if ( ! isset( $row[ $sub_field_name ] ) ) {
                return '';
            }

            $value = $row[ $sub_field_name ];

            if ( \is_numeric( $value ) ) {
                $image_url = \wp_get_attachment_image_url( $value, 'full' );
                if ( $image_url ) {
                    return [
                        'id'  => $value,
                        'url' => $image_url,
                    ];
                }
            }

            return $value;
        }

        private function get_repeater_sub_field_options() {
            $options = [];
            if ( \function_exists( 'acf_get_field_groups' ) ) {
                $field_groups = \acf_get_field_groups();
                if ( ! empty( $field_groups ) ) {
                    foreach ( $field_groups as $field_group ) {
                        $acf_fields = \acf_get_fields( $field_group['key'] );
                        if ( ! empty( $acf_fields ) ) {
                            foreach ( $acf_fields as $field ) {
                                if ( $field['type'] === 'repeater' && ! empty( $field['sub_fields'] ) ) {
                                    foreach ( $field['sub_fields'] as $sub_field ) {
                                        $key               = $field['name'] . ':' . $sub_field['name'];
                                        $label             = $field['label'] . ' > ' . $sub_field['label'];
                                        $options[ $key ] = $label;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            \asort( $options );

            return $options;
        }
    }

    $dynamic_tags_manager->register( new Acf_Repeater_Sub_Field_Tag() );
} );
