<?php defined('ABSPATH') or die('The silence is god!');

/*
Plugin Name: Modified Mask Form Elementor - Braine
Description: Plugin para incluir máscaras nos formulários do Elementor Pro.
Author: Saulo Braine
Version: 1.0
Text Domain: braine-mask-form-elementor
*/

function mmfe_load_scripts() {
    wp_enqueue_script('jquery.mask.min.js', plugin_dir_url(__FILE__) . 'js/jquery.mask.min.js', ['jquery'], '1.0', true);
    wp_enqueue_script('maskformelementor.js', plugin_dir_url(__FILE__) . 'js/maskformelementor.js', ['jquery'], '1.0', true);
}
add_action('wp_enqueue_scripts', 'mmfe_load_scripts');

function mmfe_load_admin_scripts() {
    wp_enqueue_script('mmfe-admin', plugins_url('js/admin.js', __FILE__), ['jquery'], time());
    wp_localize_script(
        'mmfe-admin',
        'maskFields',
        [
            'fields' => array_keys(mmfe_get_field_types())
        ]
    );
}
add_action('elementor/editor/after_enqueue_scripts', 'mmfe_load_admin_scripts');

function mmfe_get_field_types() {
    $types = [];
    $types['maskdate'] = __('Máscara: Data', 'mask-form-elementor');
    $types['masktime'] = __('Máscara: Horário', 'mask-form-elementor');
    $types['maskdate_time'] = __('Máscara: Data e Horário', 'mask-form-elementor');
    $types['maskcep'] = __('Máscara: CEP', 'mask-form-elementor');
    $types['maskphone'] = __('Máscara: Telefone sem DDD', 'mask-form-elementor');
    $types['masktelephone_with_ddd'] = __('Máscara: Telefone', 'mask-form-elementor');
    $types['maskphone_with_ddd'] = __('Máscara: Telefone com nono digito', 'mask-form-elementor');
    $types['maskcpfcnpj'] = __('Máscara: Cpf ou Cnpj', 'mask-form-elementor');
    $types['maskcpf'] = __('Máscara: CPF', 'mask-form-elementor');
    $types['maskcnpj'] = __('Máscara: CNPJ', 'mask-form-elementor');
    $types['maskmoney'] = __('Máscara: Monetário', 'mask-form-elementor');
    $types['maskip_address'] = __('Máscara: Endereço de IP', 'mask-form-elementor');
    $types['maskpercent'] = __('Máscara: Porcentagem', 'mask-form-elementor');
    $types['maskcard_number'] = __('Máscara: Número Cartão de Crédito', 'mask-form-elementor');
    $types['maskcard_date'] = __('Máscara: Validade Cartão de Crédito', 'mask-form-elementor');
    return $types;
}

function mmfe_add_field_types($types) {
    return array_merge($types, mmfe_get_field_types());
}

add_filter('elementor_pro/forms/field_types', 'mmfe_add_field_types');

function mmfe_render_field_types($item, $item_index, $el) {
    $mask_class = substr($item['field_type'], 4, strlen($item['field_type']));

    $el->set_render_attribute('input' . $item_index, 'type', 'tel');
    $el->add_render_attribute('input' . $item_index, 'class', 'elementor-field-textual ' . $mask_class);

    echo '<input size="1" ' . $el->get_render_attribute_string('input' . $item_index) . '>';
}

foreach (array_keys(mmfe_get_field_types()) as $field_type) {
    add_action("elementor_pro/forms/render_field/{$field_type}", "mmfe_render_field_types", 10, 3);
}

add_action('elementor/element/form/section_form_fields/before_section_end', function ($widget, $args) {
    $control_data = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack($widget->get_unique_name(), "form_fields");

    if (is_wp_error($control_data)) {
        return;
    }

    $old_values = $control_data['fields']['placeholder']['conditions']['terms'][0]['value'];

    $control_data['fields']['placeholder']['conditions']['terms'][0]['value'] = array_merge($old_values, array_keys(mmfe_get_field_types()));

    $widget->update_control('form_fields', $control_data);
}, 10, 2);
