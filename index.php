<?php include_once('model/files.php');
$latestTimestamp = strtotime('01-10-2024 15:00:00');
global $upgrader;
$upgrader = new Upgrader();
//$arr = $upgrader->getModules();
//$a = 0;
//$upgrader->init();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

</head>
<body>

<div class="container py-5">
    <form id="form" action="" method="post">
        <input type="hidden" name="action" value="">
        <div class="mb-4">
            <h2>Обновление модуля Bitrix</h2>
        </div>
        <div class="mb-3">
            <div class="row align-items-end">
                <div class="col-3">
                    <label for="modules"
                           class="form-label">
                        Выберите модуль
                    </label>
                    <select id="modules"
                            class="form-select"
                            name="modules"
                            data-event="change.getModule"
                    >
                        <option selected disabled>Выберите модуль</option>
                        <? foreach ($upgrader->getModules() as $module): ?>
                            <option value="<?= $module ?>"><?= $module ?></option>
                        <? endforeach; ?>
                    </select>
                </div>
                <div class="col-3">
                    <label for="version"
                           class="form-label">
                        Версия модуля
                    </label>
                    <input id="version" type="text" name="version" class="form-control" readonly>
                    <input id="newVersion" type="hidden" name="newVersion">

                </div>
                <div class="col-3">
                    <label for="date"
                           class="form-label">
                        Дата последнего обновления
                    </label>
                    <input id="date" class="form-control" type="text" name="date" readonly>
                </div>
                <div class="col-3">
                    <button id="searchBtn"
                            type="button"
                            class="btn btn-primary"
                            disabled
                            data-event="click.searchAndCopy"
                    >
                        Search,Copy and Convert
                    </button>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <div class="row">
                <div class="col-12 mb-3">
                    <h3>Найденые файлы</h3>
                    <table id="table" class="table">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">File</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                <div class="col-12 mb-3">
                    <label for="description"
                           class="form-label">
                        Описание обновления
                    </label>
                    <textarea class="form-control"
                              id="description"
                              name="description"
                              rows="10"
                    >
Исправления:
<ul>
    <li>Описание обновления</li>
</ul>
Внимание!!! Перед установкой обновления ОБЯЗАТЕЛЬНО выполните полное резервное копирование сайта. Если вы вносили изменения в шаблон решения, то дополнительно скопируйте всю папку шаблона!
<br><br>
Если у Вас есть пожелания по работе решение, пишете нам на почту support@web-comp.ru и мы всё доработаем.<br><br>
                    </textarea>
                </div>
                <div class="col-12 mb-3">
                    <button id="prepareArchive"
                            type="button"
                            class="btn btn-primary"
                            disabled
                            data-event="click.prepareArchive"
                    >
                        Create archive
                    </button>
                </div>
                <div class="col-12 mb-3">
                    <a id="archiveLink"
                       class="link-success"
                       href=""
                       hidden
                       download
                    >Download archive</a>
                </div>

            </div>
        </div>

    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
<script src="script.js"></script>
</body>
</html>




