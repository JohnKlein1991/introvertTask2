<?php
require_once(__DIR__ . '/vendor/autoload.php');


//функция,которая создает массив , ключами которого являются timestamp`ы на 
//30 дней вперед
function monthArray() {
    $arr = [];
    for ($i = 1; $i < 31; $i++){
        $key = mktime(0, 0, 0, date("m")  , date("d")+$i, date("Y"));
        $arr[$key] = 0;
    }
    return $arr;
}
//массив с timestamp`ами храним в глобальной переменной
$monArr = monthArray();
//id искомого поля "Дата"
$idOfField = '71237';
//callback функция для array_walk_recursive. Параметры - значение и ключ.
//Функция ищет $key ,который равен 'value' - именно здесь хранится дата. Если 
//находит то $value сравнивается с ключами-timestamp`ами из глобальной 
//переменной $monArr  и в 
//случае равенства увеличивает ззначение этого элемента массива на 1
function dateHandler($value, $key){
    global $monArr;
    if($key === 'value'){
        $date = strtotime($value);
        foreach ($monArr as $key => $item) {
            if ($key === $date){
                $item++;
                $monArr[$key] = $item;
            }
        }
    }
}
//Функция готовит массис для будующего календаря. Вернет массив ,у которого в 
//качестве ключей будут уже не timestamp`ы, а порядковые номера дней
function prepareArrForCalendar($array1) {
    $result = [];
    foreach ($array1 as $value) {
        $result[] = $value;
    }
    return $result;
}

//Функция ,которая ищет сделки по заданному статусу и вызывает все 
//вспомогательные функции. Возвращает массив, в котором ключи - порядкоые 
//номера дней, начиная с текущего, а значения - кол-во сделок, у которых 
//в custom_fields есть поле "Дата", в котором упоминается эта дата
function searchLeadsByDateAndStatus($status = null) {
    Introvert\Configuration::getDefaultConfiguration()->setApiKey('key', 'a68eb01d5aa7d40ae45af4825d8d713a');
    $api = new Introvert\ApiClient();
    
    try {
        $result = $api->lead->getAll(null, $status);
    } catch (Exception $e) {
        echo 'Не удалось получить данные: ', $e->getMessage(), PHP_EOL;
    }


    global $monArr, $idOfField;
    //перебираем все сделки, у которых доп.поля(custom_fields)
    //не пустые и, если там есть поле и искомым id === $idOfField, отправляет 
    //его как массив  в функцию dateHandler
    
    foreach ($result['result'] as $key=>$value) {
        if (count($value['custom_fields']) === 0)  continue;
        if ($value['custom_fields'][0]['id'] === $idOfField ){
            array_walk_recursive($value['custom_fields'][0], 'dateHandler');
        };
    }
    return prepareArrForCalendar($monArr);
}

echo json_encode(searchLeadsByDateAndStatus());