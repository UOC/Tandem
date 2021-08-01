<?php
require_once dirname(__FILE__) . '/../classes/lang.php';

require_once __DIR__ . '/../classes/constants.php';
require_once __DIR__ . '/../classes/gestorBD.php';
require_once __DIR__ . '/../IMSBasicLTI/uoc-blti/lti_utils.php';

$user_obj = isset($_SESSION[CURRENT_USER]) ? $_SESSION[CURRENT_USER] : false;
$course_id = isset($_SESSION[COURSE_ID]) ? $_SESSION[COURSE_ID] : false;

$gestorBD = new GestorBD();
if (empty($user_obj) || $user_obj->instructor != 1) {
    header('Location: ../index.php');
    die();
}

$exerciseId = isset($_POST['exerciseId'])?$_POST['exerciseId']:0;
$linkedTasks = $gestorBD->getLinkedTasks($exerciseId, $course_id);
?>
<table class="table">
    <thead>
    <tr>
        <th><?php echo $LanguageInstance->get('Title') ?></th>
        <th><?php echo $LanguageInstance->get('Language') ?></th>
        <th><?php echo $LanguageInstance->get('Level') ?></th>
        <th><?php echo $LanguageInstance->get('Enabled') ?></th>
        <th><?php echo $LanguageInstance->get('Action') ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($linkedTasks as $task) { ?>
        <tr data-id="<?php echo $task['id']?>">
            <td><?php echo $task['title']?></td>
            <td><?php echo $task['language']?></td>
            <td><?php echo $task['level']?></td>
            <td><?php echo $LanguageInstance->get($task['active']?'yes':'no')?></td>
            <td>
                <a onclick="unlinkTask(<?php echo $task['id']?>)" class="tandem-btn">-</a>
            </td>
        </tr>
    <?php }
    if(count($linkedTasks) == 0) { ?>
        <tr>
            <td scope="row"><?php echo $LanguageInstance->get('No tasks available') ?></td>
        </tr>
    <?php } ?>
    </tbody>

</table>
