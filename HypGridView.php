<?php

Yii::import('zii.widgets.grid.CGridView');

class HypGridView extends CGridView
{
    /**
     * Номера колонок, по которым переносится строка
     */
    public $hyphenationColumns = [];
    /**
     * Переносить каждые n столбцов
     */
    public $hyphenationOnCount = null;

    /**
     * rowHtmlOptionsExpression для перенесённой строки
     */
    public $hyphenationRowHtmlOptionsExpression = null;

    /**
     * rowCssClassExpression для перенесённой строки
     */
    public $hyphenationRowCssClassExpression = null;

    /**
     * Отключить перечисление по списку стилей (стиль перенесённой = стилю начала строки)
     */
    public $hyphenationDisableRowCssClass = true;

    /**
     * Перезаписывать $htmlOptions['class'] или смешивать с вычесленным для начала строки
     */
    public $hyphenationRewriteClass = false;

    /**
     * применить опции к перенесённой строке
     */
    public function HyphenationHtmlOptions($row, $col, $htmlOptions)
    {

        if (!empty($this->hyphenationOnCount)) {
            $htmlOptionsExpression = $this->hyphenationRowHtmlOptionsExpression;
            $cssClassExpression = $this->hyphenationRowCssClassExpression;
        } else {
            $htmlOptionsExpression = isset($this->hyphenationRowHtmlOptionsExpression[$col]) ? $this->hyphenationRowHtmlOptionsExpression[$col] : null;
            $cssClassExpression = isset($this->hyphenationRowCssClassExpression[$col]) ? $this->hyphenationRowCssClassExpression[$col] : null;
        }
        if ($htmlOptionsExpression !== null) {
            $data = $this->dataProvider->data[$row];
            $options = $this->evaluateExpression($htmlOptionsExpression, ['row' => $row, 'data' => $data]);
            if (is_array($options)) {
                if (isset($htmlOptions['class']) && isset($options['class']) && !$this->hyphenationRewriteClass)
                    $options['class'] .= ' ' . $htmlOptions['class'];
                $htmlOptions = CMap::mergeArray($htmlOptions, $options);
            }
        }

        if ($cssClassExpression !== null) {
            $data = $this->dataProvider->data[$row];
            $class = $this->evaluateExpression($cssClassExpression, ['row' => $row, 'data' => $data]);
        } elseif (!$this->hyphenationDisableRowCssClass && is_array($this->rowCssClass) && ($n = count($this->rowCssClass)) > 0)
            $class = $this->rowCssClass[$row % $n];
        if (!empty($class)) {
            if (isset($htmlOptions['class']))
                $htmlOptions['class'] .= ' ' . $class;
            else
                $htmlOptions['class'] = $class;
        }
        return $htmlOptions;
    }

    /**
     * Проверка на переносить/нетЪ
     */
    public function isHyphenation($num)
    {
        if (!empty($this->hyphenationOnCount))
            return !($num % $this->hyphenationOnCount);
        else
            if (!empty($this->hyphenationColumns)) {
                if (!$this->hyphenationColumns instanceof Traversable && !is_array($this->hyphenationColumns))
                    $this->hyphenationColumns = (array)$this->hyphenationColumns;
                $hyp = false;
                foreach ($this->hyphenationColumns as $item)
                    if ($num == $item)
                        $hyp = true;
                return $hyp;
            } else
                return false;
    }

    /**
     * Renders the table header.
     */
    public function renderTableHeader()
    {
        if (!$this->hideHeader) {
            echo "<thead>\n";

            if ($this->filterPosition === self::FILTER_POS_HEADER)
                $this->renderFilter();

            echo "<tr>\n";
            $i = 0;
            foreach ($this->columns as $column) {
                if ($this->isHyphenation(++$i))
                    echo "</tr>\n<tr>\n";
                $column->renderHeaderCell();
            }
            echo "</tr>\n";

            if ($this->filterPosition === self::FILTER_POS_BODY)
                $this->renderFilter();

            echo "</thead>\n";
        } elseif ($this->filter !== null && ($this->filterPosition === self::FILTER_POS_HEADER || $this->filterPosition === self::FILTER_POS_BODY)) {
            echo "<thead>\n";
            $this->renderFilter();
            echo "</thead>\n";
        }
    }

    /**
     * Renders the filter.
     * @since 1.1.1
     */
    public function renderFilter()
    {
        if ($this->filter !== null) {
            echo "<tr class=\"{$this->filterCssClass}\">\n";
            $i = 0;
            foreach ($this->columns as $column) {
                if ($this->isHyphenation(++$i))
                    echo "</tr>\n<tr class=\"{$this->filterCssClass}\">\n";
                $column->renderFilterCell();
            }
            echo "</tr>\n";
        }
    }

    /**
     * Renders the table footer.
     */
    public function renderTableFooter()
    {
        $hasFilter = $this->filter !== null && $this->filterPosition === self::FILTER_POS_FOOTER;
        $hasFooter = $this->getHasFooter();
        if ($hasFilter || $hasFooter) {
            echo "<tfoot>\n";
            if ($hasFooter) {
                echo "<tr>\n";
                $i = 0;
                foreach ($this->columns as $column) {
                    if ($this->isHyphenation(++$i))
                        echo "</tr>\n<tr>\n";
                    $column->renderFooterCell();
                }
                echo "</tr>\n";
            }
            if ($hasFilter)
                $this->renderFilter();
            echo "</tfoot>\n";
        }
    }

    /**
     * Renders the table body.
     */
    public function renderTableBody()
    {
        $data = $this->dataProvider->getData();
        $n = count($data);
        echo "<tbody>\n";
        if ($n > 0) {
            foreach (array_keys($data) as $row)
                $this->renderTableRow($row);
        } else {
            echo '<tr><td colspan="' . count($this->columns) . '" class="empty">';
            $this->renderEmptyText();
            echo "</td></tr>\n";
        }
        echo "</tbody>\n";
    }

    /**
     * Renders a table body row.
     * @param integer $row the row number (zero-based).
     */
    public function renderTableRow($row)
    {
        $htmlOptions = [];
        if ($this->rowHtmlOptionsExpression !== null) {
            $data = $this->dataProvider->data[$row];
            $options = $this->evaluateExpression($this->rowHtmlOptionsExpression, ['row' => $row, 'data' => $data]);
            if (is_array($options))
                $htmlOptions = $options;
        }

        if ($this->rowCssClassExpression !== null) {
            $data = $this->dataProvider->data[$row];
            $class = $this->evaluateExpression($this->rowCssClassExpression, ['row' => $row, 'data' => $data]);
        } elseif (is_array($this->rowCssClass) && ($n = count($this->rowCssClass)) > 0)
            $class = $this->rowCssClass[$row % $n];

        if (!empty($class)) {
            if (isset($htmlOptions['class']))
                $htmlOptions['class'] .= ' ' . $class;
            else
                $htmlOptions['class'] = $class;
        }

        echo CHtml::openTag('tr', $htmlOptions) . "\n";
        $i = 0;
        foreach ($this->columns as $column) {
            if ($this->isHyphenation(++$i))
                echo "</tr>\n" . CHtml::openTag('tr', $this->HyphenationHtmlOptions($row, $i, $htmlOptions)) . "\n";
            $column->renderDataCell($row);
        }
        echo "</tr>\n";
    }
}
