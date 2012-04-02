<?php

/**
 * Autoenrol cohort authentication plugin version information
 *
 * @package    auth
 * @subpackage mcae
 * @copyright  2011 Andrew "Kama" (kamasutra12@yandex.ru) 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['auth_mcaedescription'] = 'Добавляет возможность автоматического зачисления в глобальные группы';
$string['pluginname'] = 'Автматическое зачисление в глобальные группы';
$string['auth_fieldlocks_help'] = ' ';

$string['auth_mainrule_fld'] = 'Шаблон названия группы (Массив, каждый элемент с новой строки.)';
$string['auth_secondrule_fld'] = 'Заменитель для пустых значений';
$string['auth_replace_arr'] = 'Заменители текста (Массив, каждый элемент с новой строки. Формат key|value)';

$string['auth_delim'] = 'Конец строки (EOL)';
$string['auth_delim_help'] = 'В разных ОС используются разные символы конца строки.<br>В Windows - CR+LF<br>В Linux - LF<br>и т. д.<br>Если модуль не работает, попробуйте изменить это значение.';

$string['auth_donttouchusers'] = 'Игнорировать пользователей';
$string['auth_donttouchusers_help'] = 'Введите имена пользователей через запятую.';
$string['auth_enableunenrol'] = 'Включить автоматическое удаление из гг.';

$string['auth_tools_help'] = 'Автоудаление работает только с теми глобальными группами, которые привязаны к плагину. После включения функции автоматического удаления, все группы, создаваемые плагином, будут автоматически привязываться к нему. Остальные группы вы можете конвертировать на <a href="{$a->url}" target="_blank">этой странице</a>. Там же вы можете просмотреть участников или удалить группы, которые больше не нужны.'; 

$string['auth_cohorttoolmcae'] = 'Действия с глобальными группами';
$string['auth_cohortviewmcae'] = 'Просмотр глобальных групп';

$string['auth_selectcohort'] = 'Выберите группу';

$string['auth_username'] = 'Имя';
$string['auth_link'] = 'Ссылка';
$string['auth_userlink'] = 'Список пользователей';
$string['auth_userprofile'] = 'Профиль пользователя &gt;&gt;';
$string['auth_emptycohort'] = 'Пользователи не записаны';
$string['auth_viewcohort'] = 'Просмотр глобальных групп';
$string['auth_total'] = 'Итого';
$string['auth_cohortname'] = 'Название';
$string['auth_component'] = 'Модуль';
$string['auth_count'] = 'Количество';
$string['auth_cohortoper_help'] = '<p>Выберите группы для преобразования.</p><p><b>ВНИМАНИЕ:</b> <i>Вы <b>не сможете</b> редактировать группы, привязанные к модулю "auth_mcae", вручную!</i></p><p>Сделайте резервную копию базы данных!!!</p>';

$string['auth_profile_help'] = 'Доступные шаблоны';
