<?php
/***********************************************************************/
/** This file is part of the Basic Meeting List Toolbox (BMLT).
Find out more at: https://bmlt.app
BMLT is free software: you can redistribute it and/or modify
it under the terms of the MIT License.
BMLT is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
MIT License for more details.
You should have received a copy of the MIT License along with this code.
If not, see <https://opensource.org/licenses/MIT>.*/
defined('BMLT_EXEC') or die('Cannot Execute Directly');    // Makes sure that this file is in the correct context.

global $comdef_install_wizard_strings;

$comdef_install_wizard_strings = array (
    'Database_Version_Error'        =>  'ОШИБКА: на этом сервере должен быть установлен PHP версии 5.6 или выше!',
    'Database_PDO_Error'            =>  'ОШИБКА: у вас не установлен PHP PDO!',
    'Database_Type_Error'           =>  'ОШИБКА: даже если у вас есть PDO, у вас не установлены драйверы базы данных!',
    'Database_Type_MySQL_Error'     =>  'ОШИБКА: Даже если у вас есть PDO и у вас установлены драйверы баз данных, ни один из них не является MySQL (единственный поддерживаемый драйвер)!',
    'Database_TestButton_Text'      =>  'ТЕСТ',
    'Database_TestButton_Success'   =>  'Соединение с базой данных прошло успешно.',
    'Database_TestButton_Fail'      =>  'Ошибка подключения к базе данных: ',
    'Database_TestButton_Fail2'     =>  'Не удалось подключиться к базе данных, поскольку уже существует инициализированная база данных.',
    'Database_Whitespace_Note'      =>  'Warning: %s has whitespace at the beginning or end.',

    'AJAX_Handler_DB_Connect_Error' =>  'Ошибка подключения к базе данных! Убедитесь, что база данных существует, ПОЛНОСТЬЮ ПУСТАЯ, пользователь создан, и этот пользователь имеет полные права доступа к пустой базе данных.',
    'AJAX_Handler_DB_Established_Error' => 'База данных уже существует и была настроена! Вы не можете использовать эту настройку, чтобы перезаписать существующую базу данных!',
    'AJAX_Handler_DB_Incomplete_Error'  =>  'Не достаточно информации для инициализации базы!',

    'NoDatabase_Note_AlreadySet'    =>  'База данных уже инициализирована с указанным префиксом таблицы. Пожалуйста, выберите новую.',
    'NoDatabase_Note_GenericError'  =>  'При подключении к базе данных произошла ошибка. Пожалуйста, проверьте настройки вашей базы данных.',
    'NoDatabase_Note_ClickHere'     =>  'Нажмите здесь, чтобы вернуться на страницу настройки базы данных.',
    'NoDatabase_Note_PasswordIssue' =>  'Вы должны выбрать имя пользователя и пароль для пользователя Администратора сервера.',
    'NoDatabase_Note_ServerSettings_ClickHere' => 'Нажмите здесь, чтобы вернуться на страницу настроек сервера.',
    'NoServerAdmin_Note_AlreadySet' =>  'База данных уже существует, поэтому вы не можете настроить учетную запись администратора сервера (одна из них уже существует).',
    'NeedLongerPasswordNote'        =>  'Этот пароль слишком короткий. Этот пароль должен быть не менее % символов.',

    'Prev_Button'                   =>  'Предыдущий',
    'Next_Button'                   =>  'Следущий',

    'Page_1_Tab'                    =>  'ШАГ 1: База данных',
    'Page_1_Heading'                =>  'Настройки подключения к базе данных',
    'Page_1_Text'                   =>  'Прежде чем вы сможете применить настройки на этой странице, вы должны настроить новую ПОЛНОСТЬЮ ПУСТУЮ базу данных и создать пользователя базы данных, который имеет полные права пользователя в этой базе данных.',

    'Database_Name'                 =>  'Имя базы данных:',
    'Database_Name_Default_Text'    =>  'Введите имя базы данных',
    'Database_Type'                 =>  'Тип базы данных:',
    'Database_Host'                 =>  'Хост базы данных:',
    'Database_Host_Default_Text'    =>  'Введите хост базы данных',
    'Database_Host_Additional_Text' =>  'Это обычно "localhost."',
    'Table_Prefix'                  =>  'Префикс таблицы:',
    'Table_Prefix_Default_Text'     =>  'Введите префикс таблицы',
    'Table_Prefix_Additional_Text'  =>  'Только для нескольких root  серверов, совместно использующих базу данных.',
    'Database_User'                 =>  'Пользователь базы данных:',
    'Database_User_Default_Text'    =>  'Введите имя пользователя базы данных',
    'Database_PW'                   =>  'Пароль базы данных:',
    'Database_PW_Default_Text'      =>  'Введите пароль базы данных',
    'Database_PW_Additional_Text'   =>  'Сделайте самый уродливый, сложный пароль. Он обладает огромной силой, потому что вы не сможете его запомнить.',

    'Maps_API_Key_Warning'          =>  'Возникла проблема с ключом API Карт Google.',
    'Maps_API_Key_Not_Set'          =>  'Ключ API Карт Google не задан.',
    'Maps_API_Key_Valid'            =>  'Ключ API Карт Google действителен.',
    'Maps_API_Key_ClickHere'        =>  'Нажмите здесь, чтобы вернуться на страницу настройки ключа API Карт Google.',

    'Page_2_Tab'                    =>  'ШАГ 2: Настройки API Карт Google',
    'Page_2_Heading'                =>  'Настройки API Карт Google',
    'Page_2_API_Key_Prompt'         =>  'Введите ключ API Google для геокодирования:',
    'Page_2_API_Key_Set_Button'     =>  'ТЕСТОВЫЙ КЛЮЧ',
    'Page_2_API_Key_Not_Set_Prompt' =>  'Сначала установите API ключ',
    'Page_2_Text'                   =>  'При сохранении собрания корневой сервер BMLT использует API Карт Google для определения широты и долготы для адреса встречи. Эти настройки необходимы для того, чтобы корневой сервер BMLT мог взаимодействовать с API Карт Google.',

    'Page_3_Tab'                    =>  'ШАГ 3: Настройки сервера',
    'Page_3_Heading'                =>  'Установить различные глобальные настройки сервера',
    'Page_3_Text'                   =>  'Это несколько параметров, которые влияют на администрирование и общую конфигурацию этого сервера. Большинство настроек сервера выполняются на самом сервере.',
    'Admin_Login'                   =>  'Логин администратора сервера:',
    'Admin_Login_Default_Text'      =>  'Введите логин администратора сервера',
    'Admin_Login_Additional_Text'   =>  'Это строка логина для Администратора Сервера.',
    'Admin_Password'                =>  'Пароль администратора сервера:',
    'Admin_Password_Default_Text'   =>  'Введите пароль администратора сервера',
    'Admin_Password_Additional_Text'    =>  'Убедитесь, что это нетривиальный пароль! У этого есть большая сила! (Вы никогда не сможете его запомнить).',
    'ServerAdminName'               =>  'Администратор Сервера',
    'ServerAdminDesc'               =>  'Главный администратор сервера',
    'ServerLangLabel'               =>  'Язык сервера по умолчанию:',
    'DistanceUnitsLabel'            =>  'Единицы расстояния:',
    'DistanceUnitsMiles'            =>  'Мили',
    'DistanceUnitsKM'               =>  'Километры',
    'SearchDepthLabel'              =>  'Плотность встреч для автоматического поиска:',
    'SearchDepthText'               =>  'Это приблизительное количество встреч, которые необходимо найти при автоматическом выборе радиуса. Больше встреч означает больший радиус.',
    'HistoryDepthLabel'             =>  'Сколько изменений на собраниях сохранять:',
    'HistoryDepthText'              =>  'Чем дольше история, тем больше будет база данных.',
    'TitleTextLabel'                =>  'Название экрана администрирования:',
    'TitleTextDefaultText'          =>  'Введите краткое название для редактирования страницы входа',
    'BannerTextLabel'               =>  'Подсказка для логина администратора:',
    'BannerTextDefaultText'         =>  'Введите короткую подсказку для страницы входа',
    'RegionBiasLabel'               =>  'Регион смещения:',
    'PasswordLengthLabel'           =>  'Минимальная длина пароля:',
    'PasswordLengthExtraText'       =>  'Это также повлияет на пароль администратора сервера, указанный выше.',
    'DefaultClosedStatus'           =>  'Встречи считаются закрытыми по умолчанию:',
    'DefaultClosedStatusExtraText'  =>  'Это в первую очередь влияет на экспорт в NAWS.',
    'DurationLabel'                 =>  'Продолжительность встречи по умолчанию:',
    'DurationHourLabel'             =>  'Часы',
    'DurationMinutesLabel'          =>  'Минуты',
    'LanguageSelectorEnableLabel'   =>  'Выбор языка отображения при входе в систему:',
    'LanguageSelectorEnableExtraText'   =>  'Если вы нажмёте это, на экране входа появится всплывающее меню, чтобы администраторы могли выбрать свой язык.',
    'SemanticAdminLabel'            =>  'Включить семантическое администрирование:',
    'SemanticAdminExtraText'        =>  'Если этот флажок не установлен, все администрирование должно выполняться через логин корневого сервера (без приложения) (No Apps)).',
    'EmailContactEnableLabel'       =>  'Разрешить контакты  электронной почты встреч:',
    'EmailContactEnableExtraText'   =>  'Если вы выберете это, посетители сайта смогут отправлять электронные письма с записями собраний.',
    'EmailContactAdminEnableLabel'      =>  'Включить администратора службы поддержки на эти электронные письма:',
    'EmailContactAdminEnableExtraText'  =>  'Отправляет копии этих писем администратору сервисного органа (если они не являются основным получателем)).',
    'EmailContactAllAdminEnableLabel'       =>  'Включить всех администраторов сервисных органов в эти электронные письма:',
    'EmailContactAllAdminEnableExtraText'   =>  'Отправляет копии этих писем всем соответствующим администраторам сервисных органов.',

    'Page_4_Initialize_Root_Server_Heading' => 'Инициализировать root сервер',
    'Page_4_Initialize_Root_Server_Text'    => 'Кнопка ниже инициализирует root server с пустой базой данных и администратора сервера.',
    'Page_4_Initialize_Root_Server_Button'  => 'Инициализация root server',

    'Page_4_Tab'                    =>  'ШАГ 4: Сохранить настройки',
    'Page_4_Heading'                =>  'Создать файл настроек',
    'Page_4_Text'                   =>  'Root Server не смог создать файл настроек для вас. Вместо этого мы просим вас создать его самостоятельно через FTP или файловый менеджер панели управления, назовите его «auto-config.inc.php» и вставьте в файл следующий текст:',

    'NAWS_Export_Spreadsheet_Optional' => 'NAWS Экспорт электронных таблиц (необязательно): ',
    'NAWS_Export_Spreadsheet_Initially_Publish' => 'Initialize imported meetings to \'published\': ',

    'DefaultPasswordLength'         =>  10,
    'DefaultMeetingCount'           =>  10,
    'DefaultChangeDepth'            =>  5,
    'DefaultDistanceUnits'          =>  'mi',
    'DefaultDurationTime'           =>  '01:30:00',
    'DurationTextInitialText'       =>  'N.A. Собрания, как правило, продолжительностью 90 минут (полтора часа), если не указано иное.',
    'time_format'                   =>  'g:i A',
    'change_date_format'            =>  'g:i A, n/j/Y',
    'BannerTextInitialText'         =>  'Логин Администратора :',
    'TitleTextInitialText'          =>  'Basic Meeting List Toolbox Администрирование ',
    'DefaultRegionBias'             =>  'мы',
    'search_spec_map_center'        =>  array ( 'долгота' => -118.563659, 'широта' => 34.235918, 'приближенность' => 6 ),
    'DistanceChoices'               =>  array ( 2, 5, 10, 20, 50 ),
    'HistoryChoices'                =>  array ( 1, 2, 3, 5, 8, 10, 15 ),
    'PW_LengthChices'               =>  array ( 6, 8, 10, 12, 16 ),
    'ServerAdminDefaultLogin'       =>  'serveradmin',

    'Explanatory_Text_1_Initial_Intro'  =>  'Этот мастер установки проведет вас через процесс создания исходной базы данных, а также файла конфигурации. На последнем шаге мы создадим файл настроек и инициализируем пустую базу данных.',
    'Explanatory_Text_1_DB_Intro'       =>  'Первое, что вам нужно сделать, это создать новую, пустую базу данных и пользователя базы данных, который имеет полный доступ к этой базе данных. Обычно это делается через панель управления вашего веб-сайта. После того, как вы создали базу данных, вам необходимо ввести информацию об этой базе данных в текстовые элементы на этой странице..',

    'Explanatory_Text_2_Region_Bias_Intro'  =>  '«Регион смещения» - это код, который отправляется в Google по завершении поиска местоположения и может помочь Google разобраться в неоднозначных поисковых запросах.',
    'Explanatory_Text_2_API_key_Intro'      =>  '«Ключ API» - это ключ, который <a target="_blank" title="Перейдите по этой ссылке, чтобы перейти на страницу, где обсуждается ключ API Google." href="https://bmlt.app/google-api-key/">you need to register with Google</a> чтобы иметь возможность использовать их картографический сервис.',
    'Explanatory_Text_2_API_key_2_Intro'    =>  'Вам потребуется предоставить действительный ключ API для создания новых собраний на корневом сервере.',

    'Explanatory_Text_3_Server_Admin_Intro' =>  'Администратор сервера является основным пользователем сервера. Это единственная учетная запись, которая может создавать новых пользователей и службы и является очень мощной. Вы должны создать идентификатор входа в систему и нетривиальный пароль для этой учетной записи. Вы сможете изменить другие аспекты учетной записи на главном сервере после настройки базы данных.',
    'Explanatory_Text_3_Misc_Intro'     =>  'Это различные настройки, которые влияют на поведение и внешний вид корневого сервера.',

    'Explanatory_Text_4_Main_Intro'     =>  'Если вы ввели информацию базы данных, предоставили действительный ключ API Карт Google и указали регистрационную информацию для Администратора сервера, тогда вы можете инициализировать корневой сервер здесь. Помните, что база данных должна быть ПОЛНОСТЬЮ ПУСТО из таблиц корневого сервера BMLT для этого сервера (в ней могут быть таблицы для других серверов или служб).',
    'Explanatory_Text_4_NAWS_Export'    =>  'Optionally, you can import the meetings from a NAWS export spreadsheet. Uncheck the box to initialize them to \'unpublished\'. (This is useful if many of the new meetings will need to be edited or deleted, and you don\'t want them showing up in the meantime.)',
    'Explanatory_Text_4_File_Intro'     =>  'Текст в поле ниже - это исходный код PHP для основного файла настроек. Вам нужно будет создать файл на сервере с этим текстом. Файл находится на том же уровне, что и каталог основного сервера для корневого сервера.',
    'Explanatory_Text_4_File_Extra'     =>  'Вы также должны убедиться, что права доступа к файлам ограничены (chmod 0644). Это предотвращает запись файла, и корневой сервер не будет работать, если файл не имеет правильных разрешений.',
    'Page_4_PathInfo'                   =>  'Файл должен быть размещен как %s/auto-config.inc.php, где ваш %s каталог. После того, как файл был создан и вы поместили в него вышеуказанный текст, вы должны выполнить следующую команду, чтобы убедиться, что права доступа правильные:',
    'Page_4_Final'                      =>  'Как только все это будет завершено, обновите эту страницу, и вы должны увидеть страницу входа на корневой сервер.',
    'FormatLangNamesLabel'              =>  'Введите дополнительные языки в формате code1:name1 (Например "fa:farsi ru:russian"):',
);
