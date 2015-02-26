<?php
Yii::import('zii.widgets.grid.CGridView');

/**
 * EditableGrid class file.
 *
 * @author    Dyomin Dmitry <sizemail@gmail.com>
 * @link      http://size.perm.ru/yii-editable-grid
 * @copyright 2014 SiZE
 */
class EditableGrid extends HypGridView
{

    /**
     * @var int Grids counter
     */
    private static $_grid_counter = 0;

    /**
     * @var string The label for the add row button. Defaults to "Add new row"
     */
    public $buttonCreateRowLabel;

    /**
     * @var string A javascript function that will be invoked after the creating row
     */
    public $afterCreateRow;

    /**
     * @var string A javascript function to be invoked when the button create row is clicked
     */
    public $buttonCreateRowClick;

    /**
     * @var array The HTML options for the create row button tag
     */
    public $buttonCreateRowOptions = [
        'class' => 'new-grid-row'
    ];

    /**
     * @var string The label for the remove new row button. Defaults to "Delete".
     */
    public $buttonRemoveRowLabel;

    /**
     * @var array The HTML options for the remove row button tag
     */
    public $buttonRemoveRowOptions = [
        'class' => 'removeRow'
    ];

    /**
     * @var string A javascript function to be invoked when the button remove row is clicked
     */
    public $buttonRemoveRowClick;

    /**
     * @var string A javascript function that will be invoked after the removing row
     */
    public $afterRemoveRow;

    /**
     * @var string The HTML template for the new row
     */
    public $rowTemplate;

    /**
     * @var string the HTML TextArea tag attribute ID, generated to get template
     */
    protected $rowTemplateId;

    /**
     * @var string Row primary key. Used to generate hidden field with primaryKey from db
     */
    public $primaryKey = 'id';

    /**
     * @var string mask to modify the name and identifier of field. Default [{gridNum}][{rowNum}]{name}
     */
    public $fieldNameMask = '[{gridNum}][{rowNum}][{name}]';

    /**
     * @var array the HTML options for the hidden field primaryKey
     */
    public $primaryKeyHtmlOptions = [];

    private $buttonRemoveInTemplate = false;

    private $buttonCreateInTemplate = false;

    public function init()
    {
        parent::init();

        self::$_grid_counter++;

        $this->rowTemplateId = 'row-template-' . $this->id;

        if (strpos($this->rowTemplate, '{buttonRemoveRow}') !== false) {
            $this->initButtonRemoveRow();
            if (!isset($this->buttonRemoveRowOptions['class'])) {
                $this->buttonRemoveRowOptions['class'] = 'removeRow';
            }
            if (!($this->buttonRemoveRowClick instanceof CJavaScriptExpression)) {
                $this->buttonRemoveRowClick = new CJavaScriptExpression($this->buttonRemoveRowClick);
            }
            ob_start();
            $this->renderButtonRemoveRow();
            $tr = ['{buttonRemoveRow}' => ob_get_contents()];
            ob_end_clean();
            $this->rowTemplate = strtr($this->rowTemplate, $tr);
            $this->buttonRemoveInTemplate = true;
        }
        if (strpos($this->template, '{buttonCreateRow}') !== false) {
            $this->initButtonCreateRow();
            if (!isset($this->buttonCreateRowOptions['class'])) {
                $this->buttonCreateRowOptions['class'] = 'buttonCreateRow';
            }
            if (!($this->buttonCreateRowClick instanceof CJavaScriptExpression)) {
                $this->buttonCreateRowClick = new CJavaScriptExpression($this->buttonCreateRowClick);
            }
            $this->buttonCreateInTemplate = true;
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
     * @return int
     */
    public function getGridCounter()
    {
        return self::$_grid_counter;
    }

    public function initButtonCreateRow()
    {
        if ($this->afterCreateRow === null) {
            $this->afterCreateRow = 'function(){}';
        }

        $this->buttonCreateRowClick = <<<EOD
function(){
    var th = this,
        afterCreateRow = {$this->afterCreateRow},
        rowTpl = $('#{$this->rowTemplateId}').val(),
        rowNum = $('#{$this->id} tbody tr').length,
        placeholders = [{p:'gridNum',v:'{$this->getGridCounter()}'},{p:'rowNum',v:rowNum}];

        for(var i=0; i<placeholders.length; i++){
            rowTpl = rowTpl.replace(new RegExp("\{"+placeholders[i].p+"\}", "g"), placeholders[i].v);
        }
        $('#{$this->id} tbody').append( rowTpl );

        afterCreateRow();
}
EOD;
    }

    public function initButtonRemoveRow()
    {
        if ($this->afterRemoveRow === null) {
            $this->afterRemoveRow = 'function(){}';
        }

        $this->buttonRemoveRowClick = <<<EOD
function(){
    var th = this,
        afterDelete = {$this->afterRemoveRow};
    $(this).parents("tr").remove();
    afterDelete(th);
    return false;
}
EOD;
    }

    public function registerClientScript()
    {
        parent::registerClientScript();
        $js = '';
        if ($this->buttonCreateInTemplate) {
            $function = CJavaScript::encode($this->buttonCreateRowClick);
            $class = preg_replace('/\s+/', '.', $this->buttonCreateRowOptions['class']);
            $js .= "jQuery(document).on('click','#{$this->id} .{$class}',$function);";
        }
        if ($this->buttonRemoveInTemplate) {
            $function = CJavaScript::encode($this->buttonRemoveRowClick);
            $class = preg_replace('/\s+/', '.', $this->buttonRemoveRowOptions['class']);
            $js .= "jQuery(document).on('click','#{$this->id} .{$class}',$function);";
        }
        if ($js)
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->id, $js);
    }

    public function renderButtonCreateRow()
    {
        if ($this->buttonCreateRowLabel === null) {
            $this->buttonCreateRowLabel = Yii::t('editablegrid.editablegrid', 'Add new row');
        }
        echo CHtml::button($this->buttonCreateRowLabel, $this->buttonCreateRowOptions);

        echo CHtml::textArea('', $this->rowTemplate, ['id' => $this->rowTemplateId, 'style' => 'display: none;']);
    }

    public function renderButtonRemoveRow()
    {
        if ($this->buttonRemoveRowLabel === null) {
            $this->buttonRemoveRowLabel = Yii::t('zii', 'Delete');
        }

        echo CHtml::button($this->buttonRemoveRowLabel, $this->buttonRemoveRowOptions);
    }

}
