<?php 

$html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="script.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <form id="formsub">
        <div>Оставьте Вашу заявку</div>
        <div><input type="text" id="in_name" placeholder="Имя"></div>
        <div><input type="email" id="in_email" placeholder="email"></div>
        <div><input type="tel" id="in_phone" placeholder="телефон"></div>
        <div><input type="text" id="in_cost" placeholder="цена"></div>
        <div><input type="hidden" id="in_note" placeholder="note" value="'. time() .'"></div>
        <div id="bad_result">Заполните пустые строки</div>
        <div><input type="button" value="Отправить" onclick="formsub()"></div>
    </form>
</body>
</html>';

echo $html;

?>