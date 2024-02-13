function formsub() {
    var data = {};
    let red_st = document.getElementById('bad_result').style;

    // собираем значения из инпутов
    data['in_name'] = document.getElementById('in_name').value;
    data['in_email'] = document.getElementById('in_email').value;
    data['in_phone'] = document.getElementById('in_phone').value;
    data['in_cost'] = document.getElementById('in_cost').value;
    data['in_note'] = document.getElementById('in_note').value;

    // проверяем хотя бы их наличие ... 
    if (!data['in_name']) { red_st.display = 'block'; return; }
    if (!data['in_email']) { red_st.display = 'block'; return; }
    if (!data['in_phone']) { red_st.display = 'block'; return; }
    if (!data['in_cost']) { red_st.display = 'block'; return; }
    red_st.display = 'none';
    
    
    fetchData(); // отправляем данные на сервер
    async function fetchData() {
        try {
            let response = await fetch('send_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json;' //  charset=utf-8
                },
                body: JSON.stringify(data)
            });
            result = await response.text();
            console.log(result);
            alert('Спасибо за заявку');
            clearData();
        } catch (error) {
            console.log(error);
        }
    }
    

}

function clearData() // функция для очистки данных формы
{
    let now = new Date().getTime() / 1000;;
    document.getElementById('in_name').value = null;
    document.getElementById('in_email').value = null;
    document.getElementById('in_phone').value = null;
    document.getElementById('in_cost').value = null;
    document.getElementById('in_note').value = now;
   
}

