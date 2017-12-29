<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Парсер с WordPress</title>

    <link href="/favicon.ico" rel="shortcut icon" type="image/x-icon"/>
    <style type="text/css">

        ::selection {
            background-color: #E13300;
            color: white;
        }

        ::-moz-selection {
            background-color: #E13300;
            color: white;
        }

        body {
            background-color: #fff;
            margin: 40px;
            font: 13px/20px normal Helvetica, Arial, sans-serif;
            color: #4F5155;
        }

        a {
            color: #003399;
            background-color: transparent;
            font-weight: normal;
        }

        h1 {
            color: #444;
            background-color: transparent;
            border-bottom: 1px solid #D0D0D0;
            font-size: 19px;
            font-weight: normal;
            margin: 0 0 14px 0;
            padding: 14px 15px 10px 15px;
            text-align: center;
        }

        code {
            font-family: Consolas, Monaco, Courier New, Courier, monospace;
            font-size: 12px;
            background-color: #f9f9f9;
            border: 1px solid #D0D0D0;
            color: #002166;
            display: block;
            margin: 14px 0 14px 0;
            padding: 12px 10px 12px 10px;
        }

        #body {
            margin: 0 15px 0 15px;
            text-align: center;
        }

        p.footer {
            text-align: right;
            font-size: 11px;
            border-top: 1px solid #D0D0D0;
            line-height: 32px;
            padding: 0 10px 0 10px;
            margin: 20px 0 0 0;
        }

        #container {
            margin: 10px;
            border: 1px solid #D0D0D0;
            box-shadow: 0 0 8px #D0D0D0;
        }
        .right{
            float: right;
        }
        .left{
            float: left;
        }
        .access{
            text-align: center;
            width: 100%;
        }
        .ddd{
            display: inline-block;
            width: 220px;
        }
        .clr{
            clear: both;
        }
        input{
            width: 200px;
        }
        .info{
            float: left;
        }

    </style>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" type="text/javascript"></script>
</head>
<body>

<div id="container">
    <h1>Парсер с WordPress</h1>

    <div id="body">

        <div id="message">Ну что, приступим?</div>
        <div id="button">

            <button id="start">Начать</button>
            <button id="parse" style="display: none">Поехали!</button>
            <button id="stop" style="display: none">Стоп!</button>
        </div>
        <div id="result"></div>
        <center>
            <div id="msg"
                 style="width: 50%; height: 400px; overflow-y: scroll; border: dotted 1px gray; display: none"></div>
        </center>
    </div>
    <script>
        var action = false;
        var postsCount = 0;
        var i = 0;
        var doneCount = 0;

        var frt = 1000;

        function show(text) {
            $("#result").html(text);
        }

        function showMsg(text) {
            $("#msg").append(text);
            var elem = document.getElementById('msg');
            elem.scrollTop = elem.scrollHeight;
        }

        function parse() {
            for (i = 0; i <= postsCount; i++) {
                if (action == true) {
                    parseNextPost(i);
                    if (i >= postsCount)
                        show(i + " из " + postsCount + " успешно импортированы!");
                } else i = postsCount + 1;
            }
        }

        function parseNextPost(i) {
            setTimeout(function () {
                var xhr = $.ajax({
                    /* адрес файла-обработчика запроса */
                    url: '/ajax/action/getPost/',
                    /* метод отправки данных */
                    method: 'POST',
                    async: true,
                    /* данные, которые мы передаем в файл-обработчик */
                    data: {
                        "i": i
                    }
                }).done(function (data) {
                    doneCount++;
                    show(doneCount + " из " + postsCount + " успешно импортированы!");
                    showMsg(data);
                });
                if (action == false)
                    xhr.abort()

            }, frt);
            frt = frt + 1000;
        }

        function getPostsCount() {
            $.ajax({
                /* адрес файла-обработчика запроса */
                url: '/ajax/action/getPostsCount/',
                /* метод отправки данных */
                method: 'POST',
                async: true,
                /* данные, которые мы передаем в файл-обработчик */
                data: {
                    "action": 'get_posts_count'
                }
            }).done(function (data) {
                if (data != '0') {
                    show(data);
                    $("#parse").show();
                } else show('Записи не найдены...');
            });

            $.ajax({
                /* адрес файла-обработчика запроса */
                url: '/ajax/action/getPostsCountOnly/',
                /* метод отправки данных */
                method: 'POST',
                async: false,
                /* данные, которые мы передаем в файл-обработчик */
                data: {
                    "action": 'get_posts_count_only'
                }
            }).done(function (data) {
                postsCount = data;
            });
        }

        $(document).ready(function () {
            $("#start").click(function () {
                $(this).hide();
                show('Приступаем...');
                getPostsCount();
            });

            $("#parse").click(function () {
                show('Погнали...');
                $(this).hide();
                $("#msg").show();
                $("#stop").show();
                action = true;
                parse();
            });

            $("#stop").click(function () {
                action = false;
                $.ajax().abort();
                showMsg('Давлю на тормоза!');
                $(this).hide();
                $("#parse").html('Продолжить');
                $("#parse").show();
            });
        });
    </script>

    <p class="footer"><span class="info">* Доступы к базам задаются в файле: /application/config/database.php</span>Страница загрузилась за <strong>{elapsed_time}</strong>
        секунд. <?php echo (ENVIRONMENT === 'development') ? '<strong>WParser</strong> by <a href="mailto:xomiak@rap.org.ua">XomiaK</a>' : '' ?>
    </p>
</div>

</body>
</html>