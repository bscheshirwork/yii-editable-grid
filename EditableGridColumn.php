<?php

/**
 * EditableGridColumn class file.
 * Makes editable grid column.
 *
 * @author    Dyomin Dmitry <sizemail@gmail.com>
 * @link      http://size.perm.ru/yii-editable-grid
 * @copyright 2014 SiZE
 */
class EditableGridColumn extends EditableDataColumn
{

    /**
     * @var string Cell tag. Supported: textField, dropDownList
     */
    public $tag;

    /**
     * @var array For generating the list options (value=>display)
     */
    public $tagData;

    /**
     * @var array The HTML options for the cell tag.
     */
    public $tagHtmlOptions = [];

    /**
     * Renders the data cell content.
     * This method evaluates {@link value} or {@link name} and renders the result.
     * @param integer $row  the row number (zero-based)
     * @param mixed   $data the data associated with the row
     */
    protected function renderDataCellContent($row, $data)
    {
        if ($this->tag === null) {
            parent::renderDataCellContent($row, $data);
        } else {
            $this->renderIdHiddenField($row, $data);
            $name = $this->maskedName($row, $real = $this->name);

            $is_model = $data instanceof CModel;
            switch ($this->tag) {
                case 'textField':
                    if ($is_model)
                        echo CHtml::activeTextField($data, $name, $this->tagHtmlOptions);
                    else
                        echo CHtml::textField(self::resolveName($name), $data[$real], $this->tagHtmlOptions);
                    break;
                case 'dropDownList':
                    if ($is_model)
                        echo CHtml::activeDropDownList($data, $name, $this->tagData, $this->tagHtmlOptions);
                    else
                        echo CHtml::dropDownList(self::resolveName($name), $data[$real], $this->tagData, $this->tagHtmlOptions);
                    break;
            }
        }
    }

}
