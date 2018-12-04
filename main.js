var N = 5;
var array;
var currentDate = new Date();

//функия,которая принимет массив , который придет из main.php и значение n - 
//максимальное кол-во упоминаний этой даты в сделках. Создает новый массив
//, у которого значениями будут не кол-во упоминаний этой даты, а 1 - если в 
//этот день можно записаться (упоминаний меньше n), 0 - если нельзя (упоминаний
// больше или равно n)
var makeArrayForCalendar = function(array, n){
  var newArray = [];
  var count = array.length;
  for(i = 0; i < count; i++){
      let elem = array.shift();
      if(elem < n){
          newArray[i] = 1; 
      } else {
          newArray[i] = 0;
      }
  } 
  return newArray;
};

//Делаем календарь с помощью datepicker
var calendar = function(){
    $('input').datepicker({
        maxDate: '+30',
        minDate: '+1',
        showOtherMonths: true,
        selectOtherMonths: true,            
        defaultDate: currentDate,
        //данный метод проходит по всем дням, которые будут отображать в нашем
        // календаре и возвращет массив [true] - если день можно будет выбрать
        // или [false] - если нет
        beforeShowDay: function(date){
            if (date > currentDate){
                var elem = array.shift();
                if(elem === 1){
                    return [true];
                }
                else {
                    return [false];
                }    
            }
            else {
                return [false];
            }
        }
    });
};

$.ajax({
    url: 'main.php',
    //в случае, если запрос успешен - меняем параграф <p id='elem-for-replace'>
    //и вызываем функцию calendar, чтобы создать календарь для input
    success: function(data){
        console.log(JSON.parse(data));
        array = makeArrayForCalendar(JSON.parse(data), N);
        let input = document.createElement('input');
        let successParagraph = document.createElement('p');
        successParagraph.innerText = 'Данные загружены!Выберите дату'
        $('#elem-for-replace').replaceWith(successParagraph);
        $('#elem').append(input);
        calendar();
    },
    //в случае ошибки также меняем параграф и выводим сообщение об ошибке в
    // консоль
    error: function(XHR, text){
        let errorParagraph = document.createElement('p');
        errorParagraph.innerText = 'К сожаление, данные загрузить не удалось!';
        $('#elem-for-replace').replaceWith(errorParagraph);
        let message = `Не удалось получить данные из ${this.url}: ${text}`;
        console.log(message);
    }
});

