yii-editable-grid
=================

Таблица полей ввода. 
# Установка

Добавьте в Ваш composer.json в соответствующие секции
```
"repositories": [
	...
	{
		"type": "git",
		"url": "http://github.com/bscheshirwork/yii-editable-grid"
	}
	...
],
"require": {
	...
	"bscheshir/yii-editable-grid": "dev-master"
	...
}
```
И запустите composer update

#Использование в отображении (view):

```php
// Импортируем виджет
Yii::import('vendor.bscheshir.yii-editable-grid.*');

// Получаем поставщик данных
$dataProvider = new CActiveDataProvider( TestModel );

// Для удобства повторяющуюся часть маски запишем
$fieldNameMaskPrefix = '[{gridNum}][{rowNum}]',

// Шаблон для новой строчки, которая добавляется в таблицу (обычно примерно соответствует основному)
$rowTemplate = '
	<tr>
		<td>'.CHtml::textField($dataProvider->modelClass . $fieldNameMaskPrefix . '[title]', '', array('size'=>40,'maxlength'=>255)).'</td>
		<td>'.CHtml::textField($dataProvider->modelClass . $fieldNameMaskPrefix . '[price]', '', array('size'=>5,'maxlength'=>15)).'</td>
		<td>'.CHtml::textField($dataProvider->modelClass . $fieldNameMaskPrefix . '[quantity]', '', array('size'=>5,'maxlength'=>8)).'</td>
		<td>'.CHtml::dropDownList($dataProvider->modelClass . $fieldNameMaskPrefix . '[color]', '', $colors_list, array('empty'=>'')).'</td>
		<td style="text-align: right;">0</td>
		<td class="button-column">{buttonRemoveRow}</td>
	</tr>
';

// Непосредственный вызов виджета
$this->widget('EditableGrid', array(
	'dataProvider' => $dataProvider,
	'template' => '{items} {buttonCreateRow}',
	'rowTemplate' => $rowTemplate,
	'fieldNameMask' => $fieldNameMaskPrefix . '{name}',
	'columns' => array(
		array(
			'class' => 'EditableGridColumn',
			'header' => 'Title',
			'name' => 'title',
			'tag' => 'textField',
			'tagHtmlOptions' => array(
				'size' => '40'
			)
		),
		array(
			'class' => 'EditableGridColumn',
			'header' => 'Price',
			'name' => 'price',
			'tag' => 'textField',
			'tagHtmlOptions' => array(
				'size' => '5'
			)
		),
		array(
			'class' => 'EditableGridColumn',
			'header' => 'Quantity',
			'name' => 'quantity',
			'tag' => 'textField',
			'tagHtmlOptions' => array(
				'size' => '5'
			)
		),
		array(
			'class' => 'EditableGridColumn',
			'header' => 'Color',
			'name' => 'color',
			'tag' => 'dropDownList',
			'tagData' => $colors_list,
			'tagHtmlOptions' => array(
				'empty' => ''
			)
		),
		array(
			'class' => 'EditableGridColumn',
			'header' => 'Total',
			'value' => '($data["price"] * $data["quantity"])',
			'htmlOptions' => array(
				'style' => 'text-align: right;'
			)
		),
		array(
			'class' => 'EditableButtonColumn',
		),
	),
));
```

Поддержка raw data и использование анонимных функций для отрисовки внутри ячейки другого шаблона 
```php
...
$model = $dataProvider->model;

$controller = $this;

$this->widget('EditableGrid', [
...
		[
			'class'             => 'EditableDataColumn',
			'header'            => 'Title',
			'name'              => 'title',
			'type'              => 'raw',
			'headerHtmlOptions' => [
				'style' => 'width:150px; text-align:left;',
			],
			'htmlOptions'       => [
				'style' => 'width:150px; text-align:left;',
			],
			'value'             => function ($data, $row, $grid, $name, $real) {
			/** 
			* @var CModel|array $data model or array from DataProvider
			* @var int $row number of row
			* @var int $grid number of grid
			* @var string $name masked  name i.e. [1][1]title (default mask [{gridNum}][{rowNum}]{name} 
			*      resolved to Model[gridNum][rowNum][name] in CHtml: echo CHtml::activeName($model, $name);
			* @var string $real real name in model|key of array
			*/
				return $data->title . ' ' .
				$row . ' ' .
				$grid . ' ' .
				$name . ' ' .
				$real;
			},
		],
		[
			'class'             => 'EditableDataColumn',
			'name'              => 'idSort',
			'header'            => $model->getAttributeLabel('id'),
			'type'              => 'raw',
			'value'             => function($data, $row, $grid, $name, $real) use ($controller) {
				return $controller->renderPartial('view', ['data' => $data], true);
			},
		],
		//Простое использование CHtml::active..Field
        'someone'        => [
			'class'             => 'EditableDataColumn',
			'name'              => 'someone',
			'header'            => $model->getAttributeLabel('someone'),
			'type'              => 'raw',
			'headerHtmlOptions' => [
				'style' => 'width:150px; text-align:right;',
			],
			'htmlOptions'       => [
				'style' => 'width:150px; text-align:right;',
			],
			'value'             => function ($model, $row, $grid, $name, $real) {
				echo CHtml::activeTextField($model, $name, [
					'placeholder' => $model->getAttributeLabel($real),
					'style'       => 'width:100px',
				]);
			},
		],
		'markToDelete'      => [
			'class'             => 'EditableDataColumn',
			'name'              => 'markToDelete',
			'header'            => 'Удалить',
			'type'              => 'raw',
			'headerHtmlOptions' => [
				'style' => 'width:20px; text-align:center;',
			],
			'htmlOptions'       => [
				'style' => 'width:20px; text-align:center;',
			],
			'value'             => function ($model, $row, $grid, $name, $real) {
				echo CHtml::activeCheckBox($model, $name);
			},
		],
...
]);
```
##Демо
Для получения представления о том, как это выглядит, смотрите пример исходного варианта **[size.perm.ru/yii-editable-grid/](http://size.perm.ru/yii-editable-grid/)**

Теперь включает в себя HypGridView - **[http://github.com/bscheshirwork/hyphenation-grid])**
данные одной сущности размещаем в несколько строк (для тех же частичных отрисовок)


В свойствах добавлены следующие настройки: 



'hyphenationColumns' = array();
массив номеров колонок, по которым осуществлять перенос на новую строку
или 



'hyphenationOnCount' = null;
через каждые N колонок делать перенос



'hyphenationRowHtmlOptionsExpression' 
аналог rowHtmlOptionsExpression, применяется к tr перенесённой строки. Если передан hyphenationOnCount - берётся для каждого переноса. Иначе требуется передать массив, где ключами будут номера колонок, по которым идёт перенос,
а значениями - применяемые для этого переноса опции.
```php
'hyphenationRowHtmlOptionsExpression'=>[
	2=>'["id"=>"second_{$row}"]', //обратите внимание на вид кавычек - строка будет передана в evaluateExpression
],
```



'hyphenationRowCssClassExpression'
аналог rowCssClassExpression, применяется к tr перенесённой строки. Если передан hyphenationOnCount - берётся для каждого переноса. Иначе требуется передать массив, где ключами будут номера колонок, по которым идёт перенос,
а значениями - применяемые для этого переноса опции.
```php
    'hyphenationRowCssClassExpression'=>[
        2=>'($row % 2)?"hidden":""', //обратите внимание на вид кавычек - строка будет передана в evaluateExpression
    ],
```



'hyphenationDisableRowCssClass'
Если данный флаг передан - для новой строки не будет применятся стиль из перечесления rowCssClass.
Работает в паре с 'hyphenationRewriteClass'
По умолчанию true



'hyphenationRewriteClass'
При переносе строки перезаписывать класс, сформированный для начала строки. Новые значения, вычисленные в
hyphenationRowHtmlOptionsExpression и hyphenationRowCssClassExpression заменят $htmlOptions['class'] начала строки.
Используйте с 'hyphenationDisableRowCssClass'=>false, если всё ещё хотите применить тот же стиль из перечисления rowCssClass, что и в начале строки.
По умолчанию false



Используем colspan для объеденения колонок.


В остальном - используем как обычно. 



Пример
```php
...
$controller = $this;
...
<?php $this->widget('HypGridView', [
	'id'=>'currencyrate-grid-1',
	'dataProvider'=>$dataProvider,
	'hyphenationColumns'=>[2,3],
	//'hyphenationOnCount'=>2,
	'hyphenationRowHtmlOptionsExpression'=>[
		2=>'["id"=>"second_{$row}"]'
	],
	'hyphenationRowCssClassExpression'=>[
		2=>['display'=>'none']
	],
	'hyphenationDisableRowCssClass'=>true,
	'hyphenationRewriteClass'=>false,
	'columns'=>[
		'ccy',
		'ccy_name_ru',
		[
			'name'=>'buy',
			'type' => 'raw',
			'value'=>'$data->buy/10000',
			'headerHtmlOptions'=>['colspan'=>'2'],
			'htmlOptions'=>['colspan'=>'2'],
		],
		[
			'name'=>'sortOrder',
			'evaluateHtmlOptions'=>true,
			'htmlOptions'=>['id'=>'"ordering_{$data->id}"'],
		],
		'unit',
		'date',
		[
			'name'=>'somename',
			'header'=>'someheader',
			'value' => function($data, $row) use ($controller) {
				return $controller->renderPartial('trait/__someone', array('data' => $data), true);
			},
		],
	],
]); ?>
```
