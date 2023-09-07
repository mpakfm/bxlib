<?php

namespace Mpakfm\Bxlib\PhpCsFixer;

class DiffLine
{
    /** @var string */
    public $text;

    /** @var int|null Номер строки в старом файле. */
    public $oldLineNumber;

    /** @var int|null Номер строки в новом файле. */
    public $newLineNumber;

    public function __construct($text)
    {
        $this->text = $text;
    }

    /**
     * Возвращает true, если строка была удалена из нового файла.
     * @return bool
     */
    public function isDelete()
    {
        return (mb_strpos($this->text, '-') === 0);
    }

    /**
     * Возвращает true, если строка была добавлена в новом файле.
     * @return bool
     */
    public function isInsert()
    {
        return (strpos($this->text, '+') === 0);
    }

    /**
     * @return bool
     */
    public function isModified()
    {
        return ($this->isDelete() || $this->isInsert());
    }
}
