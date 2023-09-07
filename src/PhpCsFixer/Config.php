<?php

namespace Mpakfm\Bxlib\PhpCsFixer;

use LogicException;
use Symfony\Component\Finder\Finder;

/**
 * Настройки и описания правил для php-cs-fixer.
 * @see https://github.com/FriendsOfPHP/PHP-CS-Fixer
 * @see https://mlocati.github.io/php-cs-fixer-configurator/
 */

class Config
{

    public static $confRules = [];
    /**
     * Возвращает класс, определяющий, какие php файлы проверять.
     * example:
     *      return Finder::create()
     *          ->files()
     *          ->in($_SERVER['DOCUMENT_ROOT'])
     *          ->name('*.php')
     *          ->ignoreDotFiles(true)
     *          ->ignoreVCS(true)
     *          ->exclude('local/modules/sprint.migration')
     *          ->exclude('vendor')
     *          ->exclude('bitrix')
     *          ->exclude('upload');
     */
    public static function createPhpFilesFinder(): Finder
    {
        return Finder::create()
            ->files()
            ->in($_SERVER['DOCUMENT_ROOT'])
            ->name('*.php')
            ->ignoreDotFiles(true)
            ->ignoreVCS(true)
            ->exclude('vendor')
            ->exclude('bitrix');
    }

    /**
     * Настройки для проверки code style в php файлах.
     * @return array
     */
    public static function getAppliedRulesConfigForPhpFiles()
    {
        return array_map(
            function ($element) {
                return $element['config'];
            },
            self::getAppliedRulesForPhpFiles()
        );
    }

    /**
     * @param string $ruleName
     * @return string
     */
    public static function getRuleDescription($ruleName)
    {
        $rulesForPhpFiles = self::getAppliedRulesForPhpFiles();

        $description = $rulesForPhpFiles[$ruleName]['description'] ?? null;
        if ($description) {
            return $description;
        }

        throw new LogicException("Description for php-cs-fixer rule '{$ruleName}' not found");
    }

    /**
     * Настройки для проверки code style в php файлах.
     * @return array
     */
    private static function getAppliedRulesForPhpFiles()
    {
        return self::$confRules;
    }

    public function setConfRule(string $key)
    {
        if (!array_key_exists($key, self::$allRules)) {
            return $this;
        }
        self::$confRules[$key] = self::$allRules[$key];
        return $this;
    }

    public static $allRules = [
        // Проверяет, что не съехали звездочки в многосточных комментариях
        'align_multiline_comment' => [
            'description' => 'Multi-line comments must have an asterisk and must be aligned with the first one',
            'config'      => [
                'comment_type' => 'all_multiline', // Проверяет как обычные комментарии, так и phpdoc
            ],
        ],
        // Выравниваем отступы в массивах
        'array_indentation' => [
            'description' => 'Each element of an array must be indented exactly once',
            'config'      => true,
        ],
        // Используем только короткий синтаксис объявления массивов `[]`
        'array_syntax' => [
            'description' => 'PHP arrays should be declared using short syntax',
            'config'      => [
                'syntax' => 'short',
            ],
        ],
        // Проверяет, что после объявления неймспейса ровно одна пустая строка
        'blank_line_after_namespace' => [
            'description' => 'There MUST be one blank line after the namespace declaration',
            'config'      => true,
        ],
        // Проверяет наличие пустой строки после <?php
        'blank_line_after_opening_tag' => [
            'description' => 'Ensure there is no code on the same line as the PHP open tag and it is followed by a blank line',
            'config'      => true,
        ],
        // Выравнивание операторов
        'binary_operator_spaces' => [
            'description' => 'Binary operators should be surrounded by space as configured',
            'config'      => [
                'default'   => null,
                'operators' => [
                    // Объявление массивов
                    '=>'  => 'align_single_space',
                    // Операторы присваивания
                    '='   => 'align_single_space',
                    '&='  => 'align_single_space',
                    '.='  => 'align_single_space',
                    '/='  => 'align_single_space',
                    '-='  => 'align_single_space',
                    '%='  => 'align_single_space',
                    '*='  => 'align_single_space',
                    '|='  => 'align_single_space',
                    '+='  => 'align_single_space',
                    '<<=' => 'align_single_space',
                    '>>=' => 'align_single_space',
                    '^='  => 'align_single_space',
                    '**=' => 'align_single_space',
                    // Операторы вычислений
                    '|'   => 'single_space',
                    '^'   => 'single_space',
                    '+'   => 'single_space',
                    '-'   => 'single_space',
                    '&'   => 'single_space',
                    '*'   => 'single_space',
                    '/'   => 'single_space',
                    '%'   => 'single_space',
                    '**'  => 'single_space',
                    '>>'  => 'single_space',
                    '<<'  => 'single_space',
                    // Тернарный оператор
                    '??'  => 'single_space',
                    // Логические операторы
                    '<'   => 'single_space',
                    '>'   => 'single_space',
                    '&&'  => 'single_space',
                    '||'  => 'single_space',
                    '=='  => 'single_space',
                    '>='  => 'single_space',
                    '===' => 'single_space',
                    '!='  => 'single_space',
                    '<>'  => 'single_space',
                    '!==' => 'single_space',
                    '<='  => 'single_space',
                    '<=>' => 'single_space',
                    'and' => 'single_space',
                    'or'  => 'single_space',
                    'xor' => 'single_space',
                ],
            ],
        ],
        // Открывающая фигурная скобка быть помещена в «следующую» или «ту же» строку после классных конструкций (неанонимные классы, интерфейсы, признаки, методы и не-лямбда-функции)
        'braces' => [
            'description' => 'Braces: allow single line closure, don`t next position after functions and oop constructs',
            'config'      => [
                'allow_single_line_closure'                   => true,
                'position_after_functions_and_oop_constructs' => 'next',
            ],
        ],
        // Правило проверяет, что между оператором приведения типа и переменной, ровно 1 пробел
        'cast_spaces' => [
            'description' => 'A single space should be between cast and variable',
            'config'      => [
                'space' => 'single',
            ],
        ],
        // Правило проверяет, что оператор конкатенации обрамлен в двух сторон по одному пробелу
        'concat_space' => [
            'description' => 'Concatenation should be surrounded by single spaces',
            'config'      => [
                'spacing' => 'one',
            ],
        ],
        //
        'control_structure_continuation_position' => [
            'description' => 'Control structure continuation keyword must be on the configured line',
            'config'      => ['position' => ['same_line']],
        ],
        //
        'control_structure_braces' => [
            'description' => 'The body of each control structure MUST be enclosed within braces',
            'config'      => true,
        ],
        // Фигурные скобки должны быть расставлены так, как настроено
        'curly_braces_position' => [
            'description' => 'Curly braces must be placed as configured',
            'config'      => [
                'allow_single_line_anonymous_functions'     => true, // Разрешить анонимным функциям иметь открывающие и закрывающие скобки в одной строке
                'allow_single_line_empty_anonymous_classes' => true, // Разрешить анонимным классам иметь открывающие и закрывающие скобки в одной строке
                'anonymous_classes_opening_brace'           => ['same_line'], // Положение открывающей скобки тела анонимного класса
                'anonymous_functions_opening_brace'         => ['same_line'], // Положение открывающей скобки тела анонимной функции
                'classes_opening_brace'                     => ['next_line_unless_newline_at_signature_end'], // Положение открывающей скобки тела класса
                'control_structures_opening_brace'          => ['same_line'], // Положение раскрывающейся скобки управляющих конструкций
                'functions_opening_brace'                   => ['next_line_unless_newline_at_signature_end'], // Положение открывающей скобки тела функции
            ],
        ],
        // Вокруг круглых скобок объявления не должно быть пробелов.
        'declare_parentheses' => [
            'description' => 'There must not be spaces around declare statement parentheses',
            'config'      => true,
        ],
        // Удаляет (BOM) из файлов
        'encoding' => [
            'description' => 'PHP code MUST use only UTF-8 without BOM (remove BOM)',
            'config'      => true,
        ],
        // Заменяет <? на <?php
        'full_opening_tag' => [
            'description' => 'PHP code must use the long <?php tags or short-echo <?= tags and not other tag variations',
            'config'      => true,
        ],
        // Проверяет корректноть расстановки пробелов при объявлении функций
        'function_declaration' => [
            'description' => 'Spaces should be properly placed in a function declaration',
            'config'      => [
                'closure_function_spacing' => 'one',
            ],
        ],
        // Добавляет пропущенный пробел между аргументом функции и его typehint
        'function_typehint_space' => [
            'description' => 'Add missing space between function\'s argument and its typehint',
            'config'      => true,
        ],
        // Удаляет перечисленные ниже тэги из аннотаций
        'general_phpdoc_annotation_remove' => [
            'description' => 'Configured annotations should be omitted from PHPDoc',
            'config'      => [
                'annotations' => [
                    'inheritdoc',
                    'throws',
                ],
            ],
        ],
        // В качестве отступов используем только 4 пробела
        'indentation_type' => [
            'description' => 'Code must use configured indentation type (4 spaces)',
            'config'      => true,
        ],
        // Проверяет, что оператор list Использует короткий синтаксис
        'list_syntax' => [
            'description' => 'List (array destructuring) assignment should be declared using short syntax',
            'config'      => [
                'syntax' => 'short',
            ],
        ],
        // Проверяет, что приведение типов написано в нижнем регистре
        'lowercase_cast' => [
            'description' => 'Cast should be written in lower case',
            'config'      => true,
        ],
        // Проверяет, что true, false, and null написаны в нижнем регистре
        // The rules contain unknown fixers: "lowercase_constants" is renamed (did you mean "constant_case"? (note: use configuration "['case' => 'lower']")), "trailing_comma_in_multiline_array" is renamed (did you mean "trailing_comma_in_multiline"? (note: u
        //  se configuration "['elements' => ['arrays']]")).
        //  For more info about updating see: https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/v3.0.0/UPGRADE-v3.md#renamed-ruless.
        //            'lowercase_constants' => [
        //                'description' => 'The PHP constants true, false, and null MUST be in lower case',
        //                'config'      => true,
        //            ],
        'constant_case' => [
            'description' => 'The PHP constants true, false, and null MUST be in lower case',
            'config'      => ['case' => 'lower'],
        ],
        // Зарезервированные слова должны быть написаны в нижнем регистре
        'lowercase_keywords' => [
            'description' => 'PHP keywords MUST be in lower case',
            'config'      => true,
        ],
        // Проверяет корректность написания аргументов функции, при объявлении и вызове
        'method_argument_space' => [
            'description' => 'Ensure function arguments are placed correctly',
            'config'      => [
                // Если аргумены перечислены более чем в одну строку, то проверяет, что каждый аргумент находится на своей строке
                //'ensure_fully_multiline' => true,
                'on_multiline' => 'ensure_fully_multiline',
                // После запятой, будет ровно 1 пробел
                'keep_multiple_spaces_after_comma' => false,
            ],
        ],
        // Выравнимаем отступы, при использовании цепочек вызовов
        'method_chaining_indentation' => [
            'description' => 'Method chaining MUST be properly indented',
            'config'      => true,
        ],
        // Проверяет, что `;` находится на той же строке, что и оператор
        'multiline_whitespace_before_semicolons' => [
            'description' => 'Forbid multi-line whitespace before the closing semicolon',
            'config'      => [
                'strategy' => 'no_multi_line',
            ],
        ],
        // Приверяет, что при создании объекта с использованием оператора `new` используются скобочки `()`
        'new_with_braces' => [
            'description' => 'All instances created with new keyword must be followed by braces',
            'config'      => true,
        ],
        // Php файлы которые содержат только код, не должны использовать закрывающий php тэг
        'no_closing_tag' => [
            'description' => 'The closing `?>` tag MUST be omitted from files containing only PHP',
            'config'      => true,
        ],
        // Избавляемся от ненужных `;`
        'no_empty_statement' => [
            'description' => 'Remove useless semicolon statements',
            'config'      => true,
        ],
        // Избавляемся от ненужных пустых линий
        'no_extra_blank_lines' => [
            'description' => 'Remove useless empty lines',
            'config'      => [
                'tokens' => [
                    //'curly_brace_block',
                    'extra',
                    //'parenthesis_brace_block',
                    'return',
                    //'square_brace_block',
                    //'throw',
                    //'use',
                ],
            ],
        ],
        // Убирает случайное использование русских букв в именах
        'no_homoglyph_names' => [
            'description' => 'Replace accidental usage of homoglyphs (russian or other non ascii characters) in names',
            'config'      => true,
        ],
        // Удаляем лидируюший слеш при импорте
        'no_leading_import_slash' => [
            'description' => 'Remove leading slashes in use clauses',
            'config'      => true,
        ],
        // В каждой строке не должно быть более одного утверждения
        'no_multiple_statements_per_line' => [
            'description' => 'There must not be more than one statement per line',
            'config'      => true,
        ],
        // Проверяет, что нет переносов строк вокруг оператора `=>`
        'no_multiline_whitespace_around_double_arrow' => [
            'description' => 'Operator => should not be surrounded by multi-line whitespaces',
            'config'      => true,
        ],
        // Проверяет, что свойства классов и объектов не инициализируются значением `null`
        'no_null_property_initialization' => [
            'description' => 'Properties MUST not be explicitly initialized with null',
            'config'      => true,
        ],
        // Проверяет, что между названием метода и открывающейся скобкой нет пробелов
        'no_spaces_after_function_name' => [
            'description' => 'There MUST NOT be a space between the method or function name and the opening parenthesis',
            'config'      => true,
        ],
        // Проверяет, что при вызове функций нет пробела после открывающейся скобки, и перед закрывающейся
        'no_spaces_inside_parenthesis' => [
            'description' => 'There MUST NOT be a space after the opening parenthesis. There MUST NOT be a space before the closing parenthesis',
            'config'      => true,
        ],
        // Заменяет elseif на if, если это не меняет поведение кода
        'no_superfluous_elseif' => [
            'description' => 'Replaces superfluous elseif with if.',
            'config'      => true,
        ],
        // Удаляем лишние пробелы в конце не пустых строк
        'no_trailing_whitespace' => [
            'description' => 'Remove trailing whitespace at the end of non-blank lines',
            'config'      => true,
        ],
        // Удаляем неиспользуемые импорты
        'no_unused_imports' => [
            'description' => 'Unused use statements must be removed',
            'config'      => true,
        ],
        // Удаляем else, если это не меняет поведение кода
        'no_useless_else' => [
            'description' => 'There should not be useless else cases',
            'config'      => true,
        ],
        // Проверяет, что на пустых строках нет пробелов
        'no_whitespace_in_blank_line' => [
            'description' => 'Remove trailing whitespace at the end of blank lines',
            'config'      => true,
        ],
        // Удаляет невидимые символы
        'non_printable_character' => [
            'description' => 'Remove Zero-width space (ZWSP), Non-breaking space (NBSP) and other invisible unicode symbols',
            'config'      => true,
        ],
        // Выравниваем импорты по алфавиту
        'ordered_imports' => [
            'description' => 'Ordering use statements',
            'config'      => true,
        ],
        // Добавляем тэг @param для параметров, если его нет
        'phpdoc_add_missing_param_annotation' => [
            'description' => 'PHPDoc should contain @param for all params',
            'config'      => true,
        ],
        // Выравниваем тэги и их значения в doc блоках
        'phpdoc_align' => [
            'description' => 'All items of the given PHPDoc tags must be aligned vertically',
            'config'      => [
                'tags' => ['param'],
            ],
        ],
        // Проверяет корректность расстановки пробелов в односточных doc блоках для переменных (@var)
        'phpdoc_single_line_var_spacing' => [
            'description' => 'Single line @var PHPDoc should have proper spacing',
            'config'      => true,
        ],
        // Удаляет пустые строки в начале и конце php-doc блока
        'phpdoc_trim' => [
            'description' => 'PHPDoc should start and end with content, excluding the very first and last line of the docblocks',
            'config'      => true,
        ],
        // Проверяет, что есть в аннотации @param есть null, то он будет последним
        'phpdoc_types_order' => [
            'description' => 'null in @param PHPDoc must be last',
            'config'      => [
                'null_adjustment' => 'always_last',
                'sort_algorithm'  => 'none',
            ],
        ],
        // Проверяет расстановку пробелов, при использовании return type declaration
        'return_type_declaration' => [
            'description' => 'There should be no space before colon, and one space after it in return type declarations',
            'config'      => [
                'space_before' => 'none',
            ],
        ],
        // Проверяет, что в конце файла ровно одна пустая строка
        'single_blank_line_at_eof' => [
            'description' => 'A PHP file without end tag must always end with a single empty line feed',
            'config'      => true,
        ],
        // Проверяет, что после импортов ровно одна пустая строка
        'single_line_after_imports' => [
            'description' => 'Each namespace use MUST go on its own line and there MUST be one blank line after the use statements block',
            'config'      => true,
        ],
        // Проверяет, что однострочныее комментарии используют синтаксис комментирования `//`
        'single_line_comment_style' => [
            'description' => 'Single-line comments and multi-line comments with only one line of actual content should use the // syntax',
            'config'      => true,
        ],
        // Проверяет расстановку пробелов, при использовании операторов case и default
        'switch_case_space' => [
            'description' => 'Removes extra spaces between colon and case value',
            'config'      => true,
        ],
        // Проверяет расстановку пробелов, при использовании тернарного оператора
        'ternary_operator_spaces' => [
            'description' => 'Spaces around ternary operator should be placed correctly',
            'config'      => true,
        ],
        // Меняет тернарный оператор на Null Coalesce Operator, где это возможно
        'ternary_to_null_coalescing' => [
            'description' => 'Use null coalescing operator ?? where possible',
            'config'      => true,
        ],
        // Убедимся, что последний элемент многострочеого массива имеет запятую в конце
        // The rules contain unknown fixers: "trailing_comma_in_multiline_array" is renamed (did you mean "trailing_comma_in_multiline"? (note: use configuration "['elements' => ['arrays']]")).
        //  For more info about updating see: https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/v3.0.0/UPGRADE-v3.md#renamed-ruless.
        //            'trailing_comma_in_multiline_array' => [
        //                'description' => 'PHP multi-line arrays should have a trailing comma',
        //                'config'      => true,
        //            ],
        'trailing_comma_in_multiline' => [
            'description' => 'PHP multi-line arrays should have a trailing comma',
            'config'      => ['elements' => ['arrays']],
        ],
        // Проверяет, что при объявлении массива нет пробела после открывающейся скобки, и перед закрывающейся
        'trim_array_spaces' => [
            'description' => 'Arrays should be formatted like function/method arguments, without leading or trailing single line space',
            'config'      => true,
        ],
        //
        'type_declaration_spaces' => [
            'description' => 'Ensure single space between a variable and its type declaration in function arguments and properties',
            'config'      => ['elements' => ['function', 'property']],
        ],
        // Проверяет, что при объявлении массива есть пробел после каждой запятой
        'whitespace_after_comma_in_array' => [
            'description' => 'In array declaration, there MUST be a whitespace after each comma',
            'config'      => true,
        ],
    ];
}
