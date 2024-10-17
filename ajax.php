<?
include_once('model/files.php');
$upgrader = new Upgrader();

switch ($_POST['action']) {
    case 'version':
        echo json_encode($upgrader->getModuleVersion($_POST['modules']));
        break;
    case 'searchAndCopy':
        echo json_encode($upgrader->init($_POST));
        break;
    case 'prepareArchive':
        echo json_encode($upgrader->prepareArchive($_POST));
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>