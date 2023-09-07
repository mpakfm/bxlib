<?php
/**
 * Этот файл необходимо скопировать в корень проекта с именем .php-cs-fixer.php
 * Указать необходимые значения в константах битрикса
 *
 * Настроить правила стилей
 * Включить в конфиг требуемые для проекта правила:
 * $myConf
 *    ->setConfRule('align_multiline_comment')
 *    ->setConfRule('array_indentation');
 */

use Mpakfm\Bxlib\PhpCsFixer\Config as PhpCsFixerConfig;
use PhpCsFixer\Config;

define('NOT_CHECK_PERMISSIONS', true);
define("NO_AGENT_CHECK", true);
define('SITE_ID', 'lb');
define('BX_UTF', true);
define('NO_KEEP_STATISTIC', true);
define('BX_BUFFER_USED', true);

if ($_SERVER['DOCUMENT_ROOT'] == '') {
    $_SERVER['DOCUMENT_ROOT'] = __DIR__;
    require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
}

$myConf = new PhpCsFixerConfig();
$myConf
    ->setConfRule('align_multiline_comment')
    ->setConfRule('array_indentation')
    ->setConfRule('array_syntax')
    ->setConfRule('blank_line_after_namespace')
    ->setConfRule('blank_line_after_opening_tag')
    ->setConfRule('binary_operator_spaces')
    ->setConfRule('cast_spaces')
    ->setConfRule('concat_space')
    ->setConfRule('constant_case')
    ->setConfRule('control_structure_braces')
    ->setConfRule('declare_parentheses')
    ->setConfRule('encoding')
    ->setConfRule('full_opening_tag')
    ->setConfRule('function_declaration')
    ->setConfRule('general_phpdoc_annotation_remove')
    ->setConfRule('indentation_type')
    ->setConfRule('list_syntax')
    ->setConfRule('lowercase_cast')
    ->setConfRule('lowercase_keywords')
    ->setConfRule('method_argument_space')
    ->setConfRule('method_chaining_indentation')
    ->setConfRule('multiline_whitespace_before_semicolons')
    ->setConfRule('new_with_braces')
    ->setConfRule('no_closing_tag')
    ->setConfRule('no_empty_statement')
    ->setConfRule('no_extra_blank_lines')
    ->setConfRule('no_homoglyph_names')
    ->setConfRule('no_leading_import_slash')
    ->setConfRule('no_multiple_statements_per_line')
    ->setConfRule('no_multiline_whitespace_around_double_arrow')
    ->setConfRule('no_null_property_initialization')
    ->setConfRule('no_spaces_after_function_name')
    ->setConfRule('no_superfluous_elseif')
    ->setConfRule('no_trailing_whitespace')
    ->setConfRule('no_unused_imports')
    ->setConfRule('no_useless_else')
    ->setConfRule('no_whitespace_in_blank_line')
    ->setConfRule('non_printable_character')
    ->setConfRule('ordered_imports')
    ->setConfRule('phpdoc_add_missing_param_annotation')
    ->setConfRule('phpdoc_align')
    ->setConfRule('phpdoc_single_line_var_spacing')
    ->setConfRule('phpdoc_trim')
    ->setConfRule('phpdoc_types_order')
    ->setConfRule('return_type_declaration')
    ->setConfRule('single_blank_line_at_eof')
    ->setConfRule('single_line_after_imports')
    ->setConfRule('single_line_comment_style')
    ->setConfRule('single_space_around_construct')
    ->setConfRule('spaces_inside_parentheses')
    ->setConfRule('switch_case_space')
    ->setConfRule('ternary_operator_spaces')
    ->setConfRule('ternary_to_null_coalescing')
    ->setConfRule('trailing_comma_in_multiline')
    ->setConfRule('trim_array_spaces')
    ->setConfRule('whitespace_after_comma_in_array');

$conf = new Config();
$conf->setUsingCache(true)
    ->setRiskyAllowed(true)
    ->setFinder(PhpCsFixerConfig::createPhpFilesFinder())
    ->setRules(PhpCsFixerConfig::getAppliedRulesConfigForPhpFiles());
return $conf;
