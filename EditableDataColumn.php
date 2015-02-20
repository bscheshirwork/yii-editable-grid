<?php

/**
 * EditableDataColumn class file.
 * Makes editable grid column.
 *
 * @author    BSCheshir <bscheshir.work@gmail.com>
 * @link      https://github.com/bscheshirwork/yii-editable-grid
 * @copyright 2015 BSCheshir
 */
class EditableDataColumn extends CDataColumn
{
    /**
     * @var int Grid row counter
     */
    private static $_prevRowNum;

    /**
     * mask replace
     * @param $row
     * @param $field
     * @return mixed
     */
    protected function maskedName($row, $field)
    {
        return str_replace(['{gridNum}', '{rowNum}'], [$this->grid->getGridCounter(), $row], str_replace('{name}', $field, $this->grid->fieldNameMask));
    }

    /**
     * Renders the id of model for !isNewRecord .
     * @param integer $row  the row number (zero-based)
     * @param mixed   $data the data associated with the row
     */
    protected function renderIdHiddenField($row, $data)
    {
        if (self::$_prevRowNum !== $row) {
            self::$_prevRowNum = $row;
            $name = $this->maskedName($row, $real = $this->grid->primaryKey);
            if (isset($data[$real])) {
                if ($data instanceof CModel)
                    echo CHtml::activeHiddenField($data, $name, $this->grid->primaryKeyHtmlOptions);
                else
                    echo CHtml::hiddenField($name, $data[$real], $this->grid->primaryKeyHtmlOptions);
            }
        }
    }

    /**
     * Renders the data cell content.
     * This method evaluates {@link value} or {@link name} and renders the result.
     * @param integer $row  the row number (zero-based)
     * @param mixed   $data the data associated with the row
     */
    protected function renderDataCellContent($row, $data)
    {
        $this->renderIdHiddenField($row, $data);
        $name = $this->maskedName($row, $real = $this->name);
        if ($this->value !== null)
            $value = $this->evaluateExpression($this->value, [
                'data' => $data,
                'row'  => $row,
                'grid' => $this->grid->getGridCounter(),
                'name' => $name,
                'real' => $real,
            ]);
        elseif ($this->name !== null)
            $value = CHtml::value($data, $this->name);
        echo $value === null ? $this->grid->nullDisplay : $this->grid->getFormatter()->format($value, $this->type);
    }

}
