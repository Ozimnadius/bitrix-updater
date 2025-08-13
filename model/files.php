<?php
declare(strict_types=1);

class Upgrader {
  private string $moduleName;
  private array $module;
  private array $version;
  private int $latestTimestamp;
  private array $paths;
  private string $path;
  private array $files;
  private array $modulesBlackList;

  public function __construct() {
    $this->modulesBlackList = [
      '.',
      '..',
      'b24connector',
      'bitrix.sitecorporate',
      'bitrix.xscan',
      'bitrixcloud',
      'clouds',
      'fileman',
      'highloadblock',
      'iblock',
      'landing',
      'location',
      'main',
      'messageservice',
      'perfmon',
      'rest',
      'search',
      'security',
      'seo',
      'socialservices',
      'translate',
      'ui'
    ];
    $this->pathBlackList = [
      '.',
      '..'
    ];
    $this->publicBlackList = [
      '.',
      '..',
      'bitrix',
      'upload',
      'local',
      'updater',
      '.idea'
    ];
  }

  /**
   * Инициализирует объект Upgrader.
   *
   * @param array $formData Массив, содержащий информацию о модуле, который необходимо обновить.
   *
   * @return array Массив, содержащий имена файлов, которые необходимо обновить.
   *
   * Этот метод инициализирует объект Upgrader, получая из массива $formData информацию
   * о модуле, который необходимо обновить. Он получает имя модуля, старую и новую версию,
   * дату, до которой необходимо обновить файлы. Затем он рекурсивно проходит по всем
   * подкаталогам указанного каталога, отфильтровывая файлы, которые не должны быть
   * обновлены. Для каждого файла он вызывает метод fileNeedUpdate, чтобы
   * проверить, нужно ли обновить файл. Если файл необходимо обновить, он добавляет его
   * к массиву $this->files. Наконец, он возвращает массив $this->files.
   */
  public function init(array $formData): array {
    $this->latestTimestamp = strtotime($formData['date']);
    $this->moduleName = $formData['modules'];
    $this->module = explode('.', $this->moduleName);

    $this->version = [
      'old' => $formData['version'],
      'new' => $formData['newVersion']
    ];

    $this->paths = [
      $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/" . $this->module[0] => '/updater/' . $this->version["new"] . '/install/components/' . $this->module[0],
      $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/" . $this->moduleName => '/updater/' . $this->version["new"] . '/install/js/' . $this->moduleName,
      $_SERVER["DOCUMENT_ROOT"] . "/bitrix/css/" . $this->moduleName => '/updater/' . $this->version["new"] . '/install/css/' . $this->moduleName,
      $_SERVER["DOCUMENT_ROOT"] . "/bitrix/images/" . $this->moduleName => '/updater/' . $this->version["new"] . '/install/images/' . $this->moduleName,
      $_SERVER["DOCUMENT_ROOT"] . "/bitrix/templates/" . $this->module[0] => '/updater/' . $this->version["new"] . '/install/wizards/' . $this->module[0] . '/market/site/templates/' . $this->module[0],
      $_SERVER["DOCUMENT_ROOT"] => '/updater/' . $this->version["new"] . "/install/wizards/" . $this->module[0] . "/market/site/public/ru",
      $_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/' . $this->moduleName => '/updater/' . $this->version["new"]
    ];

    foreach ($this->paths as $key => $value) {
      $this->path = $key;
      $this->search($key);
    }
    $this->setVersion();

    foreach ($this->files as $value) {
      /* if (pathinfo($value, PATHINFO_EXTENSION) == "php") {
           $this->convertFileTo1251($value);
       }*/
    }

    return $this->files;
  }

  /**
   * Ищет файлы в указанном каталоге, которые необходимо обновить.
   *
   * @param string $path Путь к каталогу, в котором необходимо искать файлы, которые необходимо обновить.
   *
   * @return void
   *
   * Этот метод рекурсивно проходит по всем подкаталогам указанного каталога, отфильтровывая файлы,
   * которые не должны быть обновлены. Для каждого файла он вызывает метод fileNeedUpdate, чтобы
   * проверить, нужно ли обновить файл. Если файл необходимо обновить, он вызывает метод copyFile,
   * чтобы скопировать файл.
   */
  private function search(string $path): void {
    if (is_dir($path)) {
      $dir = opendir($path);
      while ($item = readdir($dir)) {
        if (in_array($item, $this->pathBlackList)) {
          continue;
        }
        if ($path === $_SERVER["DOCUMENT_ROOT"] && in_array($item, $this->publicBlackList)) {
          continue;
        }
        $this->search($path . '/' . $item);
      }
      closedir($dir);
    } else {
      if ($this->fileNeedUpdate($path)) {
        $this->copyFile($path);
      }
    }
  }

  /**
   * Проверяет, нужно ли обновить указанный файл на основе временной метки его последнего изменения.
   *
   * @param string $path Путь к проверяемому файлу.
   *
   * @return bool True, если файл необходимо обновить, в противном случае — false.
   *
   * Этот метод проверяет, нужно ли обновить указанный файл, на основе временной метки его последнего изменения.
   * Он сравнивает временную метку последнего изменения файла с последней временной меткой сохраненных обновлений файла.
   * в свойстве `latestTimestamp`. Если временная метка последней модификации файла больше или равна
   * до последней временной метки метод возвращает true, указывая, что файл необходимо обновить. В противном случае,
   * он возвращает false, указывая, что файл не нуждается в обновлении.
   */
  private function fileNeedUpdate(string $path): bool {
    if (!file_exists($path) || !is_readable($path)) {
      return false; // или true
    }

    return filemtime($path) >= $this->latestTimestamp;
  }

  /**
   * Копирует указанный файл в новое место, если его необходимо обновить.
   *
   * @param string $path Путь к файлу, который необходимо скопировать.
   *
   * @return bool True, если файл скопирован успешно, в противном случае — false.
   *
   * Этот метод копирует указанный файл в новое местоположение, если его необходимо обновить. Сначала он добавляет файл
   * путь к массиву `files`. Затем он создает новый путь к файлу, заменяя исходный путь на
   * новый путь указан в массиве `paths`. Затем он проверяет, действителен ли новый путь к файлу и требуется ли файлу
   * обновляется с помощью метода fileNeedUpdate. Если файл необходимо обновить, он вызывает функцию копирования.
   * чтобы скопировать файл в новое место. Наконец, он возвращает true, если файл скопирован успешно, и false.
   * в противном случае.
   */
  private function copyFile(string $path): bool {
    $pathFrom = $path;
    $pathTo = str_replace($this->path, $_SERVER["DOCUMENT_ROOT"] . $this->paths[$this->path], $pathFrom);

    $newPath = str_replace($_SERVER["DOCUMENT_ROOT"] . '/', '', $pathTo);
    $newPath = str_replace(pathinfo($path)['basename'], '', $newPath);
    $this->checkPath($newPath);
    $this->files[] = $pathTo;
    return copy($pathFrom, $pathTo);
  }

  /**
   * Проверяет, существует ли указанный путь к каталогу, и создает недостающие каталоги.
   *
   * @param string $path Путь к каталогу, который необходимо проверить и создать.
   */
  private function checkPath(string $path): void {
    $arrTree = explode('/', $path);
    $tree = $_SERVER["DOCUMENT_ROOT"];
    foreach ($arrTree as $dir) {
      $tree = $tree . '/' . $dir;
      if (!is_dir($tree) && !is_file($tree)) {
        mkdir($tree, 0777, true);
      }
    }
  }

  /**
   * Получает список доступных модулей Битрикс, за исключением тех, которые находятся в черном списке.
   *
   * Этот метод получает список доступных модулей Битрикс путем сканирования каталога «/bitrix/modules/».
   * Он отфильтровывает каталоги, находящиеся в массиве `$this->modulesBlackList`. Отфильтрованный список модулей
   * затем возвращается в виде массива.
   *
   * @return array Массив доступных модулей Битрикс, за исключением тех, что находятся в черном списке.
   */
  public function getModules(): array {
    $path = $_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/';
    return array_filter(scandir($path), function ($dir) use ($path) {
      return is_dir($path . $dir) && !in_array($dir, $this->modulesBlackList);
    });
  }

  /**
   * Получает информацию о версии указанного модуля Битрикс.
   *
   * Этот метод получает информацию о версии указанного модуля Битрикс, включая файл version.php.
   * файл, расположенный в каталоге '/bitrix/modules/' указанного модуля. Информация о версии хранится
   * в массиве $arModuleVersion, который затем возвращается методом.
   *
   * @param string $module Имя модуля Битрикс, для которого необходимо получить информацию о версии.
   *
   * @return array Массив, содержащий информацию о версии указанного модуля Битрикс.
   */
  public function getModuleVersion(string $module): array {
    $this->moduleName = $module;
    include_once $_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/' . $module . '/install/version.php';
    return $arModuleVersion;
  }

  /**
   * Записывает информацию о новой версии модуля в файле version.php.
   *
   * Этот метод записывает информацию о новой версии модуля в файле version.php, расположенном
   * в каталоге '/updater/<version>/install/'. Информация о версии записывается в виде
   * PHP-кода, который задает массив $arModuleVersion.
   *
   * @return void
   */
  private function setVersion(): void {
    $version = '<? $arModuleVersion = [ "VERSION" => "' . $this->version["new"] . '", "VERSION_DATE" => "' . date('Y-m-d H:m:s') . '" ]; ?>';
    file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/updater/" . $this->version["new"] . "/install/version.php", $version);
  }

  /**
   * Конвертирует указанный файл из кодировки UTF-8 в Windows-1251.
   *
   * @param string $path Путь к файлу, который необходимо конвертировать.
   *
   * @return void
   *
   * Этот метод конвертирует указанный файл из кодировки UTF-8 в Windows-1251. Он читает
   * содержимое файла, конвертирует его с помощью функции iconv() и записывает
   * обратно в файл.
   */
  private function convertFileTo1251(string $path): void {
    $content = file_get_contents($path);
    $content = iconv('utf-8', 'windows-1251', $content);
    file_put_contents($path, $content);
  }

  /**
   * Готовит архив для обновления модуля.
   *
   * @param array $formData Массив, содержащий информацию о модуле, который необходимо обновить.
   * @return string Путь к созданному архиву.
   *
   * Этот метод готовит архив для обновления модуля. Он копирует файл updater.php в новую директорию,
   * сохраняет описание модуля в кодировке windows-1251, создает ZIP-архив, добавляет файлы в архив рекурсивно,
   * и возвращает путь к созданному архиву.
   */
  public function prepareArchive(array $formData): string {
    copy($_SERVER["DOCUMENT_ROOT"] . '/updater/updater.php', $_SERVER["DOCUMENT_ROOT"] . '/updater/' . $formData['newVersion'] . '/updater.php');
    file_put_contents($_SERVER["DOCUMENT_ROOT"] . '/updater/' . $formData['newVersion'] . '/description.ru', iconv('utf-8', 'windows-1251', $formData['description']));
    $zip = new ZipArchive();
    $zip->open($_SERVER["DOCUMENT_ROOT"] . '/updater/' . $formData['newVersion'] . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $this->addFileRecursion($zip, $_SERVER["DOCUMENT_ROOT"] . '/updater/' . $formData['newVersion']);
    $zip->close();
    return '/updater/' . $formData['newVersion'] . '.zip';
  }

  /**
   * Добавляет файлы в архив рекурсивно.
   *
   * @param object $zip Объект ZipArchive, в который необходимо добавить файлы.
   * @param string $dir Путь к каталогу, файлы которого необходимо добавить в архив.
   * @param string $start [optional] Путь к старому местоположению файлов. Он используется
   *                      для снятия относительного пути к файлам. Если не указан, то
   *                      он равен параметру $dir.
   *
   * @return void
   *
   * Этот метод добавляет файлы в архив рекурсивно. Он проходит по всем файлам и подкаталогам
   * параметра $dir, и если файл является файлом, он добавляет его в архив. Если файл является
   * подкаталогом, он вызывает себя рекурсивно, чтобы добавить файлы из подкаталога.
   */
  private function addFileRecursion(object $zip, string $dir, string $start = ''): void {
    if (empty($start)) {
      $start = $dir;
    }

    if ($objs = glob($dir . '/*')) {
      foreach ($objs as $obj) {
        if (is_dir($obj)) {
          $this->addFileRecursion($zip, $obj, $start);
        } else {
          $zip->addFile($obj, str_replace(dirname($start) . '/', '', $obj));
        }
      }
    }
  }


}