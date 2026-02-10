<?php

namespace RepRelCon;

if ( ! \defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

\add_action( 'elementor/controls/register', function( $controls_manager ) {

    class Acf_Query_Control_Wrapper extends \ElementorPro\Modules\QueryControl\Controls\Group_Control_Related {

        protected function get_fields_array( $name ) {
            $fields = parent::get_fields_array( $name );

            $this->remove_unneeded_controls( $fields );
            $this->add_acf_to_dropdown( $fields );

            $fields['acf_repeater_name'] = [
                'label'     => \esc_html__( 'Repeater Name', 'repeaters-relationships-connector-acf-elementor' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'options'   => $this->get_acf_repeater_field_names(),
                'condition' => [
                    'post_type' => 'acf_repeater',
                ],
            ];

            $fields['acf_relation_name'] = [
                'label'     => \esc_html__( 'Relationship Name', 'repeaters-relationships-connector-acf-elementor' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'options'   => $this->get_acf_relation_field_names(),
                'condition' => [
                    'post_type' => 'acf_relation',
                ],
            ];

            $fields['acf_data_source'] = [
                'label'     => \esc_html__( 'Data Source', 'repeaters-relationships-connector-acf-elementor' ),
                'type'      => \Elementor\Controls_Manager::SELECT,
                'default'   => 'current_post',
                'options'   => $this->get_acf_data_source_options(),
                'condition' => [
                    'post_type' => [ 'acf_repeater', 'acf_relation' ],
                ],
            ];

            return $fields;
        }

        private function remove_unneeded_controls( &$fields ) {
            $exclude_from_acf = [ 'acf_repeater', 'acf_relation' ];

            $fields['query_include']['condition']['post_type!'] = \array_merge( $fields['query_include']['condition']['post_type!'], $exclude_from_acf );
            $fields['query_exclude']['condition']['post_type!'] = \array_merge( $fields['query_exclude']['condition']['post_type!'], $exclude_from_acf );
            $fields['include']['condition']['post_type!']       = \array_merge( $fields['include']['condition']['post_type!'], $exclude_from_acf );
            $fields['exclude']['condition']['post_type!']       = \array_merge( $fields['exclude']['condition']['post_type!'], $exclude_from_acf );
            $fields['select_date']['condition']['post_type!']   = \array_merge( $fields['select_date']['condition']['post_type!'], $exclude_from_acf );

            if ( ! \is_array( $fields['orderby']['condition']['post_type!'] ) ) {
                $fields['orderby']['condition']['post_type!'] = [ $fields['orderby']['condition']['post_type!'] ];
            }
            $fields['orderby']['condition']['post_type!'] = \array_merge( $fields['orderby']['condition']['post_type!'], $exclude_from_acf );

            if ( ! \is_array( $fields['order']['condition']['post_type!'] ) ) {
                $fields['order']['condition']['post_type!'] = [ $fields['order']['condition']['post_type!'] ];
            }
            $fields['order']['condition']['post_type!'] = \array_merge( $fields['order']['condition']['post_type!'], $exclude_from_acf );
        }

        private function add_acf_to_dropdown( &$fields ) {
            if ( isset( $fields['post_type']['options'] ) ) {
                $fields['post_type']['options']['acf_repeater'] = \__( 'ACF Repeater', 'repeaters-relationships-connector-acf-elementor' );
                $fields['post_type']['options']['acf_relation'] = \__( 'ACF Relationship', 'repeaters-relationships-connector-acf-elementor' );
            }
        }

        private function get_acf_repeater_field_names() {
            $repeater_fields = [];
            if ( \function_exists( 'acf_get_field_groups' ) ) {
                $field_groups = \acf_get_field_groups();
                if ( ! empty( $field_groups ) ) {
                    foreach ( $field_groups as $field_group ) {
                        $acf_fields = \acf_get_fields( $field_group['key'] );
                        if ( ! empty( $acf_fields ) ) {
                            foreach ( $acf_fields as $field ) {
                                if ( $field['type'] === 'repeater' ) {
                                    $repeater_fields[ $field['name'] ] = $field['label'];
                                }
                            }
                        }
                    }
                }
            }

            return $repeater_fields;
        }

        private function get_acf_relation_field_names() {
            $relation_fields = [];
            if ( \function_exists( 'acf_get_field_groups' ) ) {
                $field_groups = \acf_get_field_groups();
                if ( ! empty( $field_groups ) ) {
                    foreach ( $field_groups as $field_group ) {
                        $acf_fields = \acf_get_fields( $field_group['key'] );
                        if ( ! empty( $acf_fields ) ) {
                            foreach ( $acf_fields as $field ) {
                                if ( $field['type'] === 'relationship' ) {
                                    $relation_fields[ $field['name'] ] = $field['label'];
                                }
                            }
                        }
                    }
                }
            }

            return $relation_fields;
        }

        private function get_acf_data_source_options() {
            $options = [
                'current_post' => \__( 'Current Post/Page', 'repeaters-relationships-connector-acf-elementor' ),
            ];
            if ( \function_exists( 'acf_get_options_pages' ) ) {
                $options_pages = \acf_get_options_pages();
                if ( ! empty( $options_pages ) && \is_array( $options_pages ) ) {
                    foreach ( $options_pages as $page ) {
                        $post_id = isset( $page['post_id'] ) ? $page['post_id'] : 'options';
                        $options[ $post_id ] = $page['page_title'];
                    }
                }
            }
            return $options;
        }
    }

    $controls_manager->add_group_control( \ElementorPro\Modules\QueryControl\Controls\Group_Control_Related::get_type(), new Acf_Query_Control_Wrapper() );

}, 999 );
