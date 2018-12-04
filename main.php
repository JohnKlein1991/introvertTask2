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

//callback функция для array_walk_recursive. Параметры - значение и ключ.
//Функция определяет, является ли значение из custom_fields датой. Если - да,
//то она сравнивается с ключами-timestamp`ами из глобальной переменной $monArr  и в 
//случае равенства увеличивает ззначение этого элемента массива на 1
function isDate($value, $key){
    global $monArr;
    $date = strtotime($value);
    if($date){
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
//в custom_fields упоминается эта дата
function searchLeadsByDateAndStatus($status = null) {
    Introvert\Configuration::getDefaultConfiguration()->setApiKey('key', 'a68eb01d5aa7d40ae45af4825d8d713a');
    $api = new Introvert\ApiClient();
    
    try {
        $result = $api->lead->getAll(null, $status);
    } catch (Exception $e) {
        echo 'Не удалось получить данные: ', $e->getMessage(), PHP_EOL;
    }
    
    $result = $api->lead->getAll(null, $status);

    global $monArr;
    //перебираем все сделки, у которых доп.поля(custom_fields)
    //не пустые и отправляет массив со значениями этих полей в функцию isDate
    
    foreach ($result['result'] as $key=>$value) {
        if (count($value['custom_fields']) === 0)  continue;
        array_walk_recursive($value['custom_fields'], 'isDate');

    }
    return prepareArrForCalendar($monArr);
}

echo json_encode(searchLeadsByDateAndStatus());